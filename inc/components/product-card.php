<?php
/**
 * Компонент: Карточка товара
 * 
 * @param array $args {
 *     @type int|WC_Product $product     ID товара или объект WC_Product
 *     @type string         $style       Стиль карточки: grid, list, compact
 *     @type string         $size        Размер изображения
 *     @type bool           $show_image  Показывать изображение
 *     @type bool           $show_title  Показывать заголовок
 *     @type bool           $show_price  Показывать цену
 *     @type bool           $show_rating Показывать рейтинг
 *     @type bool           $show_excerpt Показывать краткое описание
 *     @type bool           $show_button Показывать кнопки действий
 *     @type string         $button_text Текст основной кнопки
 *     @type string         $class       Дополнительные CSS классы
 *     @type bool           $lazy_load   Ленивая загрузка изображений
 * }
 */

function severcon_product_card($args = []) {
    global $product;
    
    // Сохраняем оригинальный глобальный $product
    $original_product = $product;
    
    // Аргументы по умолчанию
    $defaults = [
        'product'      => null,
        'style'        => 'grid',
        'size'         => 'woocommerce_thumbnail',
        'show_image'   => true,
        'show_title'   => true,
        'show_price'   => true,
        'show_rating'  => true,
        'show_excerpt' => false,
        'show_button'  => true,
        'button_text'  => __('Подробнее', 'severcon'),
        'class'        => '',
        'lazy_load'    => true,
        'attributes'   => [], // Дополнительные атрибуты для ссылки
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Получаем объект товара
    if (is_numeric($args['product'])) {
        $product = wc_get_product($args['product']);
    } elseif ($args['product'] instanceof WC_Product) {
        $product = $args['product'];
    } elseif (!$product) {
        $product = wc_get_product(get_the_ID());
    }
    
    // Если товар не найден, возвращаем пустую строку
    if (!$product) {
        // Восстанавливаем оригинальный $product
        $product = $original_product;
        return '';
    }
    
    $product_id = $product->get_id();
    $product_title = $product->get_name();
    $product_url = $product->get_permalink();
    $product_image = $product->get_image($args['size']);
    $product_price = $product->get_price_html();
    $product_rating = wc_get_rating_html($product->get_average_rating());
    $product_excerpt = $product->get_short_description();
    
    // Определяем классы
    $classes = ['product-card', 'product-card--' . $args['style']];
    
    if ($product->is_on_sale()) {
        $classes[] = 'product-card--sale';
    }
    
    if ($product->is_featured()) {
        $classes[] = 'product-card--featured';
    }
    
    if ($product->is_out_of_stock()) {
        $classes[] = 'product-card--out-of-stock';
    }
    
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    // Атрибуты для ссылки
    $link_attributes = [
        'href' => esc_url($product_url),
        'class' => 'product-card__link',
        'title' => esc_attr($product_title),
    ];
    
    if ($args['attributes']) {
        $link_attributes = array_merge($link_attributes, $args['attributes']);
    }
    
    // Формируем HTML атрибуты
    $link_attrs = '';
    foreach ($link_attributes as $key => $value) {
        $link_attrs .= ' ' . $key . '="' . esc_attr($value) . '"';
    }
    
    // Начинаем вывод
    ob_start();
    ?>
    
    <article class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-product-id="<?php echo esc_attr($product_id); ?>">
        <a <?php echo $link_attrs; ?>>
            
            <?php if ($args['show_image']) : ?>
                <div class="product-card__image">
                    <?php 
                    if ($args['lazy_load']) {
                        $image_url = wp_get_attachment_image_url($product->get_image_id(), $args['size']);
                        $image_alt = $product_title;
                        ?>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder.jpg'); ?>" 
                             data-src="<?php echo esc_url($image_url); ?>" 
                             alt="<?php echo esc_attr($image_alt); ?>" 
                             class="product-card__img lazy-load">
                    <?php } else {
                        echo $product_image;
                    } ?>
                    
                    <?php if ($product->is_on_sale()) : ?>
                        <span class="product-card__badge product-card__badge--sale">
                            <?php _e('Акция', 'severcon'); ?>
                        </span>
                    <?php elseif ($product->is_featured()) : ?>
                        <span class="product-card__badge product-card__badge--featured">
                            <?php _e('Хит', 'severcon'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($args['style'] === 'grid') : ?>
                        <button class="product-card__quick-view" 
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                title="<?php esc_attr_e('Быстрый просмотр', 'severcon'); ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="product-card__content">
                <?php if ($args['show_title']) : ?>
                    <h3 class="product-card__title">
                        <?php echo esc_html($product_title); ?>
                    </h3>
                <?php endif; ?>
                
                <?php if ($args['show_rating'] && $product_rating) : ?>
                    <div class="product-card__rating">
                        <?php echo $product_rating; ?>
                        <?php if ($product->get_review_count() > 0) : ?>
                            <span class="product-card__review-count">
                                (<?php echo esc_html($product->get_review_count()); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($args['show_price']) : ?>
                    <div class="product-card__price">
                        <?php echo $product_price; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($args['show_excerpt'] && $product_excerpt) : ?>
                    <div class="product-card__excerpt">
                        <?php echo wp_trim_words($product_excerpt, 15); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($args['show_button']) : ?>
                    <div class="product-card__actions">
                        <button type="button" 
                                class="product-card__button product-card__button--details">
                            <i class="fas fa-external-link-alt"></i>
                            <span><?php echo esc_html($args['button_text']); ?></span>
                        </button>
                        
                        <button type="button" 
                                class="product-card__button product-card__button--request"
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                data-product-name="<?php echo esc_attr($product_title); ?>">
                            <i class="fas fa-envelope"></i>
                            <span><?php _e('Запросить цену', 'severcon'); ?></span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($args['style'] === 'list') : ?>
                    <div class="product-card__meta">
                        <?php if (wc_product_sku_enabled() && ($sku = $product->get_sku())) : ?>
                            <div class="product-card__sku">
                                <strong><?php _e('Артикул:', 'severcon'); ?></strong>
                                <span><?php echo esc_html($sku); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product->get_weight()) : ?>
                            <div class="product-card__weight">
                                <strong><?php _e('Вес:', 'severcon'); ?></strong>
                                <span><?php echo esc_html($product->get_weight()); ?> кг</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </a>
    </article>
    
    <?php
    
    // Восстанавливаем оригинальный глобальный $product
    $product = $original_product;
    
    return ob_get_clean();
}

/**
 * Шорткод для вывода карточки товара
 */
function severcon_product_card_shortcode($atts) {
    $atts = shortcode_atts([
        'id'          => 0,
        'style'       => 'grid',
        'size'        => 'woocommerce_thumbnail',
        'show_image'  => true,
        'show_title'  => true,
        'show_price'  => true,
        'show_rating' => true,
        'show_excerpt'=> false,
        'show_button' => true,
        'button_text' => __('Подробнее', 'severcon'),
        'class'       => '',
    ], $atts, 'severcon_product_card');
    
    // Преобразуем строковые значения в boolean
    $bool_fields = ['show_image', 'show_title', 'show_price', 'show_rating', 'show_excerpt', 'show_button'];
    foreach ($bool_fields as $field) {
        if (isset($atts[$field])) {
            $atts[$field] = filter_var($atts[$field], FILTER_VALIDATE_BOOLEAN);
        }
    }
    
    return severcon_product_card([
        'product' => intval($atts['id']),
        'style'   => $atts['style'],
        'size'    => $atts['size'],
        'show_image'  => $atts['show_image'],
        'show_title'  => $atts['show_title'],
        'show_price'  => $atts['show_price'],
        'show_rating' => $atts['show_rating'],
        'show_excerpt'=> $atts['show_excerpt'],
        'show_button' => $atts['show_button'],
        'button_text' => $atts['button_text'],
        'class'       => $atts['class'],
    ]);
}
add_shortcode('severcon_product_card', 'severcon_product_card_shortcode');

/**
 * Функция для вывода сетки товаров
 */
function severcon_products_grid($products, $args = []) {
    if (empty($products)) {
        return '<p class="no-products">' . __('Товары не найдены', 'severcon') . '</p>';
    }
    
    $defaults = [
        'columns' => 3,
        'style'   => 'grid',
        'class'   => '',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $classes = ['products-grid', 'products-grid--' . $args['columns'] . '-cols'];
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    ob_start();
    ?>
    
    <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
        <?php foreach ($products as $product_item) : 
            $product_card_args = array_merge($args, ['product' => $product_item]);
            echo severcon_product_card($product_card_args);
        endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}