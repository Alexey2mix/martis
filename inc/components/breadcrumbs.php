<?php
/**
 * Компонент: Хлебные крошки
 * 
 * @param array $args {
 *     @type array  $items      Массив элементов хлебных крошек
 *     @type string $home_text  Текст для ссылки "Главная"
 *     @type string $home_url   URL для ссылки "Главная"
 *     @type string $separator  Разделитель между элементами
 *     @type string $class      Дополнительные CSS классы
 *     @type string $id         ID контейнера
 *     @type bool   $show_home  Показывать ссылку "Главная"
 *     @type bool   $show_last  Показывать последний элемент как ссылку
 * }
 */

function severcon_breadcrumbs($args = []) {
    // Аргументы по умолчанию
    $defaults = [
        'items'      => [],
        'home_text'  => __('Главная', 'severcon'),
        'home_url'   => home_url('/'),
        'separator'  => '<span class="breadcrumb-separator">›</span>',
        'class'      => 'breadcrumbs',
        'id'         => '',
        'show_home'  => true,
        'show_last'  => false,
        'wrapper'    => true,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Если массив items не передан, генерируем автоматически
    if (empty($args['items'])) {
        $args['items'] = severcon_generate_breadcrumbs();
    }
    
    // Если после генерации массив пуст, не выводим крошки
    if (empty($args['items'])) {
        return '';
    }
    
    // Начинаем формировать HTML
    ob_start();
    
    // Открывающий тег
    $wrapper_class = $args['wrapper'] ? 'breadcrumbs-wrapper' : '';
    $id_attr = $args['id'] ? sprintf('id="%s"', esc_attr($args['id'])) : '';
    
    if ($args['wrapper']) {
        echo '<div class="' . esc_attr($wrapper_class) . '" ' . $id_attr . '>';
        echo '<div class="container">';
    }
    
    ?>
    
    <nav class="<?php echo esc_attr($args['class']); ?>" aria-label="<?php esc_attr_e('Хлебные крошки', 'severcon'); ?>">
        <ul class="breadcrumbs__list">
            <?php if ($args['show_home']) : ?>
                <li class="breadcrumbs__item breadcrumbs__item--home">
                    <a href="<?php echo esc_url($args['home_url']); ?>" class="breadcrumbs__link">
                        <i class="fas fa-home"></i>
                        <span class="breadcrumbs__text"><?php echo esc_html($args['home_text']); ?></span>
                    </a>
                </li>
                <?php if (!empty($args['items'])) : ?>
                    <li class="breadcrumbs__separator"><?php echo $args['separator']; ?></li>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php 
            $items_count = count($args['items']);
            foreach ($args['items'] as $index => $item) : 
                $is_last = ($index === $items_count - 1);
                $item_class = 'breadcrumbs__item';
                if ($is_last) {
                    $item_class .= ' breadcrumbs__item--current';
                }
                
                // Определяем, показывать ли последний элемент как ссылку
                $show_as_link = !$is_last || ($is_last && $args['show_last']);
            ?>
                <li class="<?php echo esc_attr($item_class); ?>">
                    <?php if ($show_as_link && !empty($item['url'])) : ?>
                        <a href="<?php echo esc_url($item['url']); ?>" class="breadcrumbs__link">
                            <span class="breadcrumbs__text"><?php echo esc_html($item['title']); ?></span>
                        </a>
                    <?php else : ?>
                        <span class="breadcrumbs__current" aria-current="page">
                            <?php echo esc_html($item['title']); ?>
                        </span>
                    <?php endif; ?>
                </li>
                
                <?php if (!$is_last) : ?>
                    <li class="breadcrumbs__separator"><?php echo $args['separator']; ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <?php
    
    // Закрывающий тег
    if ($args['wrapper']) {
        echo '</div></div>';
    }
    
    return ob_get_clean();
}

/**
 * Автоматическая генерация хлебных крошек
 */
function severcon_generate_breadcrumbs() {
    $items = [];
    
    // Если это главная страница
    if (is_front_page()) {
        return $items;
    }
    
    // Если это страница WooCommerce магазина
    if (function_exists('is_shop') && is_shop()) {
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id) {
            $items[] = [
                'title' => get_the_title($shop_page_id),
                'url'   => get_permalink($shop_page_id)
            ];
        }
        return $items;
    }
    
    // Если это страница товара WooCommerce
    if (function_exists('is_product') && is_product()) {
        global $product;
        
        // Категории товара
        $terms = wp_get_post_terms($product->get_id(), 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            $main_term = $terms[0];
            $ancestors = get_ancestors($main_term->term_id, 'product_cat');
            
            if ($ancestors) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    $items[] = [
                        'title' => $ancestor->name,
                        'url'   => get_term_link($ancestor)
                    ];
                }
            }
            
            $items[] = [
                'title' => $main_term->name,
                'url'   => get_term_link($main_term)
            ];
        }
        
        // Сам товар
        $items[] = [
            'title' => get_the_title(),
            'url'   => ''
        ];
        
        return $items;
    }
    
    // Если это категория товара WooCommerce
    if (function_exists('is_product_category') && is_product_category()) {
        $current_term = get_queried_object();
        $ancestors = get_ancestors($current_term->term_id, 'product_cat');
        
        if ($ancestors) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $items[] = [
                    'title' => $ancestor->name,
                    'url'   => get_term_link($ancestor)
                ];
            }
        }
        
        $items[] = [
            'title' => $current_term->name,
            'url'   => ''
        ];
        
        return $items;
    }
    
    // Если это страница записи
    if (is_single()) {
        // Категории записи
        $categories = get_the_category();
        if ($categories) {
            $main_category = $categories[0];
            $ancestors = get_ancestors($main_category->term_id, 'category');
            
            if ($ancestors) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor_id) {
                    $ancestor = get_category($ancestor_id);
                    $items[] = [
                        'title' => $ancestor->name,
                        'url'   => get_category_link($ancestor)
                    ];
                }
            }
            
            $items[] = [
                'title' => $main_category->name,
                'url'   => get_category_link($main_category)
            ];
        }
        
        // Сама запись
        $items[] = [
            'title' => get_the_title(),
            'url'   => ''
        ];
        
        return $items;
    }
    
    // Если это страница
    if (is_page() && !is_front_page()) {
        global $post;
        
        $ancestors = get_post_ancestors($post);
        if ($ancestors) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                $items[] = [
                    'title' => get_the_title($ancestor_id),
                    'url'   => get_permalink($ancestor_id)
                ];
            }
        }
        
        $items[] = [
            'title' => get_the_title(),
            'url'   => ''
        ];
        
        return $items;
    }
    
    // Если это архив категории
    if (is_category()) {
        $current_cat = get_queried_object();
        $ancestors = get_ancestors($current_cat->term_id, 'category');
        
        if ($ancestors) {
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_category($ancestor_id);
                $items[] = [
                    'title' => $ancestor->name,
                    'url'   => get_category_link($ancestor)
                ];
            }
        }
        
        $items[] = [
            'title' => $current_cat->name,
            'url'   => ''
        ];
        
        return $items;
    }
    
    // Если это архив тега
    if (is_tag()) {
        $items[] = [
            'title' => sprintf(__('Метка: %s', 'severcon'), single_tag_title('', false)),
            'url'   => ''
        ];
        return $items;
    }
    
    // Если это архив автора
    if (is_author()) {
        $items[] = [
            'title' => sprintf(__('Автор: %s', 'severcon'), get_the_author()),
            'url'   => ''
        ];
        return $items;
    }
    
    // Если это архив даты
    if (is_date()) {
        if (is_year()) {
            $items[] = [
                'title' => get_the_date('Y'),
                'url'   => ''
            ];
        } elseif (is_month()) {
            $items[] = [
                'title' => get_the_date('F Y'),
                'url'   => ''
            ];
        } elseif (is_day()) {
            $items[] = [
                'title' => get_the_date(),
                'url'   => ''
            ];
        }
        return $items;
    }
    
    // Если это страница поиска
    if (is_search()) {
        $items[] = [
            'title' => sprintf(__('Результаты поиска для: %s', 'severcon'), get_search_query()),
            'url'   => ''
        ];
        return $items;
    }
    
    // Если это 404 страница
    if (is_404()) {
        $items[] = [
            'title' => __('404: Страница не найдена', 'severcon'),
            'url'   => ''
        ];
        return $items;
    }
    
    return $items;
}

/**
 * Шорткод для вывода хлебных крошек
 */
function severcon_breadcrumbs_shortcode($atts) {
    $atts = shortcode_atts([
        'home_text'  => __('Главная', 'severcon'),
        'separator'  => '›',
        'class'      => '',
        'show_home'  => true,
        'show_last'  => false,
        'wrapper'    => false,
    ], $atts, 'severcon_breadcrumbs');
    
    return severcon_breadcrumbs($atts);
}
add_shortcode('severcon_breadcrumbs', 'severcon_breadcrumbs_shortcode');