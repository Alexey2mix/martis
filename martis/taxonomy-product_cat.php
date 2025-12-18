<?php get_header(); ?>

<!-- Хлебные крошки -->
<div class="breadcrumbs-section">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo home_url(); ?>">Главная</a>
            <span class="separator">/</span>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Каталог</a>
            <?php 
            $term = get_queried_object();
            $ancestors = get_ancestors($term->term_id, 'product_cat');
            if ($ancestors) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    ?>
                    <span class="separator">/</span>
                    <a href="<?php echo get_term_link($ancestor); ?>"><?php echo esc_html($ancestor->name); ?></a>
                    <?php
                }
            }
            ?>
            <span class="separator">/</span>
            <span class="current"><?php single_term_title(); ?></span>
        </div>
    </div>
</div>

<!-- Страница категории -->
<div class="product-category-page" data-category-id="<?php echo $term->term_id; ?>">
    <div class="container">
        <!-- Заголовок и описание категории -->
        <div class="category-header">
            <h1 class="category-title"><?php single_term_title(); ?></h1>
            <?php if (term_description()) : ?>
                <div class="category-description boxed">
                    <?php echo term_description(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Получаем текущую категорию
        $current_category = get_queried_object();
        
        // Получаем подкатегории первого уровня
        $subcategories = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => $current_category->term_id,
            'hide_empty' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        
        // Проверяем параметр show_all
        $show_all = isset($_GET['show_all']) && $_GET['show_all'] === 'true';
        
        if ($subcategories && !is_wp_error($subcategories) && !$show_all) :
        ?>
            <!-- Сетка подкатегорий -->
            <div class="subcategories-grid">
                <?php foreach ($subcategories as $subcategory) : 
                    $thumbnail_id = get_term_meta($subcategory->term_id, 'thumbnail_id', true);
                    $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();
                    $term_link = get_term_link($subcategory);
                ?>
                    <div class="subcategory-item">
                        <a href="<?php echo esc_url($term_link); ?>" class="subcategory-card">
                            <div class="subcategory-image">
                                <img src="<?php echo esc_url($image_url); ?>" 
                                     alt="<?php echo esc_attr($subcategory->name); ?>"
                                     loading="lazy">
                                <div class="subcategory-overlay">
                                    <span class="view-more">Смотреть товары</span>
                                </div>
                            </div>
                            <div class="subcategory-info">
                                <h3 class="subcategory-title"><?php echo esc_html($subcategory->name); ?></h3>
                                <div class="subcategory-count">
                                    <?php echo $subcategory->count; ?> товаров
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
        
        <!-- Товары текущей категории -->
        <?php 
        if (!$show_all) : // Показываем заголовок только если не в режиме "все товары"
        ?>
            <!-- Заголовок раздела с товарами -->
            <div class="category-products-header">
                <h2 class="section-title category-products-title">
                    <?php echo ($subcategories && !is_wp_error($subcategories) && count($subcategories) > 0) ? 'Популярные товары в категории' : 'Товары в категории'; ?>
                </h2>
                <?php if ($subcategories && !is_wp_error($subcategories) && count($subcategories) > 0) : ?>
                    <p class="category-products-subtitle">
                        Ниже представлены товары из текущей категории. 
                        Для более детального просмотра перейдите в соответствующую подкатегорию.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- ФИЛЬТРЫ И СОРТИРОВКА -->
        <div class="filters-section">
            <div class="container">
                <!-- Сортировка отдельно сверху -->
                <div class="filters-sorting">
                    <label for="orderby">Сортировка:</label>
                    <select class="orderby" name="orderby" id="orderby">
                        <option value="menu_order">По умолчанию</option>
                        <option value="price">По цене (дешевые)</option>
                        <option value="price-desc">По цене (дорогие)</option>
                        <option value="date">По новизне</option>
                        <option value="popularity">По популярности</option>
                        <option value="rating">По рейтингу</option>
                    </select>
                </div>
                
                <!-- Компактные фильтры -->
                <?php 
                // Выводим умные фильтры
                if (function_exists('severcon_display_category_filters')) {
                    severcon_display_category_filters($current_category->term_id);
                }
                ?>
            </div>
        </div>
        
        <!-- СЕТКА ТОВАРОВ -->
        <div class="products-grid category-products-grid" data-category-id="<?php echo $current_category->term_id; ?>">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); 
                    global $product;
                ?>
                    <div class="product-item">
                        <a href="<?php the_permalink(); ?>" class="product-card">
                            <div class="product-image">
                                <?php echo $product->get_image('woocommerce_thumbnail'); ?>
                                <?php if ($product->is_on_sale()) : ?>
                                    <span class="product-badge sale">Акция</span>
                                <?php elseif ($product->is_featured()) : ?>
                                    <span class="product-badge featured">Хит</span>
                                <?php endif; ?>
                                
                                <!-- Кнопка быстрого просмотра -->
                                <button class="quick-view-btn" 
                                        data-product-id="<?php echo $product->get_id(); ?>"
                                        title="Быстрый просмотр">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                
                                <?php if (wc_review_ratings_enabled() && $product->get_average_rating() > 0) : ?>
                                    <div class="product-rating-small">
                                        <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                                        <span class="review-count-small">(<?php echo $product->get_review_count(); ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                                
                                <div class="product-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 10); ?>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                        Подробнее
                                    </a>
                                    <button class="btn btn-dark request-price-btn" 
                                            data-product-id="<?php echo $product->get_id(); ?>"
                                            data-product-name="<?php echo esc_attr(get_the_title()); ?>">
                                        <i class="fas fa-envelope"></i> Запросить цену
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-products">
                    <p>В этой категории пока нет товаров.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ПАГИНАЦИЯ -->
        <?php if (!$subcategories || $show_all) : ?>
            <div class="woocommerce-pagination">
                <?php woocommerce_pagination(); ?>
            </div>
        <?php endif; ?>
        
        <!-- КНОПКА "ПОКАЗАТЬ ВСЕ ТОВАРЫ" -->
        <?php 
        $total_products = $current_category->count;
        $posts_per_page = get_option('posts_per_page', 12);
        
        if (!$show_all && $subcategories && !is_wp_error($subcategories) && count($subcategories) > 0 && $total_products > $posts_per_page) :
        ?>
            <div class="view-all-products">
                <a href="<?php echo add_query_arg('show_all', 'true', get_term_link($current_category)); ?>" 
                   class="btn btn-primary btn-large">
                    <i class="fas fa-th-list"></i> Показать все <?php echo $total_products; ?> товаров
                </a>
            </div>
        <?php endif; ?>
        <?php 
        // После кнопки "Показать все товары" или в начале если show_all=true
        if ($show_all) : ?>
            <div class="back-to-categories">
                <a href="<?php echo remove_query_arg('show_all', get_term_link($current_category)); ?>" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Вернуться к подкатегориям
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>