<?php
/**
 * Шаблон 404 ошибки
 */
get_header(); ?>

<div class="container">
    <div class="error-404">
        <h1>404 - Страница не найдена</h1>
        <p>Извините, но страница, которую вы ищете, не существует.</p>
        <div class="search-form">
            <?php get_search_form(); ?>
        </div>
        <a href="<?php echo home_url(); ?>" class="back-home">Вернуться на главную</a>
    </div>
</div>

<?php get_footer(); ?>