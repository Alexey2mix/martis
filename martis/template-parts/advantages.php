<?php
// template-parts/advantages.php

$advantages = new WP_Query(array(
    'post_type' => 'advantages',
    'posts_per_page' => 3,
    'orderby' => 'menu_order',
    'order' => 'ASC'
));

if ($advantages->have_posts()) :
?>
<div class="advantages-grid">
    <?php while ($advantages->have_posts()) : $advantages->the_post(); ?>
        <div class="advantage-card">
            <div class="advantage-icon">
                <i class="<?php echo esc_attr(get_post_meta(get_the_ID(), 'advantage_icon', true)); ?>"></i>
            </div>
            <h3 class="advantage-title"><?php the_title(); ?></h3>
            <div class="advantage-description"><?php the_content(); ?></div>
        </div>
    <?php endwhile; ?>
</div>
<?php
endif;
wp_reset_postdata();