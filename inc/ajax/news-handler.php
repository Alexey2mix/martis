<?php
/**
 * Обработчик новостей через AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Инициализация AJAX обработчиков новостей
 */
function severcon_init_news_handlers() {
    add_action('wp_ajax_load_more_news', 'severcon_ajax_load_more_news');
    add_action('wp_ajax_nopriv_load_more_news', 'severcon_ajax_load_more_news');
}
add_action('init', 'severcon_init_news_handlers');

/**
 * AJAX загрузка дополнительных новостей
 */
function severcon_ajax_load_more_news() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_ajax_nonce')) {
        return;
    }
    
    $page = severcon_validate_id(severcon_get_post_var('page', 1));
    $posts_per_page = 6;
    
    // Для первой страницы пропускаем 1 новость (она уже показана как главная)
    $offset = ($page == 1) ? 1 : (($page - 1) * $posts_per_page) + 1;
    
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'offset'         => $offset,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    
    $news_query = new WP_Query($args);
    
    if ($news_query->have_posts()) {
        ob_start();
        
        while ($news_query->have_posts()) {
            $news_query->the_post();
            ?>
            <div class="news-archive-item" data-post-id="<?php the_ID(); ?>">
                <a href="<?php the_permalink(); ?>" class="news-archive-card">
                    <div class="news-archive-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('severcon-news-medium'); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/news-placeholder.jpg'); ?>" 
                                 alt="<?php the_title(); ?>">
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
        
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $news_query->max_num_pages > $page,
            'next_page' => $page + 1
        ));
    } else {
        wp_send_json_success(array(
            'html' => '',
            'has_more' => false,
            'message' => __('Новостей больше нет', 'severcon')
        ));
    }
    
    wp_die();
}

/**
 * Получение последних новостей для главной страницы
 */
function severcon_get_latest_news($count = 4) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    
    $news_query = new WP_Query($args);
    
    if (!$news_query->have_posts()) {
        return false;
    }
    
    return $news_query;
}

/**
 * Генерация HTML блока новостей
 */
function severcon_generate_news_block($title = null, $count = 4, $show_button = true) {
    $news_query = severcon_get_latest_news($count);
    
    if (!$news_query) {
        return '<p class="no-news">' . __('Новостей пока нет', 'severcon') . '</p>';
    }
    
    ob_start();
    ?>
    <div class="severcon-news-block">
        <?php if ($title) : ?>
            <h2 class="news-block-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <div class="news-grid">
            <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="news-card">
                    <div class="news-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('severcon-news-medium'); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/news-placeholder.jpg'); ?>" 
                                 alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="news-content">
                        <div class="news-date"><?php echo get_the_date('d.m.Y'); ?></div>
                        <h3 class="news-title"><?php the_title(); ?></h3>
                        <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 10); ?></p>
                    </div>
                </a>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        
        <?php if ($show_button) : 
            $blog_page_url = get_permalink(get_option('page_for_posts'));
        ?>
            <?php if ($blog_page_url) : ?>
                <div class="news-footer">
                    <a href="<?php echo esc_url($blog_page_url); ?>" class="all-news-button">
                        <?php _e('Все новости', 'severcon'); ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Шорткод для вывода новостей
 */
function severcon_news_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title'       => __('Последние новости', 'severcon'),
        'count'       => 4,
        'show_button' => true,
    ), $atts, 'severcon_news');
    
    return severcon_generate_news_block(
        $atts['title'],
        intval($atts['count']),
        filter_var($atts['show_button'], FILTER_VALIDATE_BOOLEAN)
    );
}
add_shortcode('severcon_news', 'severcon_news_shortcode');

/**
 * Обновление счетчика просмотров новостей
 */
function severcon_update_post_views($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (!$post_id) {
        return;
    }
    
    $count_key = 'views';
    $count = get_post_meta($post_id, $count_key, true);
    
    if ($count == '') {
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
    } else {
        $count++;
        update_post_meta($post_id, $count_key, $count);
    }
}

/**
 * Получение популярных новостей
 */
function severcon_get_popular_news($count = 5, $days = 30) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'meta_key'       => 'views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => array(
            array(
                'after' => date('Y-m-d', strtotime('-' . $days . ' days'))
            )
        )
    );
    
    return new WP_Query($args);
}

/**
 * Добавление блока похожих новостей
 */
function severcon_display_related_news($post_id = null, $count = 3) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $categories = get_the_category($post_id);
    $category_ids = array();
    
    if ($categories) {
        foreach ($categories as $category) {
            $category_ids[] = $category->term_id;
        }
    }
    
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'post__not_in'   => array($post_id),
        'orderby'        => 'rand'
    );
    
    if (!empty($category_ids)) {
        $args['category__in'] = $category_ids;
    }
    
    $related_query = new WP_Query($args);
    
    if (!$related_query->have_posts()) {
        return;
    }
    
    ?>
    <div class="related-news-section">
        <h3 class="related-news-title"><?php _e('Похожие новости', 'severcon'); ?></h3>
        
        <div class="related-news-grid">
            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <article class="related-news-item">
                    <a href="<?php the_permalink(); ?>" class="related-news-link">
                        <div class="related-news-image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('severcon-news-small'); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/news-placeholder.jpg'); ?>" 
                                     alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="related-news-content">
                            <h4 class="related-news-title"><?php the_title(); ?></h4>
                            <div class="related-news-date"><?php echo get_the_date('d.m.Y'); ?></div>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    
    wp_reset_postdata();
}
add_action('severcon_after_single_post', 'severcon_display_related_news');