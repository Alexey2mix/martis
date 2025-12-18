<?php
/**
 * Шаблон архива товаров WooCommerce
 * Оптимизирован для работы с компонентами и CSS из main.css
 */

get_header();

// Получаем ID текущей категории
$current_category = get_queried_object();
$category_id = $current_category ? $current_category->term_id : 0;

// Параметры сортировки
$orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'menu_order';
$per_page = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();
?>

<div class="product-category-page">
    <!-- Хлебные крошки из компонента -->
    <?php 
    if (function_exists('severcon_breadcrumbs')) {
        echo severcon_breadcrumbs(['wrapper' => true]);
    }
    ?>
    
    <div class="container">
        
        <!-- Заголовок категории -->
        <header class="category-header">
            <h1 class="category-title"><?php woocommerce_page_title(); ?></h1>
            
            <?php if (term_description()) : ?>
                <div class="category-description">
                    <?php echo term_description(); ?>
                </div>
            <?php endif; ?>
        </header>
        
        <div class="category-content">
            
            <!-- Сайдбар с фильтрами -->
            <aside class="category-sidebar">
                <!-- Кнопка мобильных фильтров -->
                <button class="filters-toggle-btn" id="mobileFiltersToggle" aria-expanded="false" aria-controls="filtersContainer">
                    <i class="fas fa-sliders-h"></i>
                    <span><?php _e('Фильтры', 'severcon'); ?></span>
                    <span class="filters-count" id="activeFiltersCount">(0)</span>
                </button>
                
                <!-- Контейнер фильтров -->
                <div id="filtersContainer" class="filters-container">
                    
                    <!-- Активные фильтры -->
                    <div class="active-filters-container" id="activeFiltersContainer">
                        <div class="active-filters" id="activeFilters">
                            <!-- Активные фильтры будут добавляться через JS -->
                        </div>
                        <button class="clear-all-filters" id="clearAllFilters">
                            <?php _e('Очистить все', 'severcon'); ?>
                        </button>
                    </div>
                    
                    <!-- Сортировка в фильтрах -->
                    <div class="filters-sorting">
                        <label for="filtersOrderby"><?php _e('Сортировка:', 'severcon'); ?></label>
                        <select id="filtersOrderby" class="orderby" aria-label="<?php esc_attr_e('Сортировка товаров', 'severcon'); ?>">
                            <option value="menu_order" <?php selected($orderby, 'menu_order'); ?>>
                                <?php _e('По умолчанию', 'severcon'); ?>
                            </option>
                            <option value="popularity" <?php selected($orderby, 'popularity'); ?>>
                                <?php _e('По популярности', 'severcon'); ?>
                            </option>
                            <option value="rating" <?php selected($orderby, 'rating'); ?>>
                                <?php _e('По рейтингу', 'severcon'); ?>
                            </option>
                            <option value="date" <?php selected($orderby, 'date'); ?>>
                                <?php _e('По новизне', 'severcon'); ?>
                            </option>
                            <option value="price" <?php selected($orderby, 'price'); ?>>
                                <?php _e('По возрастанию цены', 'severcon'); ?>
                            </option>
                            <option value="price-desc" <?php selected($orderby, 'price-desc'); ?>>
                                <?php _e('По убыванию цены', 'severcon'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <!-- Основные фильтры -->
                    <div class="compact-filters" id="compactFilters">
                        <div class="filters-collapse" id="filtersCollapse">
                            <?php
                            // Получаем атрибуты товаров
                            $attribute_taxonomies = wc_get_attribute_taxonomies();
                            
                            if ($attribute_taxonomies) :
                                foreach ($attribute_taxonomies as $index => $attribute) :
                                    $taxonomy = 'pa_' . $attribute->attribute_name;
                                    
                                    // Получаем термины
                                    $terms = get_terms([
                                        'taxonomy'   => $taxonomy,
                                        'hide_empty' => true,
                                        'orderby'    => 'name',
                                        'order'      => 'ASC',
                                    ]);
                                    
                                    if (is_wp_error($terms) || empty($terms)) {
                                        continue;
                                    }
                                    
                                    // Определяем тип раскладки на основе количества терминов
                                    $term_count = count($terms);
                                    $layout = 'single-row';
                                    if ($term_count <= 4) {
                                        $layout = 'four-columns';
                                    } elseif ($term_count <= 8) {
                                        $layout = 'two-columns';
                                    } elseif ($term_count <= 12) {
                                        $layout = 'three-columns';
                                    }
                                    
                                    // Группа фильтров
                                    ?>
                                    <div class="filter-group-wrapper compact-filter-group" 
                                         data-attribute="<?php echo esc_attr($attribute->attribute_name); ?>"
                                         data-layout="<?php echo esc_attr($layout); ?>">
                                        
                                        <div class="filter-group-header">
                                            <button class="filter-group-toggle" data-toggle="collapse" data-target="#filterGroup<?php echo esc_attr($attribute->attribute_name); ?>">
                                                <span class="filter-group-title">
                                                    <?php echo esc_html($attribute->attribute_label); ?>
                                                </span>
                                                <span class="filter-group-count">(<?php echo count($terms); ?>)</span>
                                                <span class="toggle-icon"><i class="fas fa-chevron-down"></i></span>
                                            </button>
                                            
                                            <div class="group-actions">
                                                <button class="group-action-select-all" title="<?php esc_attr_e('Выбрать все', 'severcon'); ?>">
                                                    <i class="fas fa-check-square"></i>
                                                </button>
                                                <button class="group-action-reset" title="<?php esc_attr_e('Сбросить', 'severcon'); ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="filter-group-content collapse" id="filterGroup<?php echo esc_attr($attribute->attribute_name); ?>">
                                            <?php if ($term_count > 8) : ?>
                                                <div class="filter-search-container">
                                                    <i class="fas fa-search"></i>
                                                    <input type="search" 
                                                           class="filter-search" 
                                                           placeholder="<?php esc_attr_e('Поиск...', 'severcon'); ?>"
                                                           data-attribute="<?php echo esc_attr($attribute->attribute_name); ?>">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="filter-grid">
                                                <?php 
                                                $visible_terms = array_slice($terms, 0, 8);
                                                $hidden_terms = array_slice($terms, 8);
                                                
                                                // Видимые термины
                                                foreach ($visible_terms as $term) :
                                                    $term_count = $term->count;
                                                    $is_available = $term_count > 0;
                                                    $item_class = 'filter-grid-item';
                                                    if (!$is_available) {
                                                        $item_class .= ' unavailable';
                                                    }
                                                    if ($term_count === 0) {
                                                        $item_class .= ' zero-items';
                                                    }
                                                    ?>
                                                    <div class="<?php echo esc_attr($item_class); ?>" 
                                                         data-term-id="<?php echo esc_attr($term->term_id); ?>"
                                                         data-term-slug="<?php echo esc_attr($term->slug); ?>">
                                                        
                                                        <div class="custom-checkbox">
                                                            <input type="checkbox" 
                                                                   class="filter-checkbox" 
                                                                   id="filter_<?php echo esc_attr($attribute->attribute_name . '_' . $term->term_id); ?>"
                                                                   name="<?php echo esc_attr($attribute->attribute_name); ?>[]"
                                                                   value="<?php echo esc_attr($term->slug); ?>"
                                                                   <?php echo $is_available ? '' : 'disabled'; ?>>
                                                            <span class="checkmark"></span>
                                                        </div>
                                                        
                                                        <div class="filter-item-content">
                                                            <span class="filter-item-text">
                                                                <?php echo esc_html($term->name); ?>
                                                            </span>
                                                            <span class="filter-item-count">
                                                                <?php echo esc_html($term_count); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                
                                                <?php if (!empty($hidden_terms)) : ?>
                                                    <?php foreach ($hidden_terms as $term) : 
                                                        $term_count = $term->count;
                                                        $is_available = $term_count > 0;
                                                        $item_class = 'filter-grid-item initially-hidden';
                                                        if (!$is_available) {
                                                            $item_class .= ' unavailable';
                                                        }
                                                        if ($term_count === 0) {
                                                            $item_class .= ' zero-items';
                                                        }
                                                    ?>
                                                        <div class="<?php echo esc_attr($item_class); ?>" 
                                                             data-term-id="<?php echo esc_attr($term->term_id); ?>"
                                                             data-term-slug="<?php echo esc_attr($term->slug); ?>">
                                                            
                                                            <div class="custom-checkbox">
                                                                <input type="checkbox" 
                                                                       class="filter-checkbox" 
                                                                       id="filter_<?php echo esc_attr($attribute->attribute_name . '_' . $term->term_id); ?>"
                                                                       name="<?php echo esc_attr($attribute->attribute_name); ?>[]"
                                                                       value="<?php echo esc_attr($term->slug); ?>"
                                                                       <?php echo $is_available ? '' : 'disabled'; ?>>
                                                                <span class="checkmark"></span>
                                                            </div>
                                                            
                                                            <div class="filter-item-content">
                                                                <span class="filter-item-text">
                                                                    <?php echo esc_html($term->name); ?>
                                                                </span>
                                                                <span class="filter-item-count">
                                                                    <?php echo esc_html($term_count); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($hidden_terms)) : ?>
                                                <div class="filter-grid-controls">
                                                    <button class="show-more-filters" data-attribute="<?php echo esc_attr($attribute->attribute_name); ?>">
                                                        <span class="show-more-text"><?php _e('Показать еще', 'severcon'); ?></span>
                                                        <span class="show-less-text"><?php _e('Скрыть', 'severcon'); ?></span>
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                    <span class="filtered-count">
                                                        <?php printf(__('+%d еще', 'severcon'), count($hidden_terms)); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif;
                            ?>
                        </div>
                        
                        <!-- Кнопки фильтров -->
                        <div class="filters-actions">
                            <button class="apply-filters btn btn-primary" id="applyFilters">
                                <i class="fas fa-check"></i>
                                <?php _e('Применить фильтры', 'severcon'); ?>
                            </button>
                            <button class="reset-filters btn btn-secondary" id="resetFilters">
                                <i class="fas fa-times"></i>
                                <?php _e('Сбросить все', 'severcon'); ?>
                            </button>
                        </div>
                    </div>
                    
                </div>
            </aside>
            
            <!-- Основной контент -->
            <main class="category-main">
                
                <!-- Панель инструментов -->
                <div class="category-toolbar">
                    <div class="toolbar-left">
                        <p class="woocommerce-result-count" id="resultCount">
                            <?php
                            global $wp_query;
                            $total = $wp_query->found_posts;
                            $per_page = $wp_query->get('posts_per_page');
                            $current = max(1, get_query_var('paged'));
                            $first = ($per_page * $current) - $per_page + 1;
                            $last = min($total, $per_page * $current);
                            
                            if ($total <= $per_page || -1 === $per_page) {
                                printf(
                                    _n('Найден %d товар', 'Найдено %d товаров', $total, 'severcon'),
                                    $total
                                );
                            } else {
                                printf(
                                    _nx(
                                        'Показано %1$d&ndash;%2$d из %3$d товара',
                                        'Показано %1$d&ndash;%2$d из %3$d товаров',
                                        $total,
                                        '%1$d = first, %2$d = last, %3$d = total',
                                        'severcon'
                                    ),
                                    $first,
                                    $last,
                                    $total
                                );
                            }
                            ?>
                        </p>
                    </div>
                    
                    <div class="toolbar-right">
                        <!-- Вид отображения -->
                        <div class="toolbar-view">
                            <button class="view-btn view-grid active" 
                                    data-view="grid" 
                                    aria-label="<?php esc_attr_e('Сетка', 'severcon'); ?>">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn view-list" 
                                    data-view="list" 
                                    aria-label="<?php esc_attr_e('Список', 'severcon'); ?>">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Сетка товаров -->
                <div class="products-grid category-products-grid" 
                     id="productsGrid"
                     data-category-id="<?php echo esc_attr($category_id); ?>"
                     data-orderby="<?php echo esc_attr($orderby); ?>"
                     data-page="1"
                     data-per-page="<?php echo esc_attr($per_page); ?>">
                    
                    <?php
                    if (woocommerce_product_loop()) :
                        woocommerce_product_loop_start();
                        
                        while (have_posts()) : the_post();
                            // Используем наш компонент карточки товара с параметрами из main.css
                            echo severcon_product_card([
                                'style'       => 'grid',
                                'show_image'  => true,
                                'show_title'  => true,
                                'show_price'  => true,
                                'show_rating' => true,
                                'show_excerpt'=> false,
                                'show_button' => true,
                                'lazy_load'   => true,
                                'class'       => 'product-card--catalog', // Добавляем специальный класс
                            ]);
                        endwhile;
                        
                        woocommerce_product_loop_end();
                        
                    else :
                        ?>
                        <div class="no-products">
                            <div class="no-products__content">
                                <i class="fas fa-search no-products__icon"></i>
                                <h3 class="no-products__title"><?php _e('Товары не найдены', 'severcon'); ?></h3>
                                <p class="no-products__description">
                                    <?php _e('Попробуйте изменить параметры фильтрации или выберите другую категорию.', 'severcon'); ?>
                                </p>
                                <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" 
                                   class="btn btn-primary no-products__button">
                                    <?php _e('Вернуться в магазин', 'severcon'); ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    endif;
                    ?>
                    
                </div>
                
                <!-- Пагинация -->
                <?php if ($wp_query->max_num_pages > 1) : ?>
                    <div class="category-pagination">
                        <nav class="woocommerce-pagination">
                            <?php
                            echo paginate_links([
                                'base'      => esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false)))),
                                'format'    => '',
                                'add_args'  => false,
                                'current'   => max(1, get_query_var('paged')),
                                'total'     => $wp_query->max_num_pages,
                                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                'next_text' => '<i class="fas fa-chevron-right"></i>',
                                'type'      => 'list',
                                'end_size'  => 3,
                                'mid_size'  => 3,
                            ]);
                            ?>
                        </nav>
                    </div>
                <?php endif; ?>
                
                <!-- Кнопка "Показать все" -->
                <?php if ($total > $per_page) : ?>
                    <div class="view-all-container">
                        <button class="view-all-products btn btn-outline" id="viewAllProducts">
                            <i class="fas fa-eye"></i>
                            <?php _e('Показать все товары', 'severcon'); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Описание категории внизу -->
                <?php 
                $bottom_description = get_term_meta($category_id, 'bottom_description', true);
                if ($bottom_description) : 
                ?>
                    <div class="category-bottom-description">
                        <div class="category-bottom-description__content">
                            <?php echo wp_kses_post(wpautop($bottom_description)); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </main>
            
        </div>
        
    </div>
</div>

<script type="text/javascript">
// Инициализация фильтров после загрузки страницы
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли JS модули фильтров
    if (typeof window.severconFilters !== 'undefined') {
        window.severconFilters.initCategoryPage({
            categoryId: <?php echo $category_id; ?>,
            orderby: '<?php echo $orderby; ?>',
            productsContainer: '#productsGrid',
            filtersContainer: '#filtersContainer',
            resultCount: '#resultCount',
            activeFilters: '#activeFilters',
            activeFiltersCount: '#activeFiltersCount'
        });
    }
    
    // Переключение вида товаров
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const grid = document.querySelector('#productsGrid');
            
            // Обновляем активную кнопку
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Меняем стиль карточек товаров
            const cards = grid.querySelectorAll('.product-card');
            cards.forEach(card => {
                card.classList.remove('product-card--grid', 'product-card--list');
                card.classList.add(`product-card--${view}`);
            });
            
            // Меняем класс сетки
            grid.classList.remove('products-grid--grid', 'products-grid--list');
            grid.classList.add(`products-grid--${view}`);
            
            // Сохраняем в localStorage
            localStorage.setItem('productView', view);
        });
    });
    
    // Загружаем сохраненный вид
    const savedView = localStorage.getItem('productView') || 'grid';
    const viewBtn = document.querySelector(`.view-btn[data-view="${savedView}"]`);
    if (viewBtn) {
        viewBtn.click();
    }
});
</script>

<?php get_footer(); ?>