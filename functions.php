<?php
/**
 * Severcon Theme Functions
 * 
 * @package Severcon
 */

// Безопасность: предотвращаем прямой доступ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ============================================================================
 * КОНСТАНТЫ ТЕМЫ
 * ============================================================================
 */
define( 'SEVERCON_THEME_VERSION', '1.0.0' );
define( 'SEVERCON_THEME_PATH', get_template_directory() );
define( 'SEVERCON_THEME_URI', get_template_directory_uri() );

/**
 * ============================================================================
 * ЗАГРУЗКА ОСНОВНЫХ ФАЙЛОВ С ОБРАБОТКОЙ ОШИБОК
 * ============================================================================
 */

/**
 * Безопасное подключение файла с проверкой его существования
 */
function severcon_safe_require( $file_path ) {
    $full_path = SEVERCON_THEME_PATH . '/' . $file_path;
    
    if ( ! file_exists( $full_path ) ) {
        if ( WP_DEBUG ) {
            trigger_error(
                sprintf( 'Файл темы не найден: %s', $file_path ),
                E_USER_WARNING
            );
        }
        return false;
    }
    
    require_once $full_path;
    return true;
}

/**
 * ============================================================================
 * ОЧЕРЕДНОСТЬ ПОДКЛЮЧЕНИЯ ФАЙЛОВ
 * ============================================================================
 */

// 1. НАСТРОЙКА ТЕМЫ
severcon_safe_require( 'inc/setup/theme-setup.php' );

// 2. УТИЛИТЫ И БЕЗОПАСНОСТЬ
severcon_safe_require( 'inc/utils/helpers.php' );
severcon_safe_require( 'inc/utils/security.php' );

// 3. КОМПОНЕНТЫ ТЕМЫ
severcon_safe_require( 'inc/components/breadcrumbs.php' );
severcon_safe_require( 'inc/components/filter-group.php' );
severcon_safe_require( 'inc/components/news-card.php' );
severcon_safe_require( 'inc/components/product-card.php' );
severcon_safe_require( 'inc/template-functions.php' );

// 4. WOOCOMMERCE ИНТЕГРАЦИЯ
if ( class_exists( 'WooCommerce' ) ) {
    severcon_safe_require( 'inc/woocommerce/customizations.php' );
    severcon_safe_require( 'inc/woocommerce/filters-handler.php' );
    severcon_safe_require( 'inc/woocommerce/quick-view-handler.php' );
    
    // AJAX адаптеры для обратной совместимости
    severcon_safe_require( 'inc/ajax/legacy-adapters.php' );
} else {
    add_action( 'admin_notices', function() {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="notice notice-warning"><p>';
            echo 'Тема Severcon требует установленного плагина WooCommerce для полной функциональности.';
            echo '</p></div>';
        }
    } );
}

// 5. AJAX СИСТЕМА
severcon_safe_require( 'inc/ajax/ajax-router.php' );
severcon_safe_require( 'inc/ajax/request-handler.php' );
severcon_safe_require( 'inc/ajax/news-handler.php' );

// 6. СТИЛИ И СКРИПТЫ
severcon_safe_require( 'inc/setup/assets.php' );

/**
 * ============================================================================
 * ОБЩИЕ ФУНКЦИИ ТЕМЫ
 * ============================================================================
 */

/**
 * Проверка, является ли страница WooCommerce
 */
function severcon_is_woocommerce_page() {
    return function_exists( 'is_woocommerce' ) && is_woocommerce();
}

/**
 * Получение ID текущей категории товаров
 */
function severcon_get_current_category_id() {
    if ( ! severcon_is_woocommerce_page() ) {
        return 0;
    }
    
    $queried_object = get_queried_object();
    return ( $queried_object instanceof WP_Term ) ? $queried_object->term_id : 0;
}

/**
 * Получение типа текущей страницы
 */
function severcon_get_page_type() {
    if ( is_front_page() ) {
        return 'front_page';
    } elseif ( is_home() ) {
        return 'blog';
    } elseif ( is_singular( 'product' ) ) {
        return 'single_product';
    } elseif ( is_product_category() || is_product_tag() || is_shop() ) {
        return 'product_archive';
    } elseif ( is_single() ) {
        return 'single_post';
    } elseif ( is_page() ) {
        return 'page';
    } elseif ( is_archive() ) {
        return 'archive';
    } elseif ( is_search() ) {
        return 'search';
    } elseif ( is_404() ) {
        return '404';
    }
    
    return 'unknown';
}

/**
 * Получение данных для передачи в JavaScript
 */
function severcon_get_js_config() {
    $config = [
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'severcon_ajax_nonce' ),
        'loading'    => __( 'Загрузка...', 'severcon' ),
        'theme_url'  => SEVERCON_THEME_URI,
        'rest_url'   => esc_url_raw( rest_url() ),
        'rest_nonce' => wp_create_nonce( 'wp_rest' ),
    ];
    
    // Добавляем ID категории на страницах WooCommerce
    if ( severcon_is_woocommerce_page() ) {
        $config['category_id'] = severcon_get_current_category_id();
    }
    
    // Добавляем ID товара на странице товара
    if ( is_singular( 'product' ) ) {
        $config['product_id'] = get_the_ID();
    }
    
    // Добавляем настройки отладки
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        $config['debug'] = true;
    }
    
    return apply_filters( 'severcon_js_config', $config );
}

/**
 * Логирование для отладки (работает только при WP_DEBUG = true)
 */
function severcon_log( $message, $data = null ) {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }
    
    $log_entry = '[' . current_time( 'mysql' ) . '] ' . $message;
    
    if ( $data !== null ) {
        // Безопасный вывод любых данных
        $log_entry .= ' ' . wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    }
    
    error_log( $log_entry );
}

/**
 * Получение HTML-атрибутов для body
 */
function severcon_get_body_attributes() {
    $attributes = [];
    
    // Тип страницы
    $attributes['data-page-type'] = severcon_get_page_type();
    
    // ID категории для страниц товаров
    if ( severcon_is_woocommerce_page() ) {
        $category_id = severcon_get_current_category_id();
        if ( $category_id ) {
            $attributes['data-category-id'] = $category_id;
        }
    }
    
    // Режим отладки
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        $attributes['data-debug-mode'] = 'true';
    }
    
    // Язык сайта
    $attributes['data-lang'] = get_bloginfo( 'language' );
    
    // Собираем атрибуты в строку
    $output = '';
    foreach ( $attributes as $key => $value ) {
        $output .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
    }
    
    return trim( $output );
}

/**
 * ============================================================================
 * ФИЛЬТРЫ И ДЕЙСТВИЯ
 * ============================================================================
 */

// Добавление поддержки SVG
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
} );

// Добавление классов к body
add_filter( 'body_class', function( $classes ) {
    // Добавляем тип страницы как класс
    $page_type = severcon_get_page_type();
    if ( $page_type ) {
        $classes[] = 'page-type-' . $page_type;
    }
    
    // Добавляем класс для мобильных устройств
    if ( wp_is_mobile() ) {
        $classes[] = 'is-mobile';
    }
    
    // Добавляем класс если включен WooCommerce
    if ( class_exists( 'WooCommerce' ) ) {
        $classes[] = 'has-woocommerce';
    }
    
    return $classes;
} );

// Инициализация темы
add_action( 'after_setup_theme', function() {
    // Локализация
    load_theme_textdomain( 'severcon', SEVERCON_THEME_PATH . '/languages' );
    
    // Дополнительные настройки могут быть здесь
    do_action( 'severcon_theme_setup' );
}, 5 );

// Добавление данных в head
add_action( 'wp_head', function() {
    // Добавляем meta тег версии темы
    echo '<meta name="generator" content="Severcon Theme ' . esc_attr( SEVERCON_THEME_VERSION ) . '">' . "\n";
    
    // Добавляем favicon если есть
    $favicon = SEVERCON_THEME_URI . '/assets/images/favicon.ico';
    if ( file_exists( SEVERCON_THEME_PATH . '/assets/images/favicon.ico' ) ) {
        echo '<link rel="shortcut icon" href="' . esc_url( $favicon ) . '" type="image/x-icon">' . "\n";
    }
}, 1 );

/**
 * ============================================================================
 * ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ДЛЯ РАЗРАБОТЧИКОВ
 * ============================================================================
 */

if ( ! function_exists( 'dd' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    /**
     * Dump and die - для отладки
     * Работает только в режиме отладки
     */
    function dd( $variable ) {
        echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ccc; margin: 10px; border-left: 4px solid #dc3545; overflow: auto;">';
        var_dump( $variable );
        echo '</pre>';
        die();
    }
}

if ( ! function_exists( 'dump' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    /**
     * Dump without dying - для отладки
     * Работает только в режиме отладки
     */
    function dump( $variable ) {
        echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ccc; margin: 10px; border-left: 4px solid #17a2b8; overflow: auto;">';
        var_dump( $variable );
        echo '</pre>';
    }
}

/**
 * Проверка на странице ли мы админки
 */
function severcon_is_admin_page() {
    return is_admin() && ! wp_doing_ajax();
}

/**
 * Получение текущего URL
 */
function severcon_get_current_url() {
    global $wp;
    return home_url( add_query_arg( [], $wp->request ) );
}

/**
 * Сокращение текста
 */
function severcon_trim_text( $text, $length = 100, $more = '...' ) {
    if ( mb_strlen( $text ) > $length ) {
        $text = mb_substr( $text, 0, $length ) . $more;
    }
    return $text;
}
