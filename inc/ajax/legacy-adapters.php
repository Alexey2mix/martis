<?php
/**
 * Адаптеры для старых AJAX endpoint'ов
 * Обеспечивает обратную совместимость
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Адаптер для старого endpoint'а фильтрации
 */
function severcon_legacy_filter_adapter() {
    // Проверяем, это запрос к старому endpoint'у
    if (!doing_action('wp_ajax_filter_category_products') && 
        !doing_action('wp_ajax_nopriv_filter_category_products')) {
        return;
    }
    
    // Получаем данные из старого формата
    $request_data = [
        'action' => 'filter_products',
        'category_id' => $_POST['category_id'] ?? 0,
        'page' => $_POST['page'] ?? 1,
        'per_page' => $_POST['per_page'] ?? 12,
        'orderby' => $_POST['orderby'] ?? 'menu_order',
        'filters' => $_POST['filters'] ?? [],
        'nonce' => $_POST['nonce'] ?? ''
    ];
    
    // Перенаправляем в роутер
    $router = Severcon_AJAX_Router::get_instance();
    $router->handle_request();
}

/**
 * Адаптер для старого endpoint'а счетчиков
 */
function severcon_legacy_counts_adapter() {
    if (!doing_action('wp_ajax_update_filter_counts') && 
        !doing_action('wp_ajax_nopriv_update_filter_counts')) {
        return;
    }
    
    $request_data = [
        'action' => 'update_filter_counts',
        'category_id' => $_POST['category_id'] ?? 0,
        'filters' => $_POST['filters'] ?? [],
        'nonce' => $_POST['nonce'] ?? ''
    ];
    
    $router = Severcon_AJAX_Router::get_instance();
    $router->handle_request();
}

// Регистрируем адаптеры для обратной совместимости
add_action('wp_ajax_filter_category_products', 'severcon_legacy_filter_adapter');
add_action('wp_ajax_nopriv_filter_category_products', 'severcon_legacy_filter_adapter');
add_action('wp_ajax_update_filter_counts', 'severcon_legacy_counts_adapter');
add_action('wp_ajax_nopriv_update_filter_counts', 'severcon_legacy_counts_adapter');
