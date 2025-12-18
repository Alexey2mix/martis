<?php get_header(); ?>

<!-- Хлебные крошки -->
<div class="breadcrumbs-section">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo home_url(); ?>">Главная</a>
            <span class="separator">/</span>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Каталог</a>
            
            <?php
            // Получаем ID товара до начала цикла
            $product_id = get_the_ID();
            
            if ($product_id) {
                $terms = wp_get_post_terms($product_id, 'product_cat');
                
                if ($terms && !is_wp_error($terms)) {
                    $main_term = $terms[0];
                    $ancestors = get_ancestors($main_term->term_id, 'product_cat');
                    
                    if ($ancestors) {
                        $ancestors = array_reverse($ancestors);
                        foreach ($ancestors as $ancestor_id) {
                            $ancestor = get_term($ancestor_id, 'product_cat');
                            ?>
                            <span class="separator">/</span>
                            <a href="<?php echo get_term_link($ancestor); ?>">
                                <?php echo esc_html($ancestor->name); ?>
                            </a>
                            <?php
                        }
                    }
                    
                    ?>
                    <span class="separator">/</span>
                    <a href="<?php echo get_term_link($main_term); ?>">
                        <?php echo esc_html($main_term->name); ?>
                    </a>
                    <?php
                }
            }
            ?>
            
            <span class="separator">/</span>
            <span class="current"><?php the_title(); ?></span>
        </div>
    </div>
</div>

<!-- Основной контент товара -->
<div class="single-product-page">
    <div class="container">
        <?php while (have_posts()) : the_post(); 
            global $product;
            
            // Проверяем, что $product существует
            if (!$product) {
                $product = wc_get_product(get_the_ID());
            }
        ?>
            
            <div class="product-main">
                <div class="product-gallery">
                    <?php
                    $attachment_ids = $product->get_gallery_image_ids();
                    ?>
                    
                    <!-- Основное изображение -->
                    <div class="main-product-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('class' => 'product-image')); ?>
                        <?php else : ?>
                            <img src="<?php echo wc_placeholder_img_src('large'); ?>" alt="<?php the_title(); ?>" class="product-image">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Галерея изображений -->
                    <?php if ($attachment_ids) : ?>
                        <div class="product-thumbnails">
                            <?php 
                            // Миниатюра основного изображения
                            if (has_post_thumbnail()) :
                                $thumbnail_id = get_post_thumbnail_id();
                            ?>
                                <div class="thumbnail active">
                                    <?php echo wp_get_attachment_image($thumbnail_id, 'thumbnail'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php foreach ($attachment_ids as $attachment_id) : ?>
                                <div class="thumbnail">
                                    <?php echo wp_get_attachment_image($attachment_id, 'thumbnail'); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <!-- Заголовок товара -->
                    <h1 class="product-title"><?php the_title(); ?></h1>
                    
                    <!-- Рейтинг -->
                    <div class="product-rating">
                        <?php if (wc_review_ratings_enabled()) : ?>
                            <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                            <?php if ($product->get_review_count()) : ?>
                                <span class="review-count">
                                    (<?php echo esc_html($product->get_review_count()); ?> отзывов)
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Цена (только для информации) -->
                    <div class="product-price">
                        <?php echo $product->get_price_html(); ?>
                    </div>
                    
                    <!-- Краткое описание -->
                    <div class="product-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <!-- Кнопка запроса (вместо "В корзину") -->
                    <div class="product-request">
                        <button class="btn btn-primary request-product-btn" 
                                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                data-product-name="<?php echo esc_attr(get_the_title()); ?>">
                            <i class="fas fa-envelope"></i> Запросить цену
                        </button>
                    </div>
                    
                    <!-- Мета информация -->
                    <div class="product-meta">
                        <?php if (wc_product_sku_enabled() && ($sku = $product->get_sku())) : ?>
                            <div class="sku">
                                <strong>Артикул:</strong> <span><?php echo esc_html($sku); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="categories">
                            <strong>Категории:</strong>
                            <?php echo wc_get_product_category_list($product->get_id(), ', '); ?>
                        </div>
                        
                        <?php if ($product->get_tag_ids()) : ?>
                            <div class="tags">
                                <strong>Теги:</strong>
                                <?php echo wc_get_product_tag_list($product->get_id(), ', '); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Табы с описанием и характеристиками -->
            <div class="product-tabs">
                <?php
                // Убираем вкладку отзывов
                $tabs = apply_filters('woocommerce_product_tabs', array());
                
                // Удаляем вкладку отзывов если она есть
                unset($tabs['reviews']);
                
                if (!empty($tabs)) : ?>
                    <div class="woocommerce-tabs">
                        <ul class="tabs">
                            <?php foreach ($tabs as $key => $tab) : ?>
                                <li class="<?php echo esc_attr($key); ?>_tab">
                                    <a href="#tab-<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html(apply_filters('woocommerce_product_' . $key . '_tab_title', $tab['title'], $key)); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php foreach ($tabs as $key => $tab) : ?>
                            <div class="panel" id="tab-<?php echo esc_attr($key); ?>">
                                <?php
                                if (isset($tab['callback'])) {
                                    call_user_func($tab['callback'], $key, $tab);
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Похожие товары -->
            <?php
            $related_products = wc_get_related_products($product->get_id(), 4);
            
            if ($related_products) : ?>
                <div class="related-products">
                    <h2 class="related-title">Похожие товары</h2>
                    <div class="related-products-grid">
                        <?php
                        foreach ($related_products as $related_product_id) :
                            $related_product = wc_get_product($related_product_id);
                            if (!$related_product) continue;
                        ?>
                            <div class="related-product-item">
                                <a href="<?php echo esc_url($related_product->get_permalink()); ?>" class="related-product-link">
                                    <div class="related-product-image">
                                        <?php echo $related_product->get_image('woocommerce_thumbnail'); ?>
                                    </div>
                                    <div class="related-product-info">
                                        <h3 class="related-product-title"><?php echo esc_html($related_product->get_name()); ?></h3>
                                        <div class="related-product-price">
                                            <?php echo $related_product->get_price_html(); ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>