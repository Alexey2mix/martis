<?php
/**
 * Обработчик фильтрации товаров
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Инициализация AJAX обработчиков фильтрации
 */
function severcon_init_filter_handlers() {
    add_action('wp_ajax_filter_category_products', 'severcon_ajax_filter_category_products');
    add_action('wp_ajax_nopriv_filter_category_products', 'severcon_ajax_filter_category_products');
    
    add_action('wp_ajax_update_filter_counts', 'severcon_ajax_update_filter_counts');
    add_action('wp_ajax_nopriv_update_filter_counts', 'severcon_ajax_update_filter_counts');
}
add_action('init', 'severcon_init_filter_handlers');

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
 * AJAX обновление счетчиков фильтров
 */
function severcon_ajax_update_filter_counts() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_filter_nonce')) {
        return;
    }
    
    $category_id = severcon_validate_id(severcon_get_post_var('category_id', 0));
    $active_filters = severcon_clean_input(severcon_get_post_var('filters', array()));
    
    if (!$category_id) {
        wp_send_json_error(array(
            'message' => __('Категория не указана', 'severcon'),
            'code'    => 'invalid_category'
        ));
    }
    
    // Получение доступных атрибутов
    $available_counts = severcon_get_available_filter_counts($category_id, $active_filters);
    
    wp_send_json_success($available_counts);
    wp_die();
}

/**
 * Получение доступных счетчиков для фильтров
 */
function severcon_get_available_filter_counts($category_id, $active_filters) {
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    $available_counts = array();
    
    foreach ($attribute_taxonomies as $attribute) {
        $taxonomy = 'pa_' . $attribute->attribute_name;
        
        // Получаем термины для этого атрибута
        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'id=>slug'
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }
        
        foreach ($terms as $term_id => $term_slug) {
            // Считаем товары с учетом текущих фильтров
            $count = severcon_count_products_with_filters($category_id, $taxonomy, $term_slug, $active_filters);
            
            // Если товары есть или термин уже выбран - добавляем
            if ($count > 0 || severcon_is_term_selected($taxonomy, $term_slug, $active_filters)) {
                if (!isset($available_counts[$taxonomy])) {
                    $available_counts[$taxonomy] = array();
                }
                
                $available_counts[$taxonomy][$term_slug] = $count;
            }
        }
    }
    
    return $available_counts;
}

/**
 * Подсчет товаров с учетом фильтров
 */
function severcon_count_products_with_filters($category_id, $current_taxonomy, $current_term_slug, $active_filters) {
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array(
            'relation' => 'AND'
        )
    );
    
    // Категория
    $args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => $category_id,
        'include_children' => true
    );
    
    // Текущий термин
    $args['tax_query'][] = array(
        'taxonomy' => $current_taxonomy,
        'field'    => 'slug',
        'terms'    => $current_term_slug
    );
    
    // Остальные активные фильтры (кроме текущего атрибута)
    foreach ($active_filters as $taxonomy => $terms) {
        if ($taxonomy !== $current_taxonomy && !empty($terms) && is_array($terms)) {
            $args['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_text_field', $terms),
                'operator' => 'IN'
            );
        }
    }
    
    $query = new WP_Query($args);
    return $query->found_posts;
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

/**
 * Генерация HTML для фильтров
 */
function severcon_generate_filters_html($category_id) {
    if (!$category_id) {
        return '';
    }
    
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    
    if (empty($attribute_taxonomies)) {
        return '<p class="no-filters">' . __('Фильтры не настроены', 'severcon') . '</p>';
    }
    
    ob_start();
    ?>
    <div class="severcon-filters-container" data-category-id="<?php echo esc_attr($category_id); ?>">
        <div class="filters-header">
            <h3 class="filters-title"><?php _e('Фильтры', 'severcon'); ?></h3>
            <button type="button" class="clear-all-filters" aria-label="<?php _e('Очистить все фильтры', 'severcon'); ?>">
                <?php _e('Очистить все', 'severcon'); ?>
            </button>
        </div>
        
        <div class="filters-body">
            <?php foreach ($attribute_taxonomies as $attribute) : 
                $taxonomy = 'pa_' . $attribute->attribute_name;
                $terms = get_terms(array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => true,
                ));
                
                if (is_wp_error($terms) || empty($terms)) {
                    continue;
                }
            ?>
                <div class="filter-group" data-attribute="<?php echo esc_attr($attribute->attribute_name); ?>">
                    <div class="filter-group-header">
                        <h4 class="filter-group-title">
                            <?php echo esc_html($attribute->attribute_label); ?>
                        </h4>
                        <button type="button" class="filter-group-toggle" aria-expanded="false">
                            <span class="toggle-icon">+</span>
                        </button>
                    </div>
                    
                    <div class="filter-group-body" style="display: none;">
                        <div class="filter-search">
                            <input type="text" 
                                   class="filter-search-input" 
                                   placeholder="<?php _e('Поиск...', 'severcon'); ?>"
                                   data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                        </div>
                        
                        <div class="filter-items">
                            <?php foreach ($terms as $term) : 
                                $count = severcon_count_products_with_filters($category_id, $taxonomy, $term->slug, array());
                            ?>
                                <label class="filter-item <?php echo $count === 0 ? 'disabled' : ''; ?>" 
                                       data-term-slug="<?php echo esc_attr($term->slug); ?>"
                                       data-term-id="<?php echo esc_attr($term->term_id); ?>">
                                    <input type="checkbox" 
                                           class="filter-checkbox" 
                                           name="<?php echo esc_attr($taxonomy); ?>[]" 
                                           value="<?php echo esc_attr($term->slug); ?>"
                                           <?php echo $count === 0 ? 'disabled' : ''; ?>>
                                    <span class="filter-item-text"><?php echo esc_html($term->name); ?></span>
                                    <span class="filter-item-count">(<?php echo $count; ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="filters-footer">
            <button type="button" class="apply-filters btn btn-primary">
                <?php _e('Применить фильтры', 'severcon'); ?>
            </button>
            <button type="button" class="reset-filters btn btn-secondary">
                <?php _e('Сбросить', 'severcon'); ?>
            </button>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Шорткод для вывода фильтров
 */
function severcon_filters_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category_id' => 0,
    ), $atts, 'severcon_filters');
    
    $category_id = intval($atts['category_id']);
    
    if (!$category_id) {
        $category_id = severcon_get_current_category_id();
    }
    
    return severcon_generate_filters_html($category_id);
}
add_shortcode('severcon_filters', 'severcon_filters_shortcode');