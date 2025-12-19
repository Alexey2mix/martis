<?php
/**
 * Хлебные крошки для темы Severcon
 * 
 * @package Severcon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Вывод хлебных крошек
 */
function severcon_breadcrumbs() {
	if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
		return;
	}
	
	$args = array(
		'delimiter'   => '&nbsp;&#47;&nbsp;',
		'wrap_before' => '<nav class="woocommerce-breadcrumb" itemprop="breadcrumb">',
		'wrap_after'  => '</nav>',
		'before'      => '',
		'after'       => '',
		'home'        => _x( 'Главная', 'breadcrumb', 'severcon' ),
	);
	
	woocommerce_breadcrumb( $args );
}

/**
 * Кастомные хлебные крошки (альтернативная версия)
 */
function severcon_custom_breadcrumbs() {
	// Показываем только на нужных страницах
	if ( is_front_page() || is_home() ) {
		return;
	}
	
	echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
	echo '<ol class="breadcrumbs__list">';
	
	// Домашняя страница
	echo '<li class="breadcrumbs__item">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="breadcrumbs__link">' . __( 'Главная', 'severcon' ) . '</a>';
	echo '</li>';
	
	// Проверяем тип страницы
	if ( is_category() || is_single() ) {
		$category = get_the_category();
		if ( ! empty( $category ) ) {
			echo '<li class="breadcrumbs__item">';
			echo '<a href="' . esc_url( get_category_link( $category[0]->term_id ) ) . '" class="breadcrumbs__link">' . esc_html( $category[0]->cat_name ) . '</a>';
			echo '</li>';
		}
		
		if ( is_single() ) {
			echo '<li class="breadcrumbs__item">';
			echo '<span class="breadcrumbs__current">' . get_the_title() . '</span>';
			echo '</li>';
		}
		
	} elseif ( is_page() ) {
		global $post;
		
		if ( $post->post_parent ) {
			$ancestors = get_post_ancestors( $post->ID );
			$ancestors = array_reverse( $ancestors );
			
			foreach ( $ancestors as $ancestor ) {
				echo '<li class="breadcrumbs__item">';
				echo '<a href="' . esc_url( get_permalink( $ancestor ) ) . '" class="breadcrumbs__link">' . get_the_title( $ancestor ) . '</a>';
				echo '</li>';
			}
		}
		
		echo '<li class="breadcrumbs__item">';
		echo '<span class="breadcrumbs__current">' . get_the_title() . '</span>';
		echo '</li>';
		
	} elseif ( is_search() ) {
		echo '<li class="breadcrumbs__item">';
		echo '<span class="breadcrumbs__current">' . __( 'Результаты поиска для:', 'severcon' ) . ' "' . get_search_query() . '"</span>';
		echo '</li>';
		
	} elseif ( is_404() ) {
		echo '<li class="breadcrumbs__item">';
		echo '<span class="breadcrumbs__current">' . __( 'Страница не найдена', 'severcon' ) . '</span>';
		echo '</li>';
		
	} elseif ( class_exists( 'WooCommerce' ) ) {
		// WooCommerce страницы
		if ( is_shop() ) {
			echo '<li class="breadcrumbs__item">';
			echo '<span class="breadcrumbs__current">' . __( 'Магазин', 'severcon' ) . '</span>';
			echo '</li>';
			
		} elseif ( is_product_category() || is_product_tag() ) {
			$current_term = get_queried_object();
			
			if ( $current_term ) {
				if ( is_product_category() ) {
					$ancestors = get_ancestors( $current_term->term_id, 'product_cat' );
					$ancestors = array_reverse( $ancestors );
					
					foreach ( $ancestors as $ancestor_id ) {
						$ancestor = get_term( $ancestor_id, 'product_cat' );
						if ( $ancestor && ! is_wp_error( $ancestor ) ) {
							echo '<li class="breadcrumbs__item">';
							echo '<a href="' . esc_url( get_term_link( $ancestor ) ) . '" class="breadcrumbs__link">' . esc_html( $ancestor->name ) . '</a>';
							echo '</li>';
						}
					}
				}
				
				echo '<li class="breadcrumbs__item">';
				echo '<span class="breadcrumbs__current">' . esc_html( $current_term->name ) . '</span>';
				echo '</li>';
			}
			
		} elseif ( is_product() ) {
			global $post;
			
			// Важно: проверяем, что у нас есть пост
			if ( ! $post || ! is_a( $post, 'WP_Post' ) ) {
				echo '<li class="breadcrumbs__item">';
				echo '<span class="breadcrumbs__current">' . __( 'Товар', 'severcon' ) . '</span>';
				echo '</li>';
				echo '</ol></nav>';
				return;
			}
			
			$product = wc_get_product( $post->ID );
			
			// Магазин
			echo '<li class="breadcrumbs__item">';
			echo '<a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '" class="breadcrumbs__link">' . __( 'Магазин', 'severcon' ) . '</a>';
			echo '</li>';
			
			// Категории товара
			if ( $product && is_a( $product, 'WC_Product' ) ) {
				$terms = wp_get_post_terms( $product->get_id(), 'product_cat' );
				
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$main_term = $terms[0];
					$ancestors = get_ancestors( $main_term->term_id, 'product_cat' );
					$ancestors = array_reverse( $ancestors );
					
					foreach ( $ancestors as $ancestor_id ) {
						$ancestor = get_term( $ancestor_id, 'product_cat' );
						if ( $ancestor && ! is_wp_error( $ancestor ) ) {
							echo '<li class="breadcrumbs__item">';
							echo '<a href="' . esc_url( get_term_link( $ancestor ) ) . '" class="breadcrumbs__link">' . esc_html( $ancestor->name ) . '</a>';
							echo '</li>';
						}
					}
					
					echo '<li class="breadcrumbs__item">';
					echo '<a href="' . esc_url( get_term_link( $main_term ) ) . '" class="breadcrumbs__link">' . esc_html( $main_term->name ) . '</a>';
					echo '</li>';
				}
			}
			
			// Название товара
			echo '<li class="breadcrumbs__item">';
			echo '<span class="breadcrumbs__current">' . get_the_title( $post ) . '</span>';
			echo '</li>';
		}
	}
	
	echo '</ol>';
	echo '</nav>';
}

/**
 * Простая версия хлебных крошек
 */
function severcon_simple_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	
	echo '<div class="simple-breadcrumbs">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . __( 'Главная', 'severcon' ) . '</a>';
	echo '<span class="sep"> / </span>';
	
	if ( is_category() || is_single() ) {
		$category = get_the_category();
		if ( ! empty( $category ) ) {
			echo '<a href="' . esc_url( get_category_link( $category[0]->term_id ) ) . '">' . esc_html( $category[0]->cat_name ) . '</a>';
			echo '<span class="sep"> / </span>';
		}
		
		if ( is_single() ) {
			echo '<span>' . get_the_title() . '</span>';
		}
		
	} elseif ( is_page() ) {
		echo '<span>' . get_the_title() . '</span>';
		
	} elseif ( is_search() ) {
		echo '<span>' . __( 'Поиск', 'severcon' ) . '</span>';
		
	} elseif ( is_404() ) {
		echo '<span>' . __( '404', 'severcon' ) . '</span>';
		
	} elseif ( class_exists( 'WooCommerce' ) ) {
		if ( is_shop() ) {
			echo '<span>' . __( 'Магазин', 'severcon' ) . '</span>';
		} elseif ( is_product() ) {
			echo '<a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">' . __( 'Магазин', 'severcon' ) . '</a>';
			echo '<span class="sep"> / </span>';
			echo '<span>' . get_the_title() . '</span>';
		}
	}
	
	echo '</div>';
}
