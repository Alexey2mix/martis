<?php
/**
 * WooCommerce кастомизации для темы Severcon
 * 
 * @package Severcon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================================
// ИНИЦИАЛИЗАЦИЯ WOOCOMMERCE ФУНКЦИЙ
// ============================================================================

add_action( 'init', 'severcon_init_woocommerce_customizations' );

function severcon_init_woocommerce_customizations() {
    
    // Проверяем, установлен ли WooCommerce
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    // ========================================================================
    // 1. НАСТРОЙКИ ТОВАРОВ И КАТАЛОГА
    // ========================================================================
    
    // Удаляем стандартные breadcrumbs WooCommerce
    // Наши breadcrumbs в inc/components/breadcrumbs.php
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
    
    // Удаляем рейтинг из цикла товаров
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    
    // Изменяем позицию кнопки "В корзину"
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 15 );
    
    // ========================================================================
    // 2. КОРЗИНА И КНОПКИ "КУПИТЬ"
    // ========================================================================
    
    // AJAX обновление корзины
    add_filter( 'woocommerce_add_to_cart_fragments', 'severcon_cart_fragments' );
    
    // Изменение текста кнопки "В корзину" в архивах
    add_filter( 'woocommerce_product_add_to_cart_text', 'severcon_custom_add_to_cart_text', 10, 2 );
    
    // Изменение текста кнопки на странице товара
    add_filter( 'woocommerce_product_single_add_to_cart_text', 'severcon_custom_single_add_to_cart_text' );
    
    // ========================================================================
    // 3. НАСТРОЙКИ ОТОБРАЖЕНИЯ
    // ========================================================================
    
    // Количество товаров на странице
    add_filter( 'loop_shop_per_page', 'severcon_products_per_page', 20 );
    
    // Количество колонок
    add_filter( 'loop_shop_columns', 'severcon_shop_columns' );
    
    // Миниатюры товаров
    add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'severcon_gallery_thumbnail_size' );
    
    // ========================================================================
    // 4. КАСТОМИЗАЦИЯ СТРАНИЦЫ ТОВАРА
    // ========================================================================
    
    // Изменение порядка элементов на странице товара
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 15 );
    
    // Перемещаем описание товара после атрибутов
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 35 );
    
    // Добавляем кнопку "Быстрый просмотр"
    add_action( 'woocommerce_after_shop_loop_item', 'severcon_add_quick_view_button', 20 );
    
    // ========================================================================
    // 5. ФИЛЬТРЫ И СОРТИРОВКА
    // ========================================================================
    
    // Удаляем стандартную сортировку если используем свою
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
    
    // Удаляем стандартный вывод количества товаров
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
    
    // ========================================================================
    // 6. ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ
    // ========================================================================
    
    // Включаем галерею товаров
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    
    // Отключаем стили WooCommerce по умолчанию
    add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
    
}

// ============================================================================
// ФУНКЦИИ КОРЗИНЫ
// ============================================================================

/**
 * AJAX обновление содержимого корзины
 */
function severcon_cart_fragments( $fragments ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return $fragments;
    }
    
    ob_start();
    ?>
    <span class="cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['span.cart-count'] = ob_get_clean();
    
    // Обновляем общую сумму
    ob_start();
    ?>
    <span class="cart-total">
        <?php echo WC()->cart->get_cart_total(); ?>
    </span>
    <?php
    $fragments['span.cart-total'] = ob_get_clean();
    
    return $fragments;
}

/**
 * Изменение текста кнопки "В корзину" в архивах товаров
 */
function severcon_custom_add_to_cart_text( $text, $product ) {
    if ( $product->is_type( 'variable' ) ) {
        return __( 'Выбрать вариант', 'severcon' );
    }
    
    if ( $product->is_type( 'external' ) ) {
        return __( 'Подробнее', 'severcon' );
    }
    
    if ( ! $product->is_in_stock() ) {
        return __( 'Нет в наличии', 'severcon' );
    }
    
    return __( 'В корзину', 'severcon' );
}

/**
 * Изменение текста кнопки на странице товара
 */
function severcon_custom_single_add_to_cart_text() {
    global $product;
    
    if ( ! $product->is_in_stock() ) {
        return __( 'Нет в наличии', 'severcon' );
    }
    
    return __( 'Добавить в корзину', 'severcon' );
}

// ============================================================================
// НАСТРОЙКИ ОТОБРАЖЕНИЯ
// ============================================================================

/**
 * Количество товаров на странице
 */
function severcon_products_per_page( $cols ) {
    return 12; // 12 товаров на странице
}

/**
 * Количество колонок в сетке товаров
 */
function severcon_shop_columns() {
    return 3; // 3 колонки на десктопе
}

/**
 * Размер миниатюр галереи
 */
function severcon_gallery_thumbnail_size( $size ) {
    return array(
        'width'  => 150,
        'height' => 150,
        'crop'   => 1,
    );
}

// ============================================================================
// ДОПОЛНИТЕЛЬНЫЕ ЭЛЕМЕНТЫ ИНТЕРФЕЙСА
// ============================================================================

/**
 * Добавление кнопки "Быстрый просмотр" в цикле товаров
 */
function severcon_add_quick_view_button() {
    global $product;
    
    if ( ! $product ) {
        return;
    }
    
    ?>
    <button type="button" 
            class="quick-view-button" 
            data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
            aria-label="<?php echo esc_attr( sprintf( __( 'Быстрый просмотр %s', 'severcon' ), $product->get_name() ) ); ?>">
        <?php _e( 'Быстрый просмотр', 'severcon' ); ?>
    </button>
    <?php
}

// ============================================================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================================================

/**
 * Проверка, включена ли поддержка WooCommerce
 */
function severcon_woocommerce_is_active() {
    return class_exists( 'WooCommerce' );
}

/**
 * Получение количества товаров в корзине
 */
function severcon_get_cart_count() {
    if ( ! severcon_woocommerce_is_active() ) {
        return 0;
    }
    
    return WC()->cart->get_cart_contents_count();
}

/**
 * Получение общей суммы корзины
 */
function severcon_get_cart_total() {
    if ( ! severcon_woocommerce_is_active() ) {
        return 0;
    }
    
    return WC()->cart->get_cart_total();
}

/**
 * Получение цены товара с учетом скидки
 */
function severcon_get_product_price_html( $product_id = null ) {
    if ( ! severcon_woocommerce_is_active() ) {
        return '';
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        return '';
    }
    
    return $product->get_price_html();
}

/**
 * Проверка, новый ли товар (добавлен менее N дней назад)
 */
function severcon_is_new_product( $product_id, $days = 7 ) {
    if ( ! severcon_woocommerce_is_active() ) {
        return false;
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        return false;
    }
    
    $date_created = $product->get_date_created();
    
    if ( ! $date_created ) {
        return false;
    }
    
    $now = new DateTime();
    $created = new DateTime( $date_created->format( 'Y-m-d H:i:s' ) );
    $interval = $now->diff( $created );
    
    return $interval->days <= $days;
}

/**
 * Получение рейтинга товара в виде HTML
 */
function severcon_get_product_rating_html( $product_id = null ) {
    if ( ! severcon_woocommerce_is_active() ) {
        return '';
    }
    
    $product = wc_get_product( $product_id );
    
    if ( ! $product ) {
        return '';
    }
    
    $rating_count = $product->get_rating_count();
    $average_rating = $product->get_average_rating();
    
    if ( $rating_count <= 0 ) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="severcon-product-rating">
        <div class="star-rating" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Рейтинг %s из 5', 'severcon' ), $average_rating ) ); ?>">
            <span style="width:<?php echo esc_attr( ( $average_rating / 5 ) * 100 ); ?>%">
                <?php printf( __( 'Рейтинг %s из 5', 'severcon' ), '<strong class="rating">' . esc_html( $average_rating ) . '</strong>' ); ?>
            </span>
        </div>
        <?php if ( $rating_count > 0 ) : ?>
            <span class="rating-count">
                (<?php echo esc_html( $rating_count ); ?>)
            </span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================================================
// ФИЛЬТРЫ ДЛЯ КАСТОМИЗАЦИИ
// ============================================================================

/**
 * Фильтр для изменения HTML вывода цены
 */
add_filter( 'woocommerce_get_price_html', function( $price, $product ) {
    if ( $product->is_on_sale() ) {
        $price = '<span class="price sale-price">' . $price . '</span>';
        $price .= '<span class="sale-badge">' . __( 'Скидка', 'severcon' ) . '</span>';
    }
    
    return $price;
}, 10, 2 );

/**
 * Фильтр для изменения количества связанных товаров
 */
add_filter( 'woocommerce_output_related_products_args', function( $args ) {
    $args['posts_per_page'] = 4; // 4 связанных товара
    $args['columns'] = 4; // 4 колонки
    return $args;
} );

/**
 * Фильтр для изменения количества товаров вверх/вниз
 */
add_filter( 'woocommerce_quantity_input_args', function( $args, $product ) {
    $args['input_value'] = 1; // Начальное значение
    $args['max_value'] = $product->get_max_purchase_quantity(); // Максимум
    $args['min_value'] = $product->get_min_purchase_quantity(); // Минимум
    $args['step'] = 1; // Шаг
    
    return $args;
}, 10, 2 );

// ============================================================================
// ХУКИ ДЛЯ ВЫВОДА ДОПОЛНИТЕЛЬНОЙ ИНФОРМАЦИИ
// ============================================================================

/**
 * Вывод дополнительной информации на странице товара
 */
add_action( 'woocommerce_product_meta_start', function() {
    global $product;
    
    if ( $product->get_sku() ) {
        echo '<span class="sku-wrapper">';
        echo '<span class="sku-label">' . __( 'Артикул:', 'severcon' ) . '</span> ';
        echo '<span class="sku">' . esc_html( $product->get_sku() ) . '</span>';
        echo '</span>';
    }
} );

/**
 * Вывод блока "Новинка" для новых товаров
 */
add_action( 'woocommerce_before_shop_loop_item_title', function() {
    global $product;
    
    if ( severcon_is_new_product( $product->get_id(), 30 ) ) {
        echo '<span class="new-badge">' . __( 'Новинка', 'severcon' ) . '</span>';
    }
}, 5 );
