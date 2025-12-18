<?php get_header(); ?>

<!-- Хлебные крошки -->
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <div class="breadcrumbs__container">
        <ul class="breadcrumbs__list">
            <li class="breadcrumbs__item">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumbs__link">Главная</a>
            </li>
            <li class="breadcrumbs__item">
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="breadcrumbs__link">Блог</a>
            </li>
            <li class="breadcrumbs__item">
                <span class="breadcrumbs__current"><?php the_title(); ?></span>
            </li>
        </ul>
    </div>
</nav>

<!-- Основной контент -->
<div class="single-news-page">
    <div class="container">
        <article class="single-news-article">
            
            <!-- Заголовок и мета-информация -->
            <header class="news-header">
                <div class="news-meta">
                    <div class="news-date">
                        <i class="far fa-calendar"></i>
                        <?php echo get_the_date('d.m.Y'); ?>
                    </div>
                    <?php if (get_the_category()) : ?>
                        <div class="news-category">
                            <i class="far fa-folder"></i>
                            <?php the_category(', '); ?>
                        </div>
                    <?php endif; ?>
                    <div class="news-views">
                        <i class="far fa-eye"></i>
                        Просмотров: <?php echo get_post_meta(get_the_ID(), 'views', true) ?: '0'; ?>
                    </div>
                </div>
                
                <h1 class="news-title"><?php the_title(); ?></h1>
                
                <?php if (has_excerpt()) : ?>
                    <div class="news-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
            </header>

            <!-- Изображение новости -->
            <?php if (has_post_thumbnail()) : ?>
                <div class="news-featured-image">
                    <?php the_post_thumbnail('large', array('class' => 'news-image')); ?>
                </div>
            <?php endif; ?>

            <!-- Контент новости -->
            <div class="news-content">
                <?php the_content(); ?>
            </div>

            <!-- Дополнительные изображения (галерея) -->
            <?php
            $gallery = get_post_gallery($post, false);
            if ($gallery) : ?>
                <div class="news-gallery">
                    <h3>Галерея</h3>
                    <div class="gallery-grid">
                        <?php
                        $gallery_ids = explode(',', $gallery['ids']);
                        foreach ($gallery_ids as $image_id) :
                            $image_url = wp_get_attachment_image_url($image_id, 'large');
                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        ?>
                            <div class="gallery-item">
                                <a href="<?php echo esc_url($image_url); ?>" data-fancybox="gallery">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Теги -->
            <?php if (has_tag()) : ?>
                <div class="news-tags">
                    <h4>Теги:</h4>
                    <div class="tags-list">
                        <?php the_tags('', '', ''); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Навигация между новостями -->
            <div class="news-navigation">
                <div class="nav-previous">
                    <?php previous_post_link('%link', '<i class="fas fa-chevron-left"></i> Предыдущая новость'); ?>
                </div>
                <div class="nav-next">
                    <?php next_post_link('%link', 'Следующая новость <i class="fas fa-chevron-right"></i>'); ?>
                </div>
            </div>

        </article>

        <!-- Блок похожих новостей -->
        <aside class="related-news">
            <h3 class="related-title">Похожие новости</h3>
            <div class="related-grid">
                <?php
                $categories = get_the_category();
                $category_ids = array();
                
                if ($categories) {
                    foreach ($categories as $category) {
                        $category_ids[] = $category->term_id;
                    }
                }
                
                $related_args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => 3,
                    'post__not_in' => array(get_the_ID()),
                    'category__in' => $category_ids,
                    'orderby' => 'rand'
                );
                
                $related_query = new WP_Query($related_args);
                
                if ($related_query->have_posts()) :
                    while ($related_query->have_posts()) : $related_query->the_post();
                ?>
                    <div class="related-item">
                        <a href="<?php the_permalink(); ?>" class="related-card">
                            <div class="related-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('news-thumb'); ?>
                                <?php else : ?>
                                    <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="<?php the_title(); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="related-content">
                                <div class="related-date"><?php echo get_the_date('d.m.Y'); ?></div>
                                <h4 class="related-post-title"><?php the_title(); ?></h4>
                            </div>
                        </a>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>Похожих новостей не найдено.</p>';
                endif;
                ?>
            </div>
        </aside>

    </div>
</div>

<?php get_footer(); ?>