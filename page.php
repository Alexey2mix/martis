<?php
/**
 * Template Name: Страница
 */

get_header();
?>

<main class="main">
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <div class="breadcrumbs__container">
            <ul class="breadcrumbs__list">
                <li class="breadcrumbs__item">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="breadcrumbs__link">Главная</a>
                </li>
                <li class="breadcrumbs__item">
                    <span class="breadcrumbs__current"><?php the_title(); ?></span>
                </li>
            </ul>
        </div>
    </nav>

    <div class="content-container">
        <article class="page-content">
            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>

            <div class="page-content__inner">
                <?php
                if (have_posts()) :
                    while (have_posts()) : the_post();
                        the_content();
                    endwhile;
                endif;
                ?>
            </div>
        </article>
    </div>
</main>

<?php get_footer(); ?>