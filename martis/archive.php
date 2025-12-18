<?php
/**
 * Шаблон архива
 */
get_header(); ?>

<div class="container">
    <div class="archive-content">
        <header class="archive-header">
            <h1 class="archive-title">
                <?php
                if (is_category()) {
                    single_cat_title();
                } elseif (is_tag()) {
                    single_tag_title();
                } elseif (is_author()) {
                    the_author();
                } elseif (is_date()) {
                    the_archive_title();
                } else {
                    post_type_archive_title();
                }
                ?>
            </h1>
            <?php the_archive_description(); ?>
        </header>
        
        <div class="posts-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article class="post-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('severcon-thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                            </div>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="read-more">Подробнее</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php _e('Записи не найдены.', 'severcon'); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <?php the_posts_pagination(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>