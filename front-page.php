<?php get_header(); ?>

<!-- Вертикальный слайдер -->
<section class="vertical-slider">
    <div class="slider-container">
        <div class="slide-counter">
            <span class="current-slide">01</span>
            <span class="total-slides">/03</span>
        </div>
        
        <!-- Слайд 1 -->
        <div class="slide active">
            <div class="slide-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('slide_1_image', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')); ?>')"></div>
            <div class="slide-description-box">
                <h1 class="slide-title"><?php echo esc_html(get_theme_mod('slide_1_title', 'Официальный дистрибьютор климатической техники')); ?></h1>
                <button class="slide-btn"><?php echo esc_html(get_theme_mod('slide_1_button', 'СМОТРЕТЬ КАТАЛОГ')); ?></button>
            </div>
        </div>
        
        <!-- Слайд 2 -->
        <div class="slide">
            <div class="slide-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('slide_2_image', 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')); ?>')"></div>
            <div class="slide-description-box">
                <h1 class="slide-title"><?php echo esc_html(get_theme_mod('slide_2_title', 'Специальные условия для дилеров')); ?></h1>
                <button class="slide-btn"><?php echo esc_html(get_theme_mod('slide_2_button', 'СТАТЬ ДИЛЕРОМ')); ?></button>
            </div>
        </div>
        
        <!-- Слайд 3 -->
        <div class="slide">
            <div class="slide-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('slide_3_image', 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80')); ?>')"></div>
            <div class="slide-description-box">
                <h1 class="slide-title"><?php echo esc_html(get_theme_mod('slide_3_title', 'Оборудование в наличии на складе')); ?></h1>
                <button class="slide-btn"><?php echo esc_html(get_theme_mod('slide_3_button', 'УЗНАТЬ НАЛИЧИЕ')); ?></button>
            </div>
        </div>
    </div>
    
    <div class="vertical-nav">
        <button class="nav-btn nav-up" aria-label="Предыдущий слайд">
            <i class="fas fa-chevron-up"></i>
        </button>
        <button class="nav-btn nav-down" aria-label="Следующий слайд">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
</section>

<!-- Секция "Наше оборудование" -->
<section class="equipment-section">
    <div class="container">
        <h2 class="section-title"><?php echo esc_html(get_theme_mod('equipment_title', 'Наше оборудование')); ?></h2>
        
        <div class="equipment-grid">
            <div class="equipment-column left-column">
                <a href="<?php echo esc_url(get_theme_mod('equipment_main_link', '/catalog/')); ?>" class="equipment-card main-card">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('equipment_main_image', 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')); ?>')"></div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo esc_html(get_theme_mod('equipment_main_title', 'Климатическое оборудование')); ?></h3>
                    </div>
                </a>
            </div>
            
            <div class="equipment-column right-column">
                <a href="<?php echo esc_url(get_theme_mod('equipment_sub1_link', '/catalog/heating/')); ?>" class="equipment-card sub-card">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('equipment_sub1_image', 'https://images.unsplash.com/photo-1611273426858-450d8e3c9fce?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')); ?>')"></div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo esc_html(get_theme_mod('equipment_sub1_title', 'Отопительное оборудование')); ?></h3>
                    </div>
                </a>
                
                <a href="<?php echo esc_url(get_theme_mod('equipment_sub2_link', '/catalog/accessories/')); ?>" class="equipment-card sub-card">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url(get_theme_mod('equipment_sub2_image', 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')); ?>')"></div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo esc_html(get_theme_mod('equipment_sub2_title', 'Комплектующие')); ?></h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Секция "Наши преимущества" -->
<section class="advantages-section">
    <div class="container">
        <h2 class="section-title"><?php echo esc_html(get_theme_mod('advantages_title', 'Наши преимущества')); ?></h2>
        
        <div class="advantages-grid">
            <?php
            $advantages = new WP_Query(array(
                'post_type' => 'advantages',
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'ASC'
            ));
            
            if ($advantages->have_posts()) :
                while ($advantages->have_posts()) : $advantages->the_post();
                    $icon = get_post_meta(get_the_ID(), 'advantage_icon', true);
            ?>
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="<?php echo esc_attr($icon ?: 'fas fa-award'); ?>"></i>
                    </div>
                    <h3 class="advantage-title"><?php the_title(); ?></h3>
                    <div class="advantage-description"><?php the_content(); ?></div>
                </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                // Fallback на настройки кастомайзера
            ?>
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="<?php echo esc_attr(get_theme_mod('advantage_1_icon', 'fas fa-award')); ?>"></i>
                    </div>
                    <h3 class="advantage-title"><?php echo esc_html(get_theme_mod('advantage_1_title', 'Первый поставщик')); ?></h3>
                    <p class="advantage-description"><?php echo esc_html(get_theme_mod('advantage_1_description', 'Мы являемся первым поставщиком техники Energolux, Ferrum, Titan и других ведущих брендов климатического оборудования')); ?></p>
                </div>
                
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="<?php echo esc_attr(get_theme_mod('advantage_2_icon', 'fas fa-warehouse')); ?>"></i>
                    </div>
                    <h3 class="advantage-title"><?php echo esc_html(get_theme_mod('advantage_2_title', 'Собственный склад')); ?></h3>
                    <p class="advantage-description"><?php echo esc_html(get_theme_mod('advantage_2_description', 'Все оборудование всегда в наличии на нашем складе. Быстрая отгрузка и доставка по всей России')); ?></p>
                </div>
                
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="<?php echo esc_attr(get_theme_mod('advantage_3_icon', 'fas fa-headset')); ?>"></i>
                    </div>
                    <h3 class="advantage-title"><?php echo esc_html(get_theme_mod('advantage_3_title', 'Техническая поддержка')); ?></h3>
                    <p class="advantage-description"><?php echo esc_html(get_theme_mod('advantage_3_description', 'Полное сервисное обслуживание, гарантийная поддержка и консультации от наших специалистов')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Секция "Новости" -->
<section class="news-section">
    <div class="container">
        <h2 class="section-title">Новости</h2>
        
        <div class="news-grid">
            <?php
            $news = new WP_Query(array(
                'posts_per_page' => 4,
                'post_status' => 'publish'
            ));
            
            if ($news->have_posts()) :
                while ($news->have_posts()) : $news->the_post();
            ?>
                <a href="<?php the_permalink(); ?>" class="news-card">
                    <div class="news-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('news-thumb'); ?>
                        <?php else : ?>
                            <img src="https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="news-content">
                        <h3 class="news-title"><?php the_title(); ?></h3>
                        <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    </div>
                </a>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
        
        <div class="news-footer">
            <?php
            $blog_page_url = '';
            
            // Способ 1: Получаем URL страницы блога через настройки
            $page_for_posts = get_option('page_for_posts');
            if ($page_for_posts) {
                $blog_page_url = get_permalink($page_for_posts);
            } 
            // Способ 2: Если страница блога не назначена, используем стандартный архив
            else {
                $blog_page_url = get_post_type_archive_link('post');
            }
            
            // Способ 3: Fallback - если ничего не работает, используем home_url
            if (empty($blog_page_url)) {
                $blog_page_url = home_url('/news/');
            }
            ?>
            <a href="<?php echo esc_url($blog_page_url); ?>" class="all-news-btn">Все новости</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>