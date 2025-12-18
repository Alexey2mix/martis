<?php
/**
 * Шаблон поиска
 */
get_header(); ?>

<div class="container">
    <div class="search-content">
        <header class="search-header">
            <h1 class="search-title">
                <?php
                printf(__('Результаты поиска для: %s', 'severcon'), '<span>' . get_search_query() . '</span>');
                ?>
            </h1>
        </header>
        
        <?php if (have_posts()) : ?>
            <div class="search-results">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="search-result">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="search-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
            
        <?php else : ?>
            <div class="no-results">
                <p>По вашему запросу ничего не найдено.</p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>