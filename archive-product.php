<?php get_header(); ?>

<!-- Хлебные крошки -->
<div class="breadcrumbs-section">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?php echo home_url(); ?>">Главная</a>
            <span class="separator">/</span>
            <span class="current">Каталог</span>
        </div>
    </div>
</div>

<!-- Главная страница каталога -->
<div class="catalog-main-page">
    <div class="container">
        <!-- Заголовок и описание каталога -->
        <div class="catalog-header">
            <h1 class="catalog-title">Каталог товаров</h1>
            <div class="catalog-description">
                Официальный дистрибьютор климатической техники ведущих брендов.
                Широкий ассортимент оборудования для вентиляции, кондиционирования и отопления.
                Профессиональные решения для промышленных и бытовых объектов.
            </div>
        </div>

        <?php
        // Получаем главные категории (первого уровня)
        $main_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ));
        
        if ($main_categories && !is_wp_error($main_categories)) :
            foreach ($main_categories as $main_category) : 
                // Получаем подкатегории первого уровня
                $subcategories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'parent' => $main_category->term_id,
                    'hide_empty' => true,
                    'orderby' => 'menu_order',
                    'order' => 'ASC'
                ));
                
                if ($subcategories && !is_wp_error($subcategories)) :
        ?>
                    <!-- Раздел с главной категорией -->
                    <section class="catalog-section">
                        <!-- Заголовок и описание раздела -->
                        <div class="catalog-section-header">
                            <h2 class="section-title catalog-section-title">
                                <?php echo esc_html($main_category->name); ?>
                            </h2>
                            <?php if ($main_category->description) : ?>
                                <div class="catalog-section-description boxed">
                                    <?php echo wpautop($main_category->description); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Сетка подкатегорий -->
                        <div class="subcategories-grid compact-grid">
                            <?php foreach ($subcategories as $subcategory) : 
                                $thumbnail_id = get_term_meta($subcategory->term_id, 'thumbnail_id', true);
                                $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();
                                $term_link = get_term_link($subcategory);
                            ?>
                                <div class="subcategory-item compact-item">
                                    <a href="<?php echo esc_url($term_link); ?>" class="subcategory-card compact-card">
                                        <div class="subcategory-image compact-image">
                                            <img src="<?php echo esc_url($image_url); ?>" 
                                                 alt="<?php echo esc_attr($subcategory->name); ?>"
                                                 loading="lazy">
                                            <div class="subcategory-overlay compact-overlay">
                                                <span class="view-more">Перейти</span>
                                            </div>
                                        </div>
                                        <div class="subcategory-info compact-info">
                                            <h3 class="subcategory-title compact-title">
                                                <?php echo esc_html($subcategory->name); ?>
                                            </h3>
                                            <div class="subcategory-count compact-count">
                                                <?php echo $subcategory->count; ?> товаров
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                <?php endif; // if subcategories ?>
            <?php endforeach; // foreach main_categories ?>
            
        <?php else : ?>
            <div class="no-categories">
                <p>Категории товаров не найдены.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>