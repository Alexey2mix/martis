<?php
/**
 * Компонент: Карточка новости
 * 
 * @param array $args {
 *     @type int|WP_Post $post       ID поста или объект WP_Post
 *     @type string      $style      Стиль карточки: grid, list, featured, compact
 *     @type string      $size       Размер изображения
 *     @type bool        $show_image Показывать изображение
 *     @type bool        $show_date  Показывать дату
 *     @type bool        $show_cat   Показывать категорию
 *     @type bool        $show_excerpt Показывать краткое описание
 *     @type int         $excerpt_length Длина краткого описания
 *     @type bool        $show_button Показывать кнопку "Читать далее"
 *     @type string      $button_text Текст кнопки
 *     @type string      $class      Дополнительные CSS классы
 *     @type bool        $lazy_load  Ленивая загрузка изображений
 * }
 */

function severcon_news_card($args = []) {
    global $post;
    
    // Сохраняем оригинальный глобальный $post
    $original_post = $post;
    
    // Аргументы по умолчанию
    $defaults = [
        'post'          => null,
        'style'         => 'grid',
        'size'          => 'severcon-news-medium',
        'show_image'    => true,
        'show_date'     => true,
        'show_cat'      => true,
        'show_excerpt'  => true,
        'excerpt_length' => 15,
        'show_button'   => true,
        'button_text'   => __('Читать далее', 'severcon'),
        'class'         => '',
        'lazy_load'     => true,
        'show_views'    => false,
        'show_author'   => false,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Получаем объект поста
    if (is_numeric($args['post'])) {
        $post = get_post($args['post']);
    } elseif ($args['post'] instanceof WP_Post) {
        $post = $args['post'];
    }
    
    // Если пост не найден, возвращаем пустую строку
    if (!$post) {
        $post = $original_post;
        return '';
    }
    
    setup_postdata($post);
    
    $post_id = get_the_ID();
    $post_title = get_the_title();
    $post_url = get_permalink();
    $post_date = get_the_date('d.m.Y');
    $post_excerpt = get_the_excerpt();
    $post_image = get_the_post_thumbnail($post_id, $args['size']);
    
    // Получаем категории
    $categories = get_the_category();
    $first_category = !empty($categories) ? $categories[0] : null;
    
    // Получаем количество просмотров
    $views = get_post_meta($post_id, 'views', true) ?: 0;
    
    // Получаем автора
    $author = get_the_author();
    
    // Определяем классы
    $classes = ['news-card', 'news-card--' . $args['style']];
    
    if (has_post_thumbnail()) {
        $classes[] = 'news-card--has-image';
    } else {
        $classes[] = 'news-card--no-image';
    }
    
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    // Обрезаем excerpt если нужно
    if ($args['excerpt_length'] > 0) {
        $post_excerpt = wp_trim_words($post_excerpt, $args['excerpt_length']);
    }
    
    // Начинаем вывод
    ob_start();
    ?>
    
    <article class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
        <a href="<?php echo esc_url($post_url); ?>" class="news-card__link">
            
            <?php if ($args['show_image'] && has_post_thumbnail()) : ?>
                <div class="news-card__image">
                    <?php 
                    if ($args['lazy_load']) {
                        $image_url = get_the_post_thumbnail_url($post_id, $args['size']);
                        $image_alt = $post_title;
                        ?>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/news-placeholder.jpg'); ?>" 
                             data-src="<?php echo esc_url($image_url); ?>" 
                             alt="<?php echo esc_attr($image_alt); ?>" 
                             class="news-card__img lazy-load">
                    <?php } else {
                        echo $post_image;
                    } ?>
                    
                    <?php if ($args['style'] === 'featured') : ?>
                        <div class="news-card__image-overlay"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="news-card__content">
                <?php if ($args['show_date'] || $args['show_cat'] || $args['show_views'] || $args['show_author']) : ?>
                    <div class="news-card__meta">
                        <?php if ($args['show_date']) : ?>
                            <span class="news-card__date">
                                <i class="far fa-calendar"></i>
                                <?php echo esc_html($post_date); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($args['show_cat'] && $first_category) : ?>
                            <span class="news-card__category">
                                <i class="far fa-folder"></i>
                                <?php echo esc_html($first_category->name); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($args['show_views']) : ?>
                            <span class="news-card__views">
                                <i class="far fa-eye"></i>
                                <?php echo intval($views); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($args['show_author']) : ?>
                            <span class="news-card__author">
                                <i class="far fa-user"></i>
                                <?php echo esc_html($author); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <h3 class="news-card__title">
                    <?php echo esc_html($post_title); ?>
                </h3>
                
                <?php if ($args['show_excerpt'] && $post_excerpt) : ?>
                    <div class="news-card__excerpt">
                        <?php echo wp_kses_post($post_excerpt); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($args['show_button']) : ?>
                    <span class="news-card__button">
                        <?php echo esc_html($args['button_text']); ?>
                        <i class="fas fa-arrow-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </a>
    </article>
    
    <?php
    
    // Восстанавливаем оригинальный глобальный $post
    wp_reset_postdata();
    $post = $original_post;
    
    return ob_get_clean();
}

/**
 * Шорткод для вывода карточки новости
 */
function severcon_news_card_shortcode($atts) {
    $atts = shortcode_atts([
        'id'            => 0,
        'style'         => 'grid',
        'size'          => 'severcon-news-medium',
        'show_image'    => true,
        'show_date'     => true,
        'show_cat'      => true,
        'show_excerpt'  => true,
        'excerpt_length' => 15,
        'show_button'   => true,
        'button_text'   => __('Читать далее', 'severcon'),
        'class'         => '',
        'show_views'    => false,
        'show_author'   => false,
    ], $atts, 'severcon_news_card');
    
    // Преобразуем строковые значения в boolean
    $bool_fields = ['show_image', 'show_date', 'show_cat', 'show_excerpt', 'show_button', 'show_views', 'show_author'];
    foreach ($bool_fields as $field) {
        if (isset($atts[$field])) {
            $atts[$field] = filter_var($atts[$field], FILTER_VALIDATE_BOOLEAN);
        }
    }
    
    return severcon_news_card([
        'post'          => intval($atts['id']),
        'style'         => $atts['style'],
        'size'          => $atts['size'],
        'show_image'    => $atts['show_image'],
        'show_date'     => $atts['show_date'],
        'show_cat'      => $atts['show_cat'],
        'show_excerpt'  => $atts['show_excerpt'],
        'excerpt_length'=> intval($atts['excerpt_length']),
        'show_button'   => $atts['show_button'],
        'button_text'   => $atts['button_text'],
        'class'         => $atts['class'],
        'show_views'    => $atts['show_views'],
        'show_author'   => $atts['show_author'],
    ]);
}
add_shortcode('severcon_news_card', 'severcon_news_card_shortcode');

/**
 * Функция для вывода сетки новостей
 */
function severcon_news_grid($posts, $args = []) {
    if (empty($posts)) {
        return '<p class="no-news">' . __('Новости не найдены', 'severcon') . '</p>';
    }
    
    $defaults = [
        'columns' => 3,
        'style'   => 'grid',
        'class'   => '',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $classes = ['news-grid', 'news-grid--' . $args['columns'] . '-cols'];
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    ob_start();
    ?>
    
    <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
        <?php foreach ($posts as $post_item) : 
            $news_card_args = array_merge($args, ['post' => $post_item]);
            echo severcon_news_card($news_card_args);
        endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Функция для вывода блока последних новостей
 */
function severcon_latest_news_block($args = []) {
    $defaults = [
        'title'         => __('Последние новости', 'severcon'),
        'count'         => 4,
        'category'      => '',
        'style'         => 'grid',
        'columns'       => 4,
        'show_button'   => true,
        'button_text'   => __('Все новости', 'severcon'),
        'button_url'    => get_permalink(get_option('page_for_posts')),
        'class'         => '',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Аргументы для WP_Query
    $query_args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $args['count'],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    
    if ($args['category']) {
        $query_args['category_name'] = $args['category'];
    }
    
    $news_query = new WP_Query($query_args);
    
    if (!$news_query->have_posts()) {
        return '<p class="no-news">' . __('Новости не найдены', 'severcon') . '</p>';
    }
    
    ob_start();
    ?>
    
    <div class="latest-news-block <?php echo esc_attr($args['class']); ?>">
        <?php if ($args['title']) : ?>
            <h2 class="latest-news-block__title">
                <?php echo esc_html($args['title']); ?>
            </h2>
        <?php endif; ?>
        
        <?php 
        echo severcon_news_grid($news_query->posts, [
            'style'   => $args['style'],
            'columns' => $args['columns'],
        ]);
        ?>
        
        <?php if ($args['show_button'] && $args['button_url']) : ?>
            <div class="latest-news-block__footer">
                <a href="<?php echo esc_url($args['button_url']); ?>" class="latest-news-block__button">
                    <?php echo esc_html($args['button_text']); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    
    wp_reset_postdata();
    
    return ob_get_clean();
}

/**
 * Шорткод для вывода блока последних новостей
 */
function severcon_latest_news_shortcode($atts) {
    $atts = shortcode_atts([
        'title'       => __('Последние новости', 'severcon'),
        'count'       => 4,
        'category'    => '',
        'style'       => 'grid',
        'columns'     => 4,
        'show_button' => true,
        'button_text' => __('Все новости', 'severcon'),
        'button_url'  => '',
        'class'       => '',
    ], $atts, 'severcon_latest_news');
    
    // Если URL кнопки не указан, используем страницу блога
    if (empty($atts['button_url'])) {
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            $atts['button_url'] = get_permalink($blog_page_id);
        } else {
            $atts['button_url'] = get_post_type_archive_link('post');
        }
    }
    
    // Преобразуем строковые значения
    $atts['count'] = intval($atts['count']);
    $atts['columns'] = intval($atts['columns']);
    $atts['show_button'] = filter_var($atts['show_button'], FILTER_VALIDATE_BOOLEAN);
    
    return severcon_latest_news_block($atts);
}
add_shortcode('severcon_latest_news', 'severcon_latest_news_shortcode');