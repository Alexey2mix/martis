<?php get_header(); ?>

<!-- Хлебные крошки -->
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <div class="breadcrumbs__container">
        <ul class="breadcrumbs__list">
            <li class="breadcrumbs__item">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumbs__link">Главная</a>
            </li>
            <?php if (is_single()) { ?>
                <li class="breadcrumbs__item">
                    <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="breadcrumbs__link">Блог</a>
                </li>
                <li class="breadcrumbs__item">
                    <span class="breadcrumbs__current"><?php the_title(); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>

<!-- Основной контент -->
<div class="news-archive-page">
    <div class="container">
        <!-- Заголовок раздела -->
        <div class="archive-header">
            <h1 class="archive-title">Новости</h1>
        </div>

        <!-- Контейнер для новостей -->
        <div id="news-container">
            <!-- Последняя новость (на всю ширину) -->
            <?php if (have_posts()) : ?>
                <?php 
                $first_post = true;
                while (have_posts()) : the_post(); 
                    if ($first_post) : 
                        $first_post = false;
                ?>
                    <div class="featured-news">
                        <a href="<?php the_permalink(); ?>" class="featured-news-card">
                            <div class="featured-news-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large'); ?>
                                <?php else : ?>
                                    <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="<?php the_title(); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="featured-news-content">
                                <div class="news-date"><?php echo get_the_date('d.m.Y'); ?></div>
                                <h2 class="news-title"><?php the_title(); ?></h2>
                                <div class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 25); ?></div>
                                <span class="read-more">Читать далее</span>
                            </div>
                        </a>
                    </div>

                    <!-- Остальные новости в 3 колонки -->
                    <div class="news-archive-grid" id="news-grid">
                <?php else : ?>
                        <div class="news-archive-item" data-post-id="<?php the_ID(); ?>">
                            <a href="<?php the_permalink(); ?>" class="news-archive-card">
                                <div class="news-archive-image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('news-thumb'); ?>
                                    <?php else : ?>
                                        <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="<?php the_title(); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="news-archive-content">
                                    <div class="news-date"><?php echo get_the_date('d.m.Y'); ?></div>
                                    <h3 class="news-title"><?php the_title(); ?></h3>
                                    <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                                </div>
                            </a>
                        </div>
                <?php endif; ?>
                <?php endwhile; ?>
                    </div><!-- .news-archive-grid -->
            <?php else : ?>
                <div class="no-news">
                    <p>Новости не найдены.</p>
                </div>
            <?php endif; ?>
        </div><!-- #news-container -->

        <!-- Кнопка "Показать еще" -->
        <div class="load-more-container">
            <button id="load-more-news" class="load-more-btn" data-page="1" data-max-pages="<?php echo $wp_query->max_num_pages; ?>">
                <span class="btn-text">Показать еще</span>
                <span class="loading-spinner" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Загрузка...
                </span>
            </button>
        </div>

    </div>
</div>

<?php get_footer(); ?>