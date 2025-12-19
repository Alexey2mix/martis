<?php
/**
 * Оптимизированная система фильтрации товаров WooCommerce
 * Версия с кэшированием и оптимизированными запросами
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// 1. ИНИЦИАЛИЗАЦИЯ
// ============================================================================

/**
 * Инициализация AJAX обработчиков фильтрации
 */
function severcon_init_filter_handlers() {
    // Основные обработчики фильтрации
    add_action('wp_ajax_filter_category_products', 'severcon_ajax_filter_category_products');
    add_action('wp_ajax_nopriv_filter_category_products', 'severcon_ajax_filter_category_products');
    
    // Оптимизированный обработчик счетчиков
    add_action('wp_ajax_update_filter_counts', 'severcon_ajax_optimized_update_filter_counts');
    add_action('wp_ajax_nopriv_update_filter_counts', 'severcon_ajax_optimized_update_filter_counts');
    
    // Очистка кэша при обновлении товаров
    add_action('save_post_product', 'severcon_clear_filter_cache');
    add_action('edited_product_cat', 'severcon_clear_filter_cache');
    add_action('edited_product_tag', 'severcon_clear_filter_cache');
}
add_action('init', 'severcon_init_filter_handlers');

// ============================================================================
// 2. СИСТЕМА КЭШИРОВАНИЯ
// ============================================================================

/**
 * Генерация ключа кэша для фильтров
 */
function severcon_get_filter_cache_key($params) {
    $key_data = [
        'cat' => $params['category_id'] ?? 0,
        'tax' => $params['taxonomy'] ?? '',
        'term' => $params['term_slug'] ?? '',
        'filters' => $params['active_filters'] ?? [],
        'type' => $params['type'] ?? 'count'
    ];
    
    return 'severcon_filter_' . md5(wp_json_encode($key_data));
}

/**
 * Очистка кэша фильтров
 */
function severcon_clear_filter_cache($post_id = 0) {
    // Очищаем все кэши фильтров при изменении товара
    global $wpdb;
    
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_severcon_filter_%'
        )
    );
    
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_severcon_filter_%'
        )
    );
}

/**
 * Получение кэшированного значения
 */
function severcon_get_cached_filter($cache_key) {
    $cached = get_transient($cache_key);
    return $cached !== false ? $cached : null;
}

/**
 * Установка значения в кэш
 */
function severcon_set_cached_filter($cache_key, $value, $expiration = 1800) { // 30 минут
    set_transient($cache_key, $value, $expiration);
}

// ============================================================================
// 3. ОПТИМИЗИРОВАННЫЕ ФУНКЦИИ ПОДСЧЕТА
// ============================================================================

/**
 * Оптимизированный подсчет товаров с кэшированием
 */
function severcon_optimized_count_products($category_id, $taxonomy, $term_slug, $active_filters) {
    // Генерация ключа кэша
    $cache_key = severcon_get_filter_cache_key([
        'category_id' => $category_id,
        'taxonomy' => $taxonomy,
        'term_slug' => $term_slug,
        'active_filters' => $active_filters,
        'type' => 'count'
    ]);
    
    // Пробуем получить из кэша
    $cached = severcon_get_cached_filter($cache_key);
    if ($cached !== null) {
        return $cached;
    }
    
    // Строим оптимизированный запрос
    $args = severcon_build_count_query($category_id, $taxonomy, $term_slug, $active_filters);
    
    // Выполняем запрос
    $query = new WP_Query($args);
    $count = $query->found_posts;
    
    // Сохраняем в кэш
    severcon_set_cached_filter($cache_key, $count);
    
    return $count;
}

/**
 * Построение оптимизированного запроса для подсчета
 */
function severcon_build_count_query($category_id, $taxonomy, $term_slug, $active_filters) {
    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 1, // Нам нужно только количество
        'fields' => 'ids', // Только ID для экономии памяти
        'no_found_rows' => false, // Нужно для found_posts
        'update_post_meta_cache' => false, // Не кэшируем мета-данные
        'update_post_term_cache' => false, // Не кэшируем термины
        'tax_query' => ['relation' => 'AND']
    ];
    
    // 1. Категория товаров
    $args['tax_query'][] = [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $category_id,
        'include_children' => true
    ];
    
    // 2. Текущий термин атрибута
    $args['tax_query'][] = [
        'taxonomy' => $taxonomy,
        'field' => 'slug',
        'terms' => $term_slug
    ];
    
    // 3. Активные фильтры (кроме текущего атрибута)
    foreach ($active_filters as $filter_tax => $filter_terms) {
        if ($filter_tax !== $taxonomy && !empty($filter_terms) && is_array($filter_terms)) {
            $args['tax_query'][] = [
                'taxonomy' => $filter_tax,
                'field' => 'slug',
                'terms' => array_map('sanitize_text_field', $filter_terms),
                'operator' => 'IN'
            ];
        }
    }
    
    return apply_filters('severcon_count_query_args', $args, $category_id, $taxonomy, $term_slug, $active_filters);
}

// ============================================================================
// 4. ОБНОВЛЕННЫЕ AJAX ОБРАБОТЧИКИ
// ============================================================================

/**
 * Оптимизированный AJAX обработчик обновления счетчиков
 */
function severcon_ajax_optimized_update_filter_counts() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_filter_nonce')) {
        wp_send_json_error([
            'message' => __('Ошибка безопасности', 'severcon'),
            'code' => 'security_error'
        ]);
        return;
    }
    
    // Получение параметров
    $category_id = severcon_validate_id(severcon_get_post_var('category_id', 0));
    $active_filters = severcon_clean_input(severcon_get_post_var('filters', []));
    
    if (!$category_id) {
        wp_send_json_error([
            'message' => __('Категория не указана', 'severcon'),
            'code' => 'invalid_category'
        ]);
        return;
    }
    
    // Получение оптимизированных счетчиков
    $available_counts = severcon_get_optimized_filter_counts($category_id, $active_filters);
    
    wp_send_json_success($available_counts);
    wp_die();
}

/**
 * Получение оптимизированных счетчиков для фильтров
 */
function severcon_get_optimized_filter_counts($category_id, $active_filters) {
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    $available_counts = [];
    
    // Группируем запросы по таксономии для дальнейшей оптимизации
    foreach ($attribute_taxonomies as $attribute) {
        $taxonomy = 'pa_' . $attribute->attribute_name;
        
        // Получаем все термины для этой таксономии
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'id=>slug'
        ]);
        
        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }
        
        // Подсчет для каждого термина
        foreach ($terms as $term_id => $term_slug) {
            $count = severcon_optimized_count_products($category_id, $taxonomy, $term_slug, $active_filters);
            
            // Добавляем только если есть товары или термин уже выбран
            if ($count > 0 || severcon_is_term_selected($taxonomy, $term_slug, $active_filters)) {
                if (!isset($available_counts[$taxonomy])) {
                    $available_counts[$taxonomy] = [];
                }
                
                $available_counts[$taxonomy][$term_slug] = $count;
            }
        }
    }
    
    return $available_counts;
}

/**
 * Проверка выбран ли термин
 */
function severcon_is_term_selected($taxonomy, $term_slug, $active_filters) {
    if (!isset($active_filters[$taxonomy]) || !is_array($active_filters[$taxonomy])) {
        return false;
    }
    
    return in_array($term_slug, $active_filters[$taxonomy]);
}

// ============================================================================
// 5. СУЩЕСТВУЮЩИЕ ФУНКЦИИ (сохраняем для обратной совместимости)
// ============================================================================

/**
 * AJAX фильтрация товаров категории
 */
function severcon_ajax_filter_category_products() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_filter_nonce')) {
        return;
    }
    
    // Получение и валидация параметров
    $category_id = severcon_validate_id(severcon_get_post_var('category_id', 0));
    $page = severcon_validate_id(severcon_get_post_var('page', 1));
    $per_page = severcon_validate_id(severcon_get_post_var('per_page', 12));
    $orderby = severcon_get_post_var('orderby', 'menu_order');
    $filters = severcon_clean_input(severcon_get_post_var('filters', array()));
    
    if (!$category_id) {
        wp_send_json_error(array(
            'message' => __('Категория не указана', 'severcon'),
            'code'    => 'invalid_category'
        ));
    }
    
    // Подготовка аргументов запроса
    $args = severcon_build_filter_query_args($category_id, $filters, $orderby, $page, $per_page);
    
    // Выполнение запроса
    $products_query = new WP_Query($args);
    
    if ($products_query->have_posts()) {
        ob_start();
        
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }
        
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'html'       => $html,
            'count'      => $products_query->found_posts,
            'max_pages'  => $products_query->max_num_pages,
            'page'       => $page,
            'per_page'   => $per_page
        ));
    } else {
        wp_send_json_success(array(
            'html'       => '<div class="no-products-found"><p>' . __('Товаров не найдено', 'severcon') . '</p></div>',
            'count'      => 0,
            'max_pages'  => 0,
            'page'       => $page
        ));
    }
    
    wp_die();
}

/**
 * Построение аргументов запроса для фильтрации
 */
function severcon_build_filter_query_args($category_id, $filters, $orderby, $page, $per_page) {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
        'tax_query'      => array(
            'relation' => 'AND'
        ),
        'meta_query'     => array()
    );
    
    // Добавление категории
    $args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => $category_id,
        'include_children' => true
    );
    
    // Добавление фильтров по атрибутам
    if (!empty($filters) && is_array($filters)) {
        foreach ($filters as $taxonomy => $selected_terms) {
            if (!empty($selected_terms) && is_array($selected_terms)) {
                $args['tax_query'][] = array(
                    'taxonomy' => sanitize_text_field($taxonomy),
                    'field'    => 'slug',
                    'terms'    => array_map('sanitize_text_field', $selected_terms),
                    'operator' => 'IN'
                );
            }
        }
    }
    
    // Добавление сортировки
    $args = severcon_add_orderby_to_query($args, $orderby);
    
    // Фильтр для модификации аргументов
    return apply_filters('severcon_filter_query_args', $args, $category_id, $filters);
}

/**
 * Добавление сортировки к запросу
 */
function severcon_add_orderby_to_query($args, $orderby) {
    switch ($orderby) {
        case 'price':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'ASC';
            break;
            
        case 'price-desc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order']    = 'DESC';
            break;
            
        case 'date':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
            
        case 'popularity':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order']    = 'DESC';
            break;
            
        case 'rating':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_wc_average_rating';
            $args['order']    = 'DESC';
            break;
            
        case 'title':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
            
        case 'title-desc':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;
            
        default:
            $args['orderby'] = 'menu_order title';
            $args['order']   = 'ASC';
    }
    
    return $args;
}

/**
 * Получение доступных счетчиков для фильтров (старая версия для обратной совместимости)
 */
function severcon_get_available_filter_counts($category_id, $active_filters) {
    // Используем новую оптимизированную функцию
    return severcon_get_optimized_filter_counts($category_id, $active_filters);
}

/**
 * Подсчет товаров с учетом фильтров (старая версия для обратной совместимости)
 */
function severcon_count_products_with_filters($category_id, $current_taxonomy, $current_term_slug, $active_filters) {
    // Используем новую оптимизированную функцию
    return severcon_optimized_count_products($category_id, $current_taxonomy, $current_term_slug, $active_filters);
}

// ============================================================================
// 6. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ (должны быть в security.php)
// ============================================================================

// Если этих функций нет в security.php, они будут определены здесь как запасной вариант
if (!function_exists('severcon_verify_ajax_request')) {
    /**
     * Проверка AJAX запроса
     */
    function severcon_verify_ajax_request($nonce_action) {
        if (!check_ajax_referer($nonce_action, 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Ошибка безопасности', 'severcon'),
                'code'    => 'invalid_nonce'
            ));
            return false;
        }
        return true;
    }
}

if (!function_exists('severcon_get_post_var')) {
    /**
     * Безопасное получение переменной из POST
     */
    function severcon_get_post_var($key, $default = '') {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
}

if (!function_exists('severcon_validate_id')) {
    /**
     * Валидация ID
     */
    function severcon_validate_id($value) {
        $value = intval($value);
        return $value > 0 ? $value : 0;
    }
}

if (!function_exists('severcon_clean_input')) {
    /**
     * Очистка входных данных
     */
    function severcon_clean_input($data) {
        if (is_array($data)) {
            return array_map('severcon_clean_input', $data);
        }
        return is_scalar($data) ? sanitize_text_field($data) : $data;
    }
}
