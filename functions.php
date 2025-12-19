<?php
/**
 * Martis Theme Functions
 * 
 * @package Martis
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
define( 'MARTIS_THEME_VERSION', '1.0.0' );
define( 'MARTIS_THEME_PATH', get_template_directory() );
define( 'MARTIS_THEME_URI', get_template_directory_uri() );

/**
 * ============================================================================
 * ЗАГРУЗКА ОСНОВНЫХ ФАЙЛОВ С ОБРАБОТКОЙ ОШИБОК
 * ============================================================================
 */

/**
 * Безопасное подключение файла с проверкой его существования
 * 
 * @param string $file_path Относительный путь к файлу от папки темы
 * @return bool Успешно ли подключен файл
 */
function martis_safe_require( $file_path ) {
    $full_path = MARTIS_THEME_PATH . '/' . $file_path;
    
    if ( ! file_exists( $full_path ) ) {
        // В режиме разработки выводим предупреждение
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
 * ОЧЕРЕДНОСТЬ ПОДКЛЮЧЕНИЯ ФАЙЛОВ (ВАЖНО!)
 * ============================================================================
 */

// 1. НАСТРОЙКА ТЕМЫ (самое первое)
martis_safe_require( 'inc/setup/theme-setup.php' );    // Регистрация поддержки, меню и т.д.

// 2. УТИЛИТЫ И БЕЗОПАСНОСТЬ
martis_safe_require( 'inc/utils/helpers.php' );        // Вспомогательные функции
martis_safe_require( 'inc/utils/security.php' );       // Функции безопасности

// 3. КОМПОНЕНТЫ ТЕМЫ
martis_safe_require( 'inc/components/breadcrumbs.php' );
martis_safe_require( 'inc/components/filter-group.php' );
martis_safe_require( 'inc/components/news-card.php' );
martis_safe_require( 'inc/components/product-card.php' );
martis_safe_require( 'inc/template-functions.php' );   // Функции для шаблонов

// 4. WOOCOMMERCE ИНТЕГРАЦИЯ
if ( class_exists( 'WooCommerce' ) ) {
    martis_safe_require( 'inc/woocommerce/customizations.php' );     // Настройки WooCommerce
    martis_safe_require( 'inc/woocommerce/filters-handler.php' );    // AJAX фильтрация
    martis_safe_require( 'inc/woocommerce/quick-view-handler.php' ); // Быстрый просмотр
    
    // Инициализация обработчиков фильтров
    add_action( 'init', function() {
        if ( function_exists( 'severcon_init_filter_handlers' ) ) {
            severcon_init_filter_handlers();
        }
    }, 20 );
} else {
    // Можно добавить уведомление для администратора
    add_action( 'admin_notices', function() {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="notice notice-warning"><p>';
            echo 'Тема Martis требует установленного плагина WooCommerce для полной функциональности.';
            echo '</p></div>';
        }
    } );
}

// 5. AJAX ОБРАБОТЧИКИ
martis_safe_require( 'inc/ajax/request-handler.php' ); // Основной AJAX обработчик
martis_safe_require( 'inc/ajax/news-handler.php' );    // AJAX для новостей

// 6. СТИЛИ И СКРИПТЫ (подключается последним, зависит от всех предыдущих)
martis_safe_require( 'inc/setup/assets.php' );

/**
 * ============================================================================
 * ОБЩИЕ ФУНКЦИИ ТЕМЫ
 * ============================================================================
 */

/**
 * Проверка, является ли страница WooCommerce
 */
function martis_is_woocommerce_page() {
    return function_exists( 'is_woocommerce' ) && is_woocommerce();
}

/**
 * Получение ID текущей категории товаров
 */
function martis_get_current_category_id() {
    if ( ! martis_is_woocommerce_page() ) {
        return 0;
    }
    
    $queried_object = get_queried_object();
    return ( $queried_object instanceof WP_Term ) ? $queried_object->term_id : 0;
}

/**
 * Логирование для отладки (работает только при WP_DEBUG = true)
 */
function martis_log( $message, $data = null ) {
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

// Пример: добавление поддержки SVG
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
} );

// Инициализация темы
add_action( 'after_setup_theme', function() {
    // Локализация
    load_theme_textdomain( 'martis', MARTIS_THEME_PATH . '/languages' );
    
    // Дополнительные настройки могут быть здесь
    // или в theme-setup.php
}, 5 );

/**
 * ============================================================================
 * ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ДЛЯ РАЗРАБОТЧИКОВ
 * ============================================================================
 */

if ( ! function_exists( 'dd' ) ) {
    /**
     * Dump and die - для отладки
     */
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
