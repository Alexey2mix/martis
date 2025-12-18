<?php
/**
 * Кастомизации WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Инициализация WooCommerce кастомизаций
 */
function severcon_init_woocommerce() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Убираем стандартные стили WooCommerce
    add_filter('woocommerce_enqueue_styles', '__return_empty_array');
    
    // Изменяем количество товаров на странице
    add_filter('loop_shop_per_page', 'severcon_products_per_page', 20);
    
    // Кастомизация хлебных крошек
    add_filter('woocommerce_breadcrumb_defaults', 'severcon_breadcrumbs');
    
    // Изменяем структуру заголовка на странице товара
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_before_single_product', 'severcon_single_product_title', 5);
    
    // Убираем таб с отзывами
    add_filter('woocommerce_product_tabs', 'severcon_remove_reviews_tab', 98);
    
    // Добавляем кнопку "Запросить цену" вместо "Добавить в корзину"
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    add_action('woocommerce_after_shop_loop_item', 'severcon_product_request_button', 10);
    add_action('woocommerce_single_product_summary', 'severcon_single_product_request_button', 30);
}
add_action('init', 'severcon_init_woocommerce');

/**
 * Количество товаров на странице
 */
function severcon_products_per_page($products_per_page) {
    // Показывать все товары если передан параметр show_all
    if (isset($_GET['show_all']) && $_GET['show_all'] === 'true') {
        return 48; // Большое число для показа всех
    }
    
    // Разное количество для разных страниц
    if (is_shop() || is_product_category() || is_product_tag()) {
        return 12; // Основные страницы
    }
    
    if (is_product()) {
        return 4; // Похожие товары
    }
    
    return $products_per_page;
}

/**
 * Кастомизация хлебных крошек
 */
function severcon_breadcrumbs() {
    return array(
        'delimiter'   => ' <span class="breadcrumb-separator">›</span> ',
        'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="' . __('Хлебные крошки', 'severcon') . '">',
        'wrap_after'  => '</nav>',
        'before'      => '',
        'after'       => '',
        'home'        => __('Главная', 'severcon'),
    );
}

/**
 * Заголовок товара на странице товара
 */
function severcon_single_product_title() {
    ?>
    <div class="single-product-header">
        <h1 class="product-title"><?php the_title(); ?></h1>
        <?php if (wc_review_ratings_enabled()) : ?>
            <div class="product-rating">
                <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                <?php if ($product->get_review_count()) : ?>
                    <span class="review-count">
                        (<?php echo esc_html($product->get_review_count()); ?> <?php echo _n('отзыв', 'отзывов', $product->get_review_count(), 'severcon'); ?>)
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Удаление таба с отзывами
 */
function severcon_remove_reviews_tab($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}

/**
 * Кнопка "Запросить цену" в списке товаров
 */
function severcon_product_request_button() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    ?>
    <div class="product-actions">
        <a href="<?php echo esc_url($product->get_permalink()); ?>" 
           class="button product-details-btn">
            <?php _e('Подробнее', 'severcon'); ?>
        </a>
        
        <button type="button" 
                class="button alt product-request-btn" 
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                data-product-name="<?php echo esc_attr($product->get_name()); ?>">
            <i class="fas fa-envelope"></i>
            <?php _e('Запросить цену', 'severcon'); ?>
        </button>
    </div>
    <?php
}

/**
 * Кнопка "Запросить цену" на странице товара
 */
function severcon_single_product_request_button() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    ?>
    <div class="single-product-request">
        <div class="price-section">
            <span class="price-label"><?php _e('Цена:', 'severcon'); ?></span>
            <span class="price"><?php echo $product->get_price_html(); ?></span>
        </div>
        
        <button type="button" 
                class="single-product-request-btn" 
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                data-product-name="<?php echo esc_attr(get_the_title()); ?>">
            <i class="fas fa-envelope"></i>
            <span><?php _e('Запросить цену и консультацию', 'severcon'); ?></span>
        </button>
        
        <div class="product-meta">
            <?php if (wc_product_sku_enabled() && ($sku = $product->get_sku())) : ?>
                <div class="sku">
                    <strong><?php _e('Артикул:', 'severcon'); ?></strong> 
                    <span><?php echo esc_html($sku); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="categories">
                <strong><?php _e('Категории:', 'severcon'); ?></strong>
                <?php echo wc_get_product_category_list($product->get_id(), ', '); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Кастомизация миниатюр товаров
 */
function severon_product_thumbnail_size($size) {
    return 'severcon-product-medium';
}
add_filter('single_product_archive_thumbnail_size', 'seviron_product_thumbnail_size');
add_filter('subcategory_archive_thumbnail_size', 'seviron_product_thumbnail_size');

/**
 * Изменение порядка элементов на странице товара
 */
function severcon_reorder_single_product() {
    // Галерея изображений
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
    add_action('woocommerce_before_single_product_summary', 'severcon_custom_product_gallery', 20);
    
    // Краткое описание
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 15);
}
add_action('init', 'severcon_reorder_single_product');

/**
 * Кастомная галерея товара
 */
function severcon_custom_product_gallery() {
    global $product;
    
    $attachment_ids = $product->get_gallery_image_ids();
    $post_thumbnail_id = $product->get_image_id();
    
    ?>
    <div class="severcon-product-gallery">
        <div class="gallery-main">
            <?php if ($post_thumbnail_id) : ?>
                <div class="main-image">
                    <?php echo wp_get_attachment_image($post_thumbnail_id, 'severcon-product-large'); ?>
                </div>
            <?php else : ?>
                <div class="main-image placeholder">
                    <?php echo wc_placeholder_img('severcon-product-large'); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($attachment_ids || $post_thumbnail_id) : ?>
            <div class="gallery-thumbs">
                <?php if ($post_thumbnail_id) : ?>
                    <div class="thumb active">
                        <?php echo wp_get_attachment_image($post_thumbnail_id, 'severcon-product-thumb'); ?>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($attachment_ids as $attachment_id) : ?>
                    <div class="thumb">
                        <?php echo wp_get_attachment_image($attachment_id, 'severcon-product-thumb'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Добавление классов к телу для страниц WooCommerce
 */
function severcon_woocommerce_body_class($classes) {
    if (severcon_is_woocommerce_page()) {
        $classes[] = 'woocommerce-page';
        
        if (is_product()) {
            $classes[] = 'single-product-page';
        }
        
        if (is_shop() || is_product_category() || is_product_tag()) {
            $classes[] = 'product-archive-page';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'severcon_woocommerce_body_class');

/**
 * Кастомизация пагинации WooCommerce
 */
function severcon_woocommerce_pagination_args($args) {
    $args['prev_text'] = '<i class="fas fa-chevron-left"></i> ' . __('Назад', 'severcon');
    $args['next_text'] = __('Вперед', 'severcon') . ' <i class="fas fa-chevron-right"></i>';
    $args['type'] = 'list';
    
    return $args;
}
add_filter('woocommerce_pagination_args', 'severcon_woocommerce_pagination_args');