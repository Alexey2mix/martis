<?php
/**
 * Базовые настройки темы
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Настройки темы при активации
 */
function severcon_theme_setup() {
    // Поддержка возможностей WordPress
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Поддержка WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Регистрация меню
    register_nav_menus(array(
        'primary' => __('Основное меню', 'severcon'),
        'footer'  => __('Меню в футере', 'severcon'),
        'mobile'  => __('Мобильное меню', 'severcon'),
    ));
    
    // Регистрация областей виджетов
    severcon_register_widget_areas();
    
    // Добавление размеров изображений
    severcon_add_image_sizes();
}
add_action('after_setup_theme', 'severcon_theme_setup');

/**
 * Регистрация областей виджетов
 */
function severcon_register_widget_areas() {
    // Основной сайдбар магазина
    register_sidebar(array(
        'name'          => __('Сайдбар магазина', 'severcon'),
        'id'            => 'shop-sidebar',
        'description'   => __('Виджеты для страниц магазина', 'severcon'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Футер виджеты
    $footer_widgets = array(
        'logo'     => __('Футер - Логотип', 'severcon'),
        'catalog'  => __('Футер - Каталог', 'severcon'),
        'support'  => __('Футер - Поддержка', 'severcon'),
        'about'    => __('Футер - О компании', 'severcon'),
        'contacts' => __('Футер - Контакты', 'severcon'),
    );
    
    foreach ($footer_widgets as $key => $name) {
        register_sidebar(array(
            'name'          => $name,
            'id'            => 'footer-' . $key,
            'description'   => sprintf(__('Виджеты в футере: %s', 'severcon'), $key),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ));
    }
}

/**
 * Добавление размеров изображений
 */
function severcon_add_image_sizes() {
    // Новости
    add_image_size('severcon-news-large', 800, 500, true);
    add_image_size('severcon-news-medium', 400, 250, true);
    add_image_size('severcon-news-small', 300, 200, true);
    
    // Товары
    add_image_size('severcon-product-large', 600, 600, true);
    add_image_size('severcon-product-medium', 400, 400, true);
    add_image_size('severcon-product-small', 300, 300, true);
    add_image_size('severcon-product-thumb', 150, 150, true);
    
    // Галерея
    add_image_size('severcon-gallery-main', 1200, 800, true);
    add_image_size('severcon-gallery-thumb', 100, 100, true);
}

/**
 * Отключение ненужных функций WordPress
 */
function severcon_disable_unnecessary_features() {
    // Отключение эмодзи
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Отключение REST API для неавторизованных пользователей
    if (!is_user_logged_in()) {
        add_filter('rest_authentication_errors', function($result) {
            if (!empty($result)) {
                return $result;
            }
            if (!is_user_logged_in()) {
                return new WP_Error('rest_not_logged_in', 'Вы должны быть авторизованы.', array('status' => 401));
            }
            return $result;
        });
    }
}
add_action('init', 'severcon_disable_unnecessary_features');