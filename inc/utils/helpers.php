<?php
/**
 * Вспомогательные функции темы Severcon
 *
 * @package Severcon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С ИЗОБРАЖЕНИЯМИ
// ============================================================================

/**
 * Получение URL изображения поста с указанным размером
 */
function severcon_get_post_image_url( $post_id = null, $size = 'large' ) {
	$post_id = $post_id ?: get_the_ID();
	$image_id = get_post_thumbnail_id( $post_id );
	
	if ( ! $image_id ) {
		return severcon_get_placeholder_image( $size );
	}
	
	$image_url = wp_get_attachment_image_url( $image_id, $size );
	
	return $image_url ?: severcon_get_placeholder_image( $size );
}

/**
 * Получение placeholder изображения
 */
function severcon_get_placeholder_image( $size = 'large' ) {
	$placeholders = [
		'thumbnail' => SEVERCON_THEME_URI . '/assets/images/placeholder-150x150.jpg',
		'medium'    => SEVERCON_THEME_URI . '/assets/images/placeholder-300x300.jpg',
		'large'     => SEVERCON_THEME_URI . '/assets/images/placeholder-1024x1024.jpg',
		'full'      => SEVERCON_THEME_URI . '/assets/images/placeholder-1920x1080.jpg',
	];
	
	return $placeholders[ $size ] ?? $placeholders['large'];
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С ДАТАМИ
// ============================================================================

/**
 * Форматирование даты в читаемый вид
 */
function severcon_format_date( $date_string, $format = null ) {
	if ( ! $format ) {
		$format = get_option( 'date_format' );
	}
	
	$timestamp = strtotime( $date_string );
	
	if ( ! $timestamp ) {
		return $date_string;
	}
	
	return date_i18n( $format, $timestamp );
}

/**
 * Получение времени, прошедшего с даты
 */
function severcon_time_ago( $date_string ) {
	$timestamp = strtotime( $date_string );
	$current_time = current_time( 'timestamp' );
	$time_difference = $current_time - $timestamp;
	
	$intervals = [
		'year'   => 31536000,
		'month'  => 2592000,
		'week'   => 604800,
		'day'    => 86400,
		'hour'   => 3600,
		'minute' => 60,
		'second' => 1,
	];
	
	foreach ( $intervals as $key => $value ) {
		if ( $time_difference >= $value ) {
			$time = floor( $time_difference / $value );
			
			if ( $time == 1 ) {
				return sprintf( _n( '%d %s ago', '%d %s ago', $time, 'severcon' ), $time, $key );
			} else {
				return sprintf( _n( '%d %s ago', '%d %s ago', $time, 'severcon' ), $time, $key . 's' );
			}
		}
	}
	
	return __( 'just now', 'severcon' );
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С ТЕКСТОМ
// ============================================================================

/**
 * Сокращение текста с сохранением целых слов
 */
function severcon_trim_text( $text, $length = 100, $more = '...' ) {
	if ( mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length );
		$last_space = mb_strrpos( $text, ' ' );
		
		if ( $last_space !== false ) {
			$text = mb_substr( $text, 0, $last_space );
		}
		
		$text .= $more;
	}
	
	return $text;
}

/**
 * Удаление HTML тегов и сокращение текста
 */
function severcon_get_excerpt( $text, $length = 100 ) {
	$text = strip_tags( $text );
	$text = preg_replace( '/\s+/', ' ', $text );
	
	return severcon_trim_text( $text, $length );
}

/**
 * Форматирование номера телефона для ссылки
 */
function severcon_format_phone_link( $phone ) {
	$phone = preg_replace( '/[^0-9+]/', '', $phone );
	return 'tel:' . $phone;
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С МАССИВАМИ И ОБЪЕКТАМИ
// ============================================================================

/**
 * Рекурсивное слияние массивов
 */
function severcon_array_merge_recursive( array ...$arrays ) {
	$result = array();
	
	foreach ( $arrays as $array ) {
		foreach ( $array as $key => $value ) {
			// Если ключ является целым числом, просто добавляем значение
			if ( is_int( $key ) ) {
				$result[] = $value;
			} elseif ( isset( $result[ $key ] ) && is_array( $result[ $key ] ) && is_array( $value ) ) {
				// Если оба значения массивы, рекурсивно сливаем
				$result[ $key ] = severcon_array_merge_recursive( $result[ $key ], $value );
			} else {
				// Иначе заменяем
				$result[ $key ] = $value;
			}
		}
	}
	
	return $result;
}

/**
 * Получение значения из массива/объекта по пути
 */
function severcon_get_value_by_path( $data, $path, $default = null ) {
	if ( empty( $path ) ) {
		return $data;
	}
	
	$keys = is_array( $path ) ? $path : explode( '.', $path );
	$current = $data;
	
	foreach ( $keys as $key ) {
		if ( is_array( $current ) && isset( $current[ $key ] ) ) {
			$current = $current[ $key ];
		} elseif ( is_object( $current ) && isset( $current->$key ) ) {
			$current = $current->$key;
		} elseif ( is_object( $current ) && method_exists( $current, 'get_' . $key ) ) {
			$method = 'get_' . $key;
			$current = $current->$method();
		} else {
			return $default;
		}
	}
	
	return $current;
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С КЛАССАМИ CSS
// ============================================================================

/**
 * Формирование строки классов CSS
 */
function severcon_get_css_classes( ...$classes ) {
	$classes = array_filter( array_unique( array_merge( ...$classes ) ) );
	return implode( ' ', $classes );
}

/**
 * Добавление классов к элементу
 */
function severcon_add_css_classes( $existing_classes, $new_classes ) {
	if ( is_string( $existing_classes ) ) {
		$existing_classes = explode( ' ', $existing_classes );
	}
	
	if ( is_string( $new_classes ) ) {
		$new_classes = explode( ' ', $new_classes );
	}
	
	$all_classes = array_merge( (array) $existing_classes, (array) $new_classes );
	return severcon_get_css_classes( $all_classes );
}

// ============================================================================
// ФУНКЦИИ ДЛЯ ПРОВЕРКИ УСЛОВИЙ
// ============================================================================

/**
 * Проверка на AJAX запрос
 */
function severcon_is_ajax_request() {
	return wp_doing_ajax();
}

/**
 * Проверка на REST API запрос
 */
function severcon_is_rest_request() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Проверка, является ли запрос предпросмотром
 */
function severcon_is_preview() {
	return is_preview() || isset( $_GET['preview'] );
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С URL
// ============================================================================

/**
 * Получение текущего URL
 */
function severcon_get_current_url() {
	global $wp;
	
	if ( is_admin() ) {
		return admin_url( 'admin.php?page=' . $_GET['page'] ?? '' );
	}
	
	$url = home_url( $wp->request );
	
	// Добавляем query параметры если есть
	if ( ! empty( $_GET ) ) {
		$url = add_query_arg( $_GET, $url );
	}
	
	return $url;
}

/**
 * Получение параметра из URL
 */
function severcon_get_url_param( $key, $default = '' ) {
	if ( isset( $_GET[ $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
	}
	
	if ( isset( $_POST[ $key ] ) ) {
		return sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	}
	
	return $default;
}

/**
 * Добавление/удаление параметров из URL
 */
function severcon_modify_url( $url, $params = [], $remove_params = [] ) {
	// Удаляем параметры
	foreach ( $remove_params as $param ) {
		$url = remove_query_arg( $param, $url );
	}
	
	// Добавляем/изменяем параметры
	if ( ! empty( $params ) ) {
		$url = add_query_arg( $params, $url );
	}
	
	return $url;
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С ФАЙЛАМИ
// ============================================================================

/**
 * Проверка существования файла в теме
 */
function severcon_file_exists( $relative_path ) {
	$absolute_path = SEVERCON_THEME_PATH . '/' . ltrim( $relative_path, '/' );
	return file_exists( $absolute_path );
}

/**
 * Получение содержимого файла темы
 */
function severcon_get_file_contents( $relative_path ) {
	$absolute_path = SEVERCON_THEME_PATH . '/' . ltrim( $relative_path, '/' );
	
	if ( ! file_exists( $absolute_path ) || ! is_readable( $absolute_path ) ) {
		return '';
	}
	
	return file_get_contents( $absolute_path );
}

/**
 * Получение размера файла в человекочитаемом формате
 */
function severcon_get_file_size( $path, $precision = 2 ) {
	$bytes = filesize( $path );
	$units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
	
	$bytes = max( $bytes, 0 );
	$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow = min( $pow, count( $units ) - 1 );
	
	$bytes /= pow( 1024, $pow );
	
	return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С ИКОНКАМИ
// ============================================================================

/**
 * Получение SVG иконки по названию
 */
function severcon_get_icon( $name, $class = '', $attrs = [] ) {
	$icons = [
		'cart' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
		'search' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
		'close' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
		'arrow-right' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
		'arrow-left' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
		'chevron-down' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>',
		'user' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
		'heart' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>',
		'star' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
		'check' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
	];
	
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	
	$icon = $icons[ $name ];
	
	// Добавляем классы
	if ( $class ) {
		$icon = str_replace( '<svg ', '<svg class="' . esc_attr( $class ) . '" ', $icon );
	}
	
	// Добавляем дополнительные атрибуты
	if ( ! empty( $attrs ) ) {
		foreach ( $attrs as $attr_name => $attr_value ) {
			$icon = str_replace( '<svg ', '<svg ' . $attr_name . '="' . esc_attr( $attr_value ) . '" ', $icon );
		}
	}
	
	return $icon;
}

/**
 * Вывод SVG иконки
 */
function severcon_icon( $name, $class = '', $attrs = [] ) {
	echo severcon_get_icon( $name, $class, $attrs );
}

// ============================================================================
// ФУНКЦИИ ДЛЯ ОТЛАДКИ И ЛОГИРОВАНИЯ
// ============================================================================

/**
 * Безопасный вывод данных для отладки
 */
if ( ! function_exists( 'severcon_dump' ) ) {
	function severcon_dump( $data, $return = false ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return '';
		}
		
		$output = '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ccc; margin: 10px; border-left: 4px solid #17a2b8; overflow: auto; font-size: 13px; line-height: 1.4;">';
		$output .= htmlspecialchars( print_r( $data, true ), ENT_QUOTES, 'UTF-8' );
		$output .= '</pre>';
		
		if ( $return ) {
			return $output;
		}
		
		echo $output;
	}
}

/**
 * Логирование в файл отладки
 */
function severcon_log_message( $message, $data = null, $type = 'info' ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}
	
	$log_entry = '[' . current_time( 'mysql' ) . '] [' . strtoupper( $type ) . '] ' . $message;
	
	if ( $data !== null ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			$log_entry .= ' ' . wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		} else {
			$log_entry .= ' ' . $data;
		}
	}
	
	error_log( $log_entry );
}

// ============================================================================
// ФУНКЦИИ ДЛЯ РАБОТЫ С Woocommerce (если установлен)
// ============================================================================

if ( class_exists( 'WooCommerce' ) ) {
	
	/**
	 * Получение цены товара с учетом скидки
	 */
	function severcon_get_product_price( $product_id = null ) {
		$product = wc_get_product( $product_id );
		
		if ( ! $product ) {
			return '';
		}
		
		if ( $product->is_on_sale() ) {
			$price = '<span class="price sale-price">' . wc_price( $product->get_sale_price() ) . '</span>';
			$price .= '<del class="price regular-price">' . wc_price( $product->get_regular_price() ) . '</del>';
		} else {
			$price = '<span class="price regular-price">' . wc_price( $product->get_price() ) . '</span>';
		}
		
		return $price;
	}
	
	/**
	 * Проверка, новый ли товар (добавлен менее N дней назад)
	 */
	function severcon_is_new_product( $product_id, $days = 7 ) {
		$product = wc_get_product( $product_id );
		
		if ( ! $product ) {
			return false;
		}
		
		$date_created = $product->get_date_created();
		
		if ( ! $date_created ) {
			return false;
		}
		
		$now = new DateTime();
		$created = new DateTime( $date_created->format( 'Y-m-d H:i:s' ) );
		$interval = $now->diff( $created );
		
		return $interval->days <= $days;
	}
}

// ============================================================================
// ФИЛЬТРЫ ДЛЯ РАСШИРЕНИЯ ФУНКЦИОНАЛА
// ============================================================================

/**
 * Фильтр для добавления своих иконок
 */
add_filter( 'severcon_icons', function( $icons ) {
	// Другие плагины/темы могут добавлять свои иконки
	return $icons;
} );

/**
 * Фильтр для модификации placeholder изображений
 */
add_filter( 'severcon_placeholder_images', function( $placeholders ) {
	return $placeholders;
} );
