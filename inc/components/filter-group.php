<?php
/**
 * Компонент: Группа фильтров
 * 
 * @param array $args {
 *     @type string $attribute      Slug атрибута (без префикса pa_)
 *     @type string $title          Заголовок группы
 *     @type array  $terms          Массив терминов
 *     @type array  $selected       Выбранные значения
 *     @type string $type           Тип фильтра: checkbox, radio, select, color, image
 *     @type bool   $collapsible    Сворачиваемая группа
 *     @type bool   $collapsed      Начальное состояние (свернута)
 *     @type bool   $show_count     Показывать счетчики
 *     @type bool   $show_search    Показывать поле поиска
 *     @type int    $limit          Лимит отображаемых элементов
 *     @type string $layout         Расположение: vertical, horizontal, grid
 *     @type string $class          Дополнительные CSS классы
 * }
 */

function severcon_filter_group($args = []) {
    // Аргументы по умолчанию
    $defaults = [
        'attribute'   => '',
        'title'       => '',
        'terms'       => [],
        'selected'    => [],
        'type'        => 'checkbox',
        'collapsible' => true,
        'collapsed'   => false,
        'show_count'  => true,
        'show_search' => false,
        'limit'       => 0,
        'layout'      => 'vertical',
        'class'       => '',
        'taxonomy'    => '', // Полное название таксономии (pa_color и т.д.)
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // Если таксономия не указана, формируем из атрибута
    if (empty($args['taxonomy']) && !empty($args['attribute'])) {
        $args['taxonomy'] = 'pa_' . $args['attribute'];
    }
    
    // Если термины не переданы, получаем их из таксономии
    if (empty($args['terms']) && !empty($args['taxonomy'])) {
        $args['terms'] = get_terms([
            'taxonomy'   => $args['taxonomy'],
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        
        if (is_wp_error($args['terms'])) {
            return '';
        }
    }
    
    // Если терминов нет или они не являются массивом
    if (empty($args['terms']) || !is_array($args['terms'])) {
        return '';
    }
    
    // Ограничиваем количество отображаемых терминов если нужно
    $all_terms = $args['terms'];
    $display_terms = $args['limit'] > 0 ? array_slice($all_terms, 0, $args['limit']) : $all_terms;
    $has_more = $args['limit'] > 0 && count($all_terms) > $args['limit'];
    
    // Формируем ID для группы
    $group_id = 'filter-group-' . ($args['attribute'] ?: sanitize_title($args['title']));
    $content_id = $group_id . '-content';
    
    // Классы группы
    $group_classes = ['filter-group', 'filter-group--' . $args['type'], 'filter-group--' . $args['layout']];
    
    if ($args['collapsible']) {
        $group_classes[] = 'filter-group--collapsible';
    }
    
    if ($args['collapsed']) {
        $group_classes[] = 'filter-group--collapsed';
    }
    
    if ($args['class']) {
        $group_classes[] = $args['class'];
    }
    
    // Начинаем вывод
    ob_start();
    ?>
    
    <div class="<?php echo esc_attr(implode(' ', $group_classes)); ?>" 
         data-attribute="<?php echo esc_attr($args['attribute']); ?>"
         data-taxonomy="<?php echo esc_attr($args['taxonomy']); ?>"
         data-type="<?php echo esc_attr($args['type']); ?>">
        
        <!-- Заголовок группы -->
        <div class="filter-group__header">
            <?php if ($args['title']) : ?>
                <h3 class="filter-group__title">
                    <?php echo esc_html($args['title']); ?>
                </h3>
            <?php endif; ?>
            
            <?php if ($args['collapsible']) : ?>
                <button class="filter-group__toggle" 
                        type="button"
                        aria-expanded="<?php echo $args['collapsed'] ? 'false' : 'true'; ?>"
                        aria-controls="<?php echo esc_attr($content_id); ?>">
                    <span class="filter-group__toggle-icon">
                        <i class="fas fa-chevron-<?php echo $args['collapsed'] ? 'down' : 'up'; ?>"></i>
                    </span>
                    <span class="screen-reader-text">
                        <?php echo $args['collapsed'] ? __('Развернуть', 'severcon') : __('Свернуть', 'severcon'); ?>
                    </span>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Тело группы -->
        <div class="filter-group__body" 
             id="<?php echo esc_attr($content_id); ?>"
             style="<?php echo $args['collapsible'] && $args['collapsed'] ? 'display: none;' : ''; ?>">
            
            <?php if ($args['show_search'] && count($display_terms) > 5) : ?>
                <div class="filter-group__search">
                    <input type="text" 
                           class="filter-group__search-input" 
                           placeholder="<?php esc_attr_e('Поиск...', 'severcon'); ?>"
                           data-filter-group="<?php echo esc_attr($group_id); ?>">
                    <i class="fas fa-search filter-group__search-icon"></i>
                </div>
            <?php endif; ?>
            
            <div class="filter-group__items">
                <?php 
                switch ($args['type']) {
                    case 'checkbox':
                        echo severcon_filter_checkboxes($display_terms, $args);
                        break;
                        
                    case 'radio':
                        echo severcon_filter_radios($display_terms, $args);
                        break;
                        
                    case 'select':
                        echo severcon_filter_select($display_terms, $args);
                        break;
                        
                    case 'color':
                        echo severcon_filter_colors($display_terms, $args);
                        break;
                        
                    case 'image':
                        echo severcon_filter_images($display_terms, $args);
                        break;
                        
                    default:
                        echo severcon_filter_checkboxes($display_terms, $args);
                }
                ?>
            </div>
            
            <?php if ($has_more) : ?>
                <div class="filter-group__more">
                    <button type="button" class="filter-group__show-more" 
                            data-group="<?php echo esc_attr($group_id); ?>"
                            data-limit="<?php echo esc_attr($args['limit']); ?>"
                            data-total="<?php echo count($all_terms); ?>">
                        <span class="filter-group__show-more-text">
                            <?php printf(__('Показать еще %d', 'severcon'), count($all_terms) - $args['limit']); ?>
                        </span>
                        <span class="filter-group__show-less-text" style="display: none;">
                            <?php _e('Скрыть', 'severcon'); ?>
                        </span>
                        <i class="fas fa-chevron-down filter-group__more-icon"></i>
                    </button>
                </div>
                
                <!-- Скрытые термины -->
                <div class="filter-group__hidden" style="display: none;">
                    <?php 
                    $hidden_terms = array_slice($all_terms, $args['limit']);
                    switch ($args['type']) {
                        case 'checkbox':
                            echo severcon_filter_checkboxes($hidden_terms, $args);
                            break;
                        default:
                            echo severcon_filter_checkboxes($hidden_terms, $args);
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($args['selected'])) : ?>
            <div class="filter-group__selected">
                <span class="filter-group__selected-label">
                    <?php _e('Выбрано:', 'severcon'); ?>
                </span>
                <span class="filter-group__selected-count">
                    <?php echo count($args['selected']); ?>
                </span>
                <button type="button" class="filter-group__clear" 
                        data-attribute="<?php echo esc_attr($args['attribute']); ?>">
                    <?php _e('Сбросить', 'severcon'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Чекбоксы для фильтрации
 */
function severcon_filter_checkboxes($terms, $args) {
    if (empty($terms)) {
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="filter-checkboxes">
        <?php foreach ($terms as $term) : 
            $term_id = is_object($term) ? $term->term_id : $term['term_id'];
            $term_slug = is_object($term) ? $term->slug : $term['slug'];
            $term_name = is_object($term) ? $term->name : $term['name'];
            $term_count = is_object($term) ? $term->count : ($term['count'] ?? 0);
            
            $input_id = 'filter-' . $args['attribute'] . '-' . $term_slug;
            $is_selected = in_array($term_slug, (array)$args['selected']);
        ?>
            <div class="filter-checkbox-item <?php echo $is_selected ? 'filter-checkbox-item--selected' : ''; ?>">
                <input type="checkbox" 
                       id="<?php echo esc_attr($input_id); ?>"
                       class="filter-checkbox"
                       name="<?php echo esc_attr($args['attribute']); ?>[]"
                       value="<?php echo esc_attr($term_slug); ?>"
                       <?php checked($is_selected); ?>
                       data-term-id="<?php echo esc_attr($term_id); ?>"
                       data-term-slug="<?php echo esc_attr($term_slug); ?>">
                
                <label for="<?php echo esc_attr($input_id); ?>" class="filter-checkbox-label">
                    <span class="filter-checkbox-custom"></span>
                    <span class="filter-checkbox-text"><?php echo esc_html($term_name); ?></span>
                    
                    <?php if ($args['show_count']) : ?>
                        <span class="filter-checkbox-count">
                            (<?php echo intval($term_count); ?>)
                        </span>
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Радиокнопки для фильтрации
 */
function severcon_filter_radios($terms, $args) {
    if (empty($terms)) {
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="filter-radios">
        <div class="filter-radio-item filter-radio-item--all">
            <input type="radio" 
                   id="filter-<?php echo esc_attr($args['attribute']); ?>-all"
                   class="filter-radio"
                   name="<?php echo esc_attr($args['attribute']); ?>"
                   value=""
                   <?php checked(empty($args['selected'])); ?>>
            
            <label for="filter-<?php echo esc_attr($args['attribute']); ?>-all" class="filter-radio-label">
                <span class="filter-radio-custom"></span>
                <span class="filter-radio-text"><?php _e('Все', 'severcon'); ?></span>
            </label>
        </div>
        
        <?php foreach ($terms as $term) : 
            $term_slug = is_object($term) ? $term->slug : $term['slug'];
            $term_name = is_object($term) ? $term->name : $term['name'];
            $term_count = is_object($term) ? $term->count : ($term['count'] ?? 0);
            
            $input_id = 'filter-' . $args['attribute'] . '-' . $term_slug;
            $is_selected = in_array($term_slug, (array)$args['selected']);
        ?>
            <div class="filter-radio-item <?php echo $is_selected ? 'filter-radio-item--selected' : ''; ?>">
                <input type="radio" 
                       id="<?php echo esc_attr($input_id); ?>"
                       class="filter-radio"
                       name="<?php echo esc_attr($args['attribute']); ?>"
                       value="<?php echo esc_attr($term_slug); ?>"
                       <?php checked($is_selected); ?>>
                
                <label for="<?php echo esc_attr($input_id); ?>" class="filter-radio-label">
                    <span class="filter-radio-custom"></span>
                    <span class="filter-radio-text"><?php echo esc_html($term_name); ?></span>
                    
                    <?php if ($args['show_count']) : ?>
                        <span class="filter-radio-count">
                            (<?php echo intval($term_count); ?>)
                        </span>
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Выпадающий список для фильтрации
 */
function severcon_filter_select($terms, $args) {
    if (empty($terms)) {
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="filter-select">
        <select class="filter-select__dropdown" 
                name="<?php echo esc_attr($args['attribute']); ?>"
                data-attribute="<?php echo esc_attr($args['attribute']); ?>">
            <option value=""><?php _e('Все', 'severcon'); ?></option>
            
            <?php foreach ($terms as $term) : 
                $term_slug = is_object($term) ? $term->slug : $term['slug'];
                $term_name = is_object($term) ? $term->name : $term['name'];
                $term_count = is_object($term) ? $term->count : ($term['count'] ?? 0);
                $is_selected = in_array($term_slug, (array)$args['selected']);
            ?>
                <option value="<?php echo esc_attr($term_slug); ?>" <?php selected($is_selected); ?>>
                    <?php echo esc_html($term_name); ?>
                    <?php if ($args['show_count']) : ?>
                        (<?php echo intval($term_count); ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <div class="filter-select__arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Цветовые фильтры
 */
function severcon_filter_colors($terms, $args) {
    if (empty($terms)) {
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="filter-colors">
        <?php foreach ($terms as $term) : 
            $term_id = is_object($term) ? $term->term_id : $term['term_id'];
            $term_slug = is_object($term) ? $term->slug : $term['slug'];
            $term_name = is_object($term) ? $term->name : $term['name'];
            
            // Получаем цвет из метаполя термина
            $color = get_term_meta($term_id, 'color', true);
            if (!$color) {
                // Генерируем цвет на основе slug если не задан
                $color = '#' . substr(md5($term_slug), 0, 6);
            }
            
            $is_selected = in_array($term_slug, (array)$args['selected']);
            $input_id = 'filter-color-' . $term_slug;
        ?>
            <div class="filter-color-item <?php echo $is_selected ? 'filter-color-item--selected' : ''; ?>"
                 title="<?php echo esc_attr($term_name); ?>">
                <input type="checkbox" 
                       id="<?php echo esc_attr($input_id); ?>"
                       class="filter-color-input"
                       name="<?php echo esc_attr($args['attribute']); ?>[]"
                       value="<?php echo esc_attr($term_slug); ?>"
                       <?php checked($is_selected); ?>
                       data-term-slug="<?php echo esc_attr($term_slug); ?>">
                
                <label for="<?php echo esc_attr($input_id); ?>" class="filter-color-label">
                    <span class="filter-color-swatch" style="background-color: <?php echo esc_attr($color); ?>"></span>
                    <span class="filter-color-name"><?php echo esc_html($term_name); ?></span>
                    
                    <?php if ($args['show_count'] && isset($term->count)) : ?>
                        <span class="filter-color-count">
                            (<?php echo intval($term->count); ?>)
                        </span>
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Фильтры с изображениями
 */
function severcon_filter_images($terms, $args) {
    if (empty($terms)) {
        return '';
    }
    
    ob_start();
    ?>
    
    <div class="filter-images">
        <?php foreach ($terms as $term) : 
            $term_id = is_object($term) ? $term->term_id : $term['term_id'];
            $term_slug = is_object($term) ? $term->slug : $term['slug'];
            $term_name = is_object($term) ? $term->name : $term['name'];
            
            // Получаем изображение из метаполя термина
            $image_id = get_term_meta($term_id, 'thumbnail_id', true);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
            
            $is_selected = in_array($term_slug, (array)$args['selected']);
            $input_id = 'filter-image-' . $term_slug;
        ?>
            <div class="filter-image-item <?php echo $is_selected ? 'filter-image-item--selected' : ''; ?>"
                 title="<?php echo esc_attr($term_name); ?>">
                <input type="checkbox" 
                       id="<?php echo esc_attr($input_id); ?>"
                       class="filter-image-input"
                       name="<?php echo esc_attr($args['attribute']); ?>[]"
                       value="<?php echo esc_attr($term_slug); ?>"
                       <?php checked($is_selected); ?>
                       data-term-slug="<?php echo esc_attr($term_slug); ?>">
                
                <label for="<?php echo esc_attr($input_id); ?>" class="filter-image-label">
                    <?php if ($image_url) : ?>
                        <span class="filter-image-thumb">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="<?php echo esc_attr($term_name); ?>">
                        </span>
                    <?php else : ?>
                        <span class="filter-image-placeholder">
                            <i class="fas fa-image"></i>
                        </span>
                    <?php endif; ?>
                    
                    <span class="filter-image-name"><?php echo esc_html($term_name); ?></span>
                    
                    <?php if ($args['show_count'] && isset($term->count)) : ?>
                        <span class="filter-image-count">
                            (<?php echo intval($term->count); ?>)
                        </span>
                    <?php endif; ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

/**
 * Шорткод для вывода группы фильтров
 */
function severcon_filter_group_shortcode($atts) {
    $atts = shortcode_atts([
        'attribute'   => '',
        'title'       => '',
        'type'        => 'checkbox',
        'collapsible' => true,
        'collapsed'   => false,
        'show_count'  => true,
        'show_search' => false,
        'limit'       => 0,
        'layout'      => 'vertical',
        'class'       => '',
    ], $atts, 'severcon_filter_group');
    
    // Преобразуем строковые значения в boolean
    $bool_fields = ['collapsible', 'collapsed', 'show_count', 'show_search'];
    foreach ($bool_fields as $field) {
        if (isset($atts[$field])) {
            $atts[$field] = filter_var($atts[$field], FILTER_VALIDATE_BOOLEAN);
        }
    }
    
    return severcon_filter_group($atts);
}
add_shortcode('severcon_filter_group', 'severcon_filter_group_shortcode');