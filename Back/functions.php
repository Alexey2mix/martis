<?php
if (!defined('ABSPATH')) {
    exit;
}

// ===== ОСНОВНЫЕ НАСТРОЙКИ ТЕМЫ =====
function severcon_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Поддержка Woocommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'severcon_setup');

// ===== РЕГИСТРАЦИЯ МЕНЮ =====
function severcon_menus() {
    register_nav_menus(array(
        'primary' => 'Основное меню',
        'footer' => 'Меню в футере',
    ));
}
add_action('init', 'severcon_menus');

// ===== ЗАГРУЗКА СТИЛЕЙ И СКРИПТОВ =====
function severcon_scripts() {
    // Основной CSS
    wp_enqueue_style('severcon-main', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    
    // jQuery (уже есть в WordPress)
    wp_enqueue_script('jquery');
    
    // Основной JavaScript
    wp_enqueue_script('severcon-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
    
    // Локализация для AJAX (ТОЛЬКО ОДИН РАЗ!)
    wp_localize_script('severcon-main', 'severcon_ajax', array(
        'ajax_url'      => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('severcon_nonce'),
        'filter_nonce'  => wp_create_nonce('filter_nonce'),
        'quick_nonce'   => wp_create_nonce('quick_view_nonce'),
        'i18n' => array(
            'loading' => 'Загрузка...',
            'no_products' => 'Товаров не найдено',
            'apply_filters' => 'Применить фильтры'
        )
    ));
}
add_action('wp_enqueue_scripts', 'severcon_scripts');

// ===== РЕГИСТРАЦИЯ ВИДЖЕТОВ =====
function severcon_widgets_init() {
    register_sidebar(array(
        'name' => 'Сайдбар магазина',
        'id' => 'shop-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => 'Футер - Логотип',
        'id' => 'footer-logo',
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => 'Футер - Каталог',
        'id' => 'footer-catalog',
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => 'Футер - Поддержка',
        'id' => 'footer-support',
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => 'Футер - О компании',
        'id' => 'footer-about',
        'before_widget' => '<div class="footer-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'severcon_widgets_init');

// ===== CUSTOMIZER НАСТРОЙКИ =====
function severcon_customize_register($wp_customize) {
    // Ваш существующий код Customizer (строка 89-445)
    // Оставляем без изменений
}
add_action('customize_register', 'severcon_customize_register');

// ===== РАЗМЕРЫ ИЗОБРАЖЕНИЙ =====
function severcon_image_sizes() {
    add_image_size('news-thumb', 400, 250, true);
    add_image_size('equipment-large', 600, 400, true);
    add_image_size('equipment-small', 400, 300, true);
    add_image_size('product-thumb', 300, 300, true);
}
add_action('after_setup_theme', 'severcon_image_sizes');

// ===== AJAX ДЛЯ ЗАГРУЗКИ НОВОСТЕЙ =====
add_action('wp_ajax_load_more_news', 'severcon_load_more_news');
add_action('wp_ajax_nopriv_load_more_news', 'severcon_load_more_news');

function severcon_load_more_news() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $posts_per_page = 6;
    $offset = 7 + (($page - 2) * $posts_per_page);
    
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $news_query = new WP_Query($args);
    
    if ($news_query->have_posts()) {
        while ($news_query->have_posts()) {
            $news_query->the_post();
            ?>
            <div class="news-archive-item" data-post-id="<?php the_ID(); ?>">
                <a href="<?php the_permalink(); ?>" class="news-archive-card">
                    <div class="news-archive-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('news-thumb'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="news-archive-content">
                        <div class="news-date"><?php echo get_the_date('d.m.Y'); ?></div>
                        <h3 class="news-title"><?php the_title(); ?></h3>
                        <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    </div>
                </a>
            </div>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo 'no_more_posts';
    }
    wp_die();
}

// ===== БЫСТРЫЙ ПРОСМОТР ТОВАРА =====
add_action('wp_ajax_get_quick_view', 'get_quick_view_product');
add_action('wp_ajax_nopriv_get_quick_view', 'get_quick_view_product');

function get_quick_view_product() {
    // Упрощенная проверка
    if (!isset($_POST['product_id'])) {
        wp_send_json_error('Не передан ID товара');
    }
    
    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error('Товар не найден');
    }
    
    ob_start();
    ?>
    <div class="quick-view-product" data-current-id="<?php echo $product_id; ?>">
        <div class="quick-view-nav-container">
            <?php
            // Простая навигация - можно упростить если не работает
            $prev_id = $product_id - 1;
            $next_id = $product_id + 1;
            
            // Проверяем существование товаров
            if (wc_get_product($prev_id)) : ?>
                <button class="nav-arrow prev-arrow" data-product-id="<?php echo $prev_id; ?>">
                    <i class="fas fa-chevron-left"></i>
                </button>
            <?php endif; ?>
            
            <?php if (wc_get_product($next_id)) : ?>
                <button class="nav-arrow next-arrow" data-product-id="<?php echo $next_id; ?>">
                    <i class="fas fa-chevron-right"></i>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="quick-view-gallery">
            <div class="quick-view-image-main">
                <?php 
                $image_id = $product->get_image_id();
                if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'large');
                } else {
                    echo wc_placeholder_img('large');
                }
                ?>
            </div>
            
            <?php 
            $gallery_ids = $product->get_gallery_image_ids();
            if ($gallery_ids) : ?>
                <div class="quick-view-thumbnails">
                    <?php if ($image_id) : ?>
                        <div class="quick-view-thumb active">
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                        </div>
                    <?php endif;
                    foreach ($gallery_ids as $gallery_id) : ?>
                        <div class="quick-view-thumb">
                            <?php echo wp_get_attachment_image($gallery_id, 'thumbnail'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="quick-view-info">
            <h2><?php echo esc_html($product->get_name()); ?></h2>
            
            <?php if (wc_review_ratings_enabled() && $product->get_average_rating() > 0) : ?>
                <div class="quick-view-rating">
                    <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                    <span class="review-count">(<?php echo $product->get_review_count(); ?> отзывов)</span>
                </div>
            <?php endif; ?>
            
            <div class="quick-view-price">
                <?php echo $product->get_price_html(); ?>
            </div>
            
            <?php if ($product->get_short_description()) : ?>
                <div class="quick-view-excerpt">
                    <?php echo apply_filters('the_content', $product->get_short_description()); ?>
                </div>
            <?php endif; ?>
            
            <div class="quick-view-actions">
                <a href="<?php echo esc_url($product->get_permalink()); ?>" 
                   class="btn btn-primary btn-details">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Перейти к товару</span>
                </a>
                
                <button class="btn btn-dark btn-request quick-view-request"
                        data-product-id="<?php echo esc_attr($product_id); ?>"
                        data-product-name="<?php echo esc_attr($product->get_name()); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Запросить цену</span>
                </button>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
    wp_die();
}

// ===== ФИЛЬТРАЦИЯ ТОВАРОВ (ИСПРАВЛЕННАЯ) =====
add_action('wp_ajax_filter_category_products', 'filter_category_products_ajax');
add_action('wp_ajax_nopriv_filter_category_products', 'filter_category_products_ajax');

function filter_category_products_ajax() {
    // Проверяем nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'filter_nonce')) {
        wp_send_json_error('Ошибка безопасности');
    }
    
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    
    if (!$category_id) {
        wp_send_json_error('Категория не указана');
    }
    
    // Основные аргументы
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category_id,
            )
        )
    );
    
    // Сортировка
    switch ($orderby) {
        case 'price':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_average_rating';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order title';
            $args['order'] = 'ASC';
    }
    
    // Фильтры по атрибутам
    if (!empty($filters) && is_array($filters)) {
        foreach ($filters as $attribute_name => $selected_terms) {
            if (!empty($selected_terms) && is_array($selected_terms)) {
                $taxonomy = 'pa_' . sanitize_text_field($attribute_name);
                $args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => array_map('sanitize_text_field', $selected_terms),
                    'operator' => 'IN'
                );
            }
        }
        
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
    }
    
    // Запрос товаров
    $products_query = new WP_Query($args);
    
    ob_start();
    
    if ($products_query->have_posts()) :
        while ($products_query->have_posts()) : $products_query->the_post();
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
                        <div class="product-price"><?php echo $product->get_price_html(); ?></div>
                        <div class="product-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 10); ?></div>
                        <div class="product-actions">
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary view-product-btn">
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
            <?php
        endwhile;
    else :
        ?>
        <div class="no-products-filtered">
            <p>Товаров по выбранным фильтрам не найдено.</p>
            <button class="btn btn-primary reset-filters-ajax">Сбросить фильтры</button>
        </div>
        <?php
    endif;
    
    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html'       => $html,
        'count'      => $products_query->found_posts,
        'max_pages'  => $products_query->max_num_pages,
        'page'       => $page
    ));
    
    wp_die();
}

// ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====

// Управление количеством товаров
add_filter('loop_shop_per_page', 'custom_products_per_page', 20);
function custom_products_per_page($products_per_page) {
    $term = get_queried_object();
    if (is_a($term, 'WP_Term') && $term->taxonomy === 'product_cat') {
        if (isset($_GET['show_all']) && $_GET['show_all'] === 'true') {
            return 24;
        } else {
            return 12;
        }
    }
    return $products_per_page;
}

// Отключаем стандартные хлебные крошки WooCommerce
add_filter('woocommerce_breadcrumb_defaults', 'custom_woocommerce_breadcrumbs');
function custom_woocommerce_breadcrumbs() {
    return array(
        'delimiter'   => ' <span class="separator">/</span> ',
        'wrap_before' => '<div class="breadcrumbs-section"><div class="container"><nav class="breadcrumbs">',
        'wrap_after'  => '</nav></div></div>',
        'before'      => '',
        'after'       => '',
        'home'        => 'Главная',
    );
}

// ===== ОТКЛЮЧЕНИЕ КОММЕНТАРИЕВ (упрощенное) =====
function severcon_disable_comments() {
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    add_filter('comments_array', '__return_empty_array', 10, 2);
    
    // Убираем из админки
    function severcon_remove_comments_admin_menu() {
        remove_menu_page('edit-comments.php');
    }
    add_action('admin_menu', 'severcon_remove_comments_admin_menu');
}
add_action('init', 'severcon_disable_comments');

// ===== ФИЛЬТРАЦИЯ ТОВАРОВ =====
function initFilters() {
    const filterBtn = $('.filters-toggle-btn');
    const filterContent = $('.filters-content');
    const applyBtn = $('.apply-filters');
    const resetBtn = $('.reset-filters');
    const categoryId = $('.products-grid').data('category-id');
    
    if (!filterBtn.length || !categoryId) return;
    
    // Переключение видимости фильтров
    filterBtn.on('click', function() {
        filterContent.toggleClass('active');
        $(this).find('i').toggleClass('fa-sliders-h fa-times');
    });
    
    // Применение фильтров
    applyBtn.on('click', function() {
        applyFilters(categoryId);
    });
    
    // Сброс фильтров
    resetBtn.on('click', function() {
        resetFilters(categoryId);
    });
    
    // Сортировка
    $('.woocommerce-ordering select').on('change', function() {
        applyFilters(categoryId);
    });
    
    // Пагинация
    $(document).on('click', '.woocommerce-pagination a', function(e) {
        e.preventDefault();
        const page = $(this).data('page') || $(this).text();
        applyFilters(categoryId, page);
    });
}

function applyFilters(categoryId, page = 1) {
    const loader = '<div class="filter-loader"><i class="fas fa-spinner fa-spin"></i> Загрузка...</div>';
    $('.products-grid').html(loader);
    
    const filters = {};
    $('.filter-group').each(function() {
        const attribute = $(this).data('attribute');
        const selected = [];
        $(this).find('input[type="checkbox"]:checked').each(function() {
            selected.push($(this).val());
        });
        if (selected.length > 0) {
            filters[attribute] = selected;
        }
    });
    
    const orderby = $('.woocommerce-ordering select').val() || 'menu_order';
    
    $.ajax({
        url: severcon_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'filter_category_products',
            category_id: categoryId,
            filters: filters,
            orderby: orderby,
            page: page,
            per_page: 12,
            nonce: severcon_ajax.filter_nonce
        },
        success: function(response) {
            if (response.success) {
                $('.products-grid').html(response.data.html);
                
                // Обновляем счетчик
                $('.woocommerce-result-count').text(`Найдено товаров: ${response.data.count}`);
                
                // Обновляем пагинацию
                updatePagination(response.data.max_pages, page);
            } else {
                $('.products-grid').html('<p class="filter-error">Ошибка загрузки товаров</p>');
            }
        },
        error: function() {
            $('.products-grid').html('<p class="filter-error">Ошибка соединения</p>');
        }
    });
}

function resetFilters(categoryId) {
    $('input[type="checkbox"]').prop('checked', false);
    $('.woocommerce-ordering select').val('menu_order');
    applyFilters(categoryId);
}

function updatePagination(maxPages, currentPage) {
    if (maxPages <= 1) {
        $('.woocommerce-pagination').hide();
        return;
    }
    
    let paginationHTML = '<ul class="page-numbers">';
    
    // Предыдущая страница
    if (currentPage > 1) {
        paginationHTML += `<li><a class="prev" href="#" data-page="${currentPage - 1}">←</a></li>`;
    }
    
    // Номера страниц
    for (let i = 1; i <= maxPages; i++) {
        if (i == currentPage) {
            paginationHTML += `<li><span class="current">${i}</span></li>`;
        } else {
            paginationHTML += `<li><a href="#" data-page="${i}">${i}</a></li>`;
        }
    }
    
    // Следующая страница
    if (currentPage < maxPages) {
        paginationHTML += `<li><a class="next" href="#" data-page="${currentPage + 1}">→</a></li>`;
    }
    
    paginationHTML += '</ul>';
    $('.woocommerce-pagination').html(paginationHTML).show();
}

// В функции initAll() добавьте:
function initAll() {
    console.log('Initializing all functions...');
    
    // ... существующие функции ...
    
    initFilters(); // <-- Добавить эту строку
    
    console.log('All functions initialized');
}