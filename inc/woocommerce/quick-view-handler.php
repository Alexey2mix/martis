<?php
/**
 * Обработчик быстрого просмотра товаров
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Инициализация AJAX обработчиков быстрого просмотра
 */
function severcon_init_quick_view_handlers() {
    add_action('wp_ajax_get_quick_view', 'severcon_ajax_get_quick_view');
    add_action('wp_ajax_nopriv_get_quick_view', 'severcon_ajax_get_quick_view');
}
add_action('init', 'severcon_init_quick_view_handlers');

/**
 * AJAX получение быстрого просмотра товара
 */
function severcon_ajax_get_quick_view() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_ajax_nonce')) {
        return;
    }
    
    $product_id = severcon_validate_id(severcon_get_post_var('product_id', 0));
    
    if (!$product_id) {
        wp_send_json_error(array(
            'message' => __('Товар не найден', 'severcon'),
            'code'    => 'invalid_product'
        ));
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error(array(
            'message' => __('Товар не существует', 'severcon'),
            'code'    => 'product_not_found'
        ));
    }
    
    // Генерация HTML для быстрого просмотра
    $html = severcon_generate_quick_view_html($product);
    
    wp_send_json_success(array(
        'html' => $html,
        'title' => $product->get_name()
    ));
    
    wp_die();
}

/**
 * Генерация HTML для быстрого просмотра
 */
function severcon_generate_quick_view_html($product) {
    $product_id = $product->get_id();
    $category_id = severcon_get_product_category_id($product_id);
    
    ob_start();
    ?>
    <div class="quick-view-modal" data-product-id="<?php echo esc_attr($product_id); ?>">
        <div class="quick-view-header">
            <h2 class="quick-view-title"><?php echo esc_html($product->get_name()); ?></h2>
            <button type="button" class="quick-view-close" aria-label="<?php _e('Закрыть', 'severcon'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="quick-view-body">
            <div class="quick-view-gallery">
                <?php echo severcon_get_product_gallery_html($product); ?>
            </div>
            
            <div class="quick-view-info">
                <?php echo severcon_get_product_info_html($product); ?>
            </div>
        </div>
        
        <div class="quick-view-footer">
            <?php echo severcon_get_product_actions_html($product); ?>
            
            <?php if ($category_id) : ?>
                <div class="quick-view-navigation">
                    <?php echo severcon_get_adjacent_products_nav($product_id, $category_id); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Получение ID категории товара
 */
function severcon_get_product_category_id($product_id) {
    $terms = wp_get_post_terms($product_id, 'product_cat');
    
    if ($terms && !is_wp_error($terms)) {
        return $terms[0]->term_id;
    }
    
    return 0;
}

/**
 * Генерация HTML галереи товара
 */
function severcon_get_product_gallery_html($product) {
    $product_id = $product->get_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $thumbnail_id = $product->get_image_id();
    
    ob_start();
    ?>
    <div class="product-gallery">
        <?php if ($thumbnail_id || !empty($gallery_ids)) : ?>
            <div class="gallery-main">
                <div class="main-image-slider">
                    <?php if ($thumbnail_id) : ?>
                        <div class="slide">
                            <?php echo wp_get_attachment_image($thumbnail_id, 'severcon-product-large', false, array(
                                'class' => 'product-main-image',
                                'data-image-id' => $thumbnail_id
                            )); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($gallery_ids as $gallery_id) : ?>
                        <div class="slide">
                            <?php echo wp_get_attachment_image($gallery_id, 'severcon-product-large', false, array(
                                'class' => 'product-gallery-image',
                                'data-image-id' => $gallery_id
                            )); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="gallery-thumbs">
                <?php if ($thumbnail_id) : ?>
                    <div class="thumb active" data-image-id="<?php echo esc_attr($thumbnail_id); ?>">
                        <?php echo wp_get_attachment_image($thumbnail_id, 'severcon-product-thumb'); ?>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($gallery_ids as $gallery_id) : ?>
                    <div class="thumb" data-image-id="<?php echo esc_attr($gallery_id); ?>">
                        <?php echo wp_get_attachment_image($gallery_id, 'severcon-product-thumb'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="no-image">
                <?php echo wc_placeholder_img('severcon-product-large'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Генерация HTML информации о товаре
 */
function severcon_get_product_info_html($product) {
    ob_start();
    ?>
    <div class="product-info">
        <div class="product-meta">
            <?php if (wc_review_ratings_enabled() && $product->get_average_rating() > 0) : ?>
                <div class="product-rating">
                    <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                    <span class="review-count">
                        (<?php echo $product->get_review_count(); ?>)
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (wc_product_sku_enabled() && ($sku = $product->get_sku())) : ?>
                <div class="product-sku">
                    <strong><?php _e('Артикул:', 'severcon'); ?></strong>
                    <span><?php echo esc_html($sku); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-price">
            <?php echo $product->get_price_html(); ?>
        </div>
        
        <div class="product-excerpt">
            <?php echo apply_filters('the_content', $product->get_short_description()); ?>
        </div>
        
        <div class="product-attributes">
            <?php echo severcon_get_product_attributes_html($product); ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Генерация HTML атрибутов товара
 */
function severcon_get_product_attributes_html($product) {
    $attributes = $product->get_attributes();
    
    if (empty($attributes)) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="attributes-list">
        <?php foreach ($attributes as $attribute) : 
            $name = $attribute->get_name();
            $options = $attribute->get_options();
            
            if (empty($options)) {
                continue;
            }
        ?>
            <div class="attribute">
                <strong><?php echo wc_attribute_label($name); ?>:</strong>
                <span><?php echo implode(', ', $options); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Генерация HTML действий с товаром
 */
function severcon_get_product_actions_html($product) {
    $product_id = $product->get_id();
    
    ob_start();
    ?>
    <div class="product-actions">
        <a href="<?php echo esc_url($product->get_permalink()); ?>" 
           class="button view-product-details">
            <i class="fas fa-external-link-alt"></i>
            <?php _e('Подробнее о товаре', 'severcon'); ?>
        </a>
        
        <button type="button" 
                class="button alt request-price-quick-view"
                data-product-id="<?php echo esc_attr($product_id); ?>"
                data-product-name="<?php echo esc_attr($product->get_name()); ?>">
            <i class="fas fa-envelope"></i>
            <?php _e('Запросить цену', 'severcon'); ?>
        </button>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Навигация между соседними товарами
 */
function severcon_get_adjacent_products_nav($product_id, $category_id) {
    $prev_id = severcon_get_adjacent_product($product_id, $category_id, 'prev');
    $next_id = severcon_get_adjacent_product($product_id, $category_id, 'next');
    
    ob_start();
    ?>
    <div class="adjacent-products-nav">
        <?php if ($prev_id) : ?>
            <button type="button" 
                    class="adjacent-product-prev"
                    data-product-id="<?php echo esc_attr($prev_id); ?>"
                    title="<?php _e('Предыдущий товар', 'severcon'); ?>">
                <i class="fas fa-chevron-left"></i>
                <span><?php _e('Предыдущий', 'severcon'); ?></span>
            </button>
        <?php endif; ?>
        
        <?php if ($next_id) : ?>
            <button type="button" 
                    class="adjacent-product-next"
                    data-product-id="<?php echo esc_attr($next_id); ?>"
                    title="<?php _e('Следующий товар', 'severcon'); ?>">
                <span><?php _e('Следующий', 'severcon'); ?></span>
                <i class="fas fa-chevron-right"></i>
            </button>
        <?php endif; ?>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Получение соседнего товара в категории
 */
function severcon_get_adjacent_product($product_id, $category_id, $direction = 'next') {
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_id,
            )
        )
    );
    
    $products = get_posts($args);
    
    if (empty($products)) {
        return false;
    }
    
    $current_index = array_search($product_id, $products);
    
    if ($current_index === false) {
        return false;
    }
    
    if ($direction === 'next') {
        $next_index = $current_index + 1;
        return isset($products[$next_index]) ? $products[$next_index] : false;
    } else {
        $prev_index = $current_index - 1;
        return isset($products[$prev_index]) ? $products[$prev_index] : false;
    }
}

/**
 * Добавление кнопки быстрого просмотра к товарам
 */
function severcon_add_quick_view_button() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    ?>
    <button type="button" 
            class="quick-view-button" 
            data-product-id="<?php echo esc_attr($product->get_id()); ?>"
            title="<?php _e('Быстрый просмотр', 'severcon'); ?>">
        <i class="fas fa-eye"></i>
        <span class="button-text"><?php _e('Быстрый просмотр', 'severcon'); ?></span>
    </button>
    <?php
}
add_action('woocommerce_after_shop_loop_item', 'severcon_add_quick_view_button', 15);

/**
 * Добавление модального окна быстрого просмотра в футер
 */
function severcon_add_quick_view_modal() {
    ?>
    <div id="severcon-quick-view-modal" class="severcon-modal" aria-hidden="true">
        <div class="modal-overlay" tabindex="-1">
            <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="quick-view-title">
                <div class="modal-content">
                    <!-- Контент подгружается через AJAX -->
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'severcon_add_quick_view_modal');