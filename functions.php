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
    
    // Инициализация обработчиков фильтров
    add_action( 'init', function() {
        if ( function_exists( 'severcon_init_filter_handlers' ) ) {
            severcon_init_filter_handlers();
        }
    }, 20 );
} else {
    add_action( 'admin_notices', function() {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="notice notice-warning"><p>';
            echo 'Тема Severcon требует установленного плагина WooCommerce для полной функциональности.';
            echo '</p></div>';
        }
    } );
}

// 5. AJAX ОБРАБОТЧИКИ
severcon_safe_require( 'inc/ajax/request-handler.php' );
severcon_safe_require( 'inc/ajax/news-handler.php' );
severcon_safe_require('inc/ajax/ajax-router.php');

// Инициализируем роутер
$severcon_ajax_router = severcon_init_ajax_router();


// 6. СТИЛИ И СКРИПТЫ
severcon_safe_require( 'inc/setup/assets.php' );

/**
 * ============================================================================
 * ОБЩИЕ ФУНКЦИИ ТЕМЫ
 * ============================================================================
 */

function severcon_is_woocommerce_page() {
    return function_exists( 'is_woocommerce' ) && is_woocommerce();
}

function severcon_get_current_category_id() {
    if ( ! severcon_is_woocommerce_page() ) {
        return 0;
    }
    
    $queried_object = get_queried_object();
    return ( $queried_object instanceof WP_Term ) ? $queried_object->term_id : 0;
}

function severcon_log( $message, $data = null ) {
    if ( ! WP_DEBUG ) {
        return;
    }
    
    $log_entry = '[' . current_time( 'mysql' ) . '] ' . $message;
    
    if ( $data !== null ) {
        $log_entry .= ' ' . print_r( $data, true );
    }
    
    error_log( $log_entry );
}

/**
 * ============================================================================
 * ФИЛЬТРЫ И ДЕЙСТВИЯ
 * ============================================================================
 */

add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
} );

add_action( 'after_setup_theme', function() {
    load_theme_textdomain( 'severcon', SEVERCON_THEME_PATH . '/languages' );
}, 5 );

/**
 * ============================================================================
 * ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ДЛЯ РАЗРАБОТЧИКОВ
 * ============================================================================
 */

if ( ! function_exists( 'dd' ) ) {
    function dd( $variable ) {
        if ( ! WP_DEBUG ) {
            return;
        }
        
        echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ccc; margin: 10px;">';
        var_dump( $variable );
        echo '</pre>';
        die();
    }
}
