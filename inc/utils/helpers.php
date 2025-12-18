<?php
/**
 * Вспомогательные функции
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Безопасное получение значения из массива
 */
function severcon_get_array_value($array, $key, $default = '') {
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    }
    return $default;
}

/**
 * Безопасное получение GET параметра
 */
function severcon_get_get_var($key, $default = '', $sanitize = 'text_field') {
    if (isset($_GET[$key])) {
        $value = $_GET[$key];
        
        switch ($sanitize) {
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'html':
                return wp_kses_post($value);
            default:
                return sanitize_text_field($value);
        }
    }
    
    return $default;
}

/**
 * Безопасное получение POST параметра
 */
function severcon_get_post_var($key, $default = '', $sanitize = 'text_field') {
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
        
        switch ($sanitize) {
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'email':
                return sanitize_email($value);
            case 'url':
                return esc_url_raw($value);
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'html':
                return wp_kses_post($value);
            default:
                return sanitize_text_field($value);
        }
    }
    
    return $default;
}

/**
 * Проверка AJAX запроса
 */
function severcon_verify_ajax_request($nonce_action = 'severcon_ajax_nonce') {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $nonce_action)) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности. Обновите страницу.', 'severcon'),
            'code'    => 'invalid_nonce'
        ), 403);
    }
    
    // Проверка реферера
    $referer = wp_get_referer();
    if (!$referer || !strpos($referer, home_url())) {
        wp_send_json_error(array(
            'message' => __('Неверный источник запроса.', 'severcon'),
            'code'    => 'invalid_referer'
        ), 403);
    }
    
    // Проверка частоты запросов (защита от DDoS)
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'severcon_ajax_limit_' . md5($ip . $nonce_action);
    
    $request_count = get_transient($transient_key);
    if ($request_count && $request_count > 10) {
        wp_send_json_error(array(
            'message' => __('Слишком много запросов. Подождите немного.', 'severcon'),
            'code'    => 'rate_limit'
        ), 429);
    }
    
    if (!$request_count) {
        $request_count = 0;
    }
    
    set_transient($transient_key, $request_count + 1, 60); // 60 секунд
    
    return true;
}

/**
 * Форматирование телефона
 */
function severcon_format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 11) {
        return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', $phone);
    }
    
    return $phone;
}

/**
 * Получение ID текущей категории товаров
 */
function severcon_get_current_category_id() {
    if (is_product_category()) {
        $cat = get_queried_object();
        return $cat->term_id;
    }
    
    return 0;
}

/**
 * Проверка, является ли страница WooCommerce
 */
function severcon_is_woocommerce_page() {
    if (!class_exists('WooCommerce')) {
        return false;
    }
    
    return is_woocommerce() || 
           is_cart() || 
           is_checkout() || 
           is_account_page() || 
           is_wc_endpoint_url();
}

/**
 * Получение настроек темы с fallback значениями
 */
function severcon_get_theme_option($key, $default = '') {
    $value = get_theme_mod($key, $default);
    
    if (empty($value) && $default !== '') {
        return $default;
    }
    
    return $value;
}

/**
 * Отладочная функция
 */
function severcon_debug($data, $exit = false) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; margin: 20px 0;">';
    print_r($data);
    echo '</pre>';
    
    if ($exit) {
        exit;
    }
}

/**
 * Получение хлебных крошек для товара
 */
function severcon_get_product_breadcrumbs($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    $breadcrumbs = array(
        array(
            'title' => __('Главная', 'severcon'),
            'url'   => home_url('/')
        ),
        array(
            'title' => __('Каталог', 'severcon'),
            'url'   => get_permalink(wc_get_page_id('shop'))
        )
    );
    
    $terms = wp_get_post_terms($product_id, 'product_cat');
    
    if ($terms && !is_wp_error($terms)) {
        $main_term = $terms[0];
        $ancestors = get_ancestors($main_term->term_id, 'product_cat');
        
        if ($ancestors) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $breadcrumbs[] = array(
                    'title' => esc_html($ancestor->name),
                    'url'   => get_term_link($ancestor)
                );
            }
        }
        
        $breadcrumbs[] = array(
            'title' => esc_html($main_term->name),
            'url'   => get_term_link($main_term)
        );
    }
    
    $breadcrumbs[] = array(
        'title' => get_the_title($product_id),
        'url'   => ''
    );
    
    return $breadcrumbs;
}

/**
 * Создание неce для фильтров
 */
function severcon_create_filter_nonce() {
    return wp_create_nonce('severcon_filter_nonce');
}

/**
 * Получение URL с параметрами фильтрации
 */
function severcon_build_filter_url($filters = array(), $base_url = null) {
    if (!$base_url) {
        $base_url = $_SERVER['REQUEST_URI'];
    }
    
    $url_parts = parse_url($base_url);
    $query_params = array();
    
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query_params);
    }
    
    // Удаляем старые параметры фильтров
    foreach ($query_params as $key => $value) {
        if (strpos($key, 'filter_') === 0) {
            unset($query_params[$key]);
        }
    }
    
    // Добавляем новые фильтры
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $query_params['filter_' . $key] = $value;
        }
    }
    
    // Строим новый URL
    $new_query = http_build_query($query_params);
    $new_url = $url_parts['path'] . ($new_query ? '?' . $new_query : '');
    
    return $new_url;
}