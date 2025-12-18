<?php
/**
 * Подключение стилей и скриптов
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация и подключение скриптов
 */
function severcon_register_assets() {
    $theme_version = wp_get_theme()->get('Version');
    $minified = defined('WP_DEBUG') && WP_DEBUG ? '' : '.min';
    
    // ===== СТИЛИ =====
    
    // Font Awesome 6
    wp_register_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Основной стиль темы
    wp_register_style('severcon-main', get_template_directory_uri() . '/assets/css/main' . $minified . '.css', array(), $theme_version);
    
    // WooCommerce стили
    if (class_exists('WooCommerce')) {
        wp_register_style('severcon-woocommerce', get_template_directory_uri() . '/assets/css/woocommerce' . $minified . '.css', array('severcon-main'), $theme_version);
    }
    
    // ===== СКРИПТЫ =====
    
    // jQuery уже включен в WordPress
    
    // Основной скрипт темы
    wp_register_script('severcon-main', get_template_directory_uri() . '/assets/js/main' . $minified . '.js', array('jquery'), $theme_version, true);
    
    // Модули
    wp_register_script('severcon-filters', get_template_directory_uri() . '/assets/js/modules/filters' . $minified . '.js', array('jquery', 'severcon-main'), $theme_version, true);
    wp_register_script('severcon-modals', get_template_directory_uri() . '/assets/js/modules/modals' . $minified . '.js', array('jquery', 'severcon-main'), $theme_version, true);
    wp_register_script('severcon-sliders', get_template_directory_uri() . '/assets/js/modules/sliders' . $minified . '.js', array('jquery', 'severcon-main'), $theme_version, true);
}
add_action('init', 'severcon_register_assets');

/**
 * Подключение скриптов на фронтенде
 */
function severcon_enqueue_frontend_assets() {
    // Стили
    wp_enqueue_style('font-awesome');
    wp_enqueue_style('severcon-main');
    
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
        wp_enqueue_style('severcon-woocommerce');
    }
    
    // Скрипты
    wp_enqueue_script('severcon-main');
    
    // Подключаем модули по необходимости
    if (is_archive('product') || is_product_category() || is_product_tag()) {
        wp_enqueue_script('severcon-filters');
    }
    
    if (is_singular('product')) {
        wp_enqueue_script('severcon-modals');
    }
    
    if (is_front_page() || is_home()) {
        wp_enqueue_script('severcon-sliders');
    }
    
    // Локализация для AJAX
    severcon_localize_scripts();
}
add_action('wp_enqueue_scripts', 'severcon_enqueue_frontend_assets');

/**
 * Локализация скриптов
 */
function severcon_localize_scripts() {
    $ajax_params = array(
        'ajax_url'    => admin_url('admin-ajax.php'),
        'ajax_nonce'  => wp_create_nonce('severcon_ajax_nonce'),
        'home_url'    => home_url('/'),
        'theme_url'   => get_template_directory_uri(),
        'is_mobile'   => wp_is_mobile(),
        'user_id'     => get_current_user_id(),
        'i18n'        => array(
            'loading'        => __('Загрузка...', 'severcon'),
            'no_products'    => __('Товаров не найдено', 'severcon'),
            'apply_filters'  => __('Применить фильтры', 'severcon'),
            'reset_filters'  => __('Сбросить фильтры', 'severcon'),
            'view_details'   => __('Подробнее', 'severcon'),
            'request_price'  => __('Запросить цену', 'severcon'),
            'close'          => __('Закрыть', 'severcon'),
            'error'          => __('Произошла ошибка', 'severcon'),
            'success'        => __('Успешно', 'severcon'),
        )
    );
    
    // Добавляем данные для WooCommerce
    if (class_exists('WooCommerce')) {
        $ajax_params['wc'] = array(
            'cart_url'      => wc_get_cart_url(),
            'checkout_url'  => wc_get_checkout_url(),
            'shop_url'      => wc_get_page_permalink('shop'),
            'currency'      => get_woocommerce_currency_symbol(),
        );
    }
    
    wp_localize_script('severcon-main', 'severcon_ajax', $ajax_params);
    
    // Дополнительная локализация для фильтров
    if (wp_script_is('severcon-filters', 'enqueued')) {
        wp_localize_script('severcon-filters', 'severcon_filters', array(
            'nonce' => wp_create_nonce('severcon_filter_nonce'),
        ));
    }
}

/**
 * Подключение стилей в админке
 */
function severcon_enqueue_admin_assets($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_style('severcon-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), wp_get_theme()->get('Version'));
    }
}
add_action('admin_enqueue_scripts', 'severcon_enqueue_admin_assets');

/**
 * Добавление атрибутов к скриптам
 */
function severcon_script_loader_tag($tag, $handle, $src) {
    // Добавляем атрибут defer для определенных скриптов
    $defer_scripts = array('severcon-main', 'severcon-filters', 'severcon-modals');
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'severcon_script_loader_tag', 10, 3);

/**
 * Предзагрузка критичных ресурсов
 */
function severcon_preload_critical_assets() {
    if (is_front_page()) {
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/opensans.woff2" as="font" type="font/woff2" crossorigin>';
        echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/images/logo.svg" as="image" type="image/svg+xml">';
    }
}
add_action('wp_head', 'severcon_preload_critical_assets', 1);