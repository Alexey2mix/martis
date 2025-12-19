<?php
/**
 * Шапка сайта для темы Severcon
 *
 * @package Severcon
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php
    /**
     * SEO: Запрещаем индексацию в нерабочих средах
     */
    if ( defined( 'WP_ENV' ) && in_array( WP_ENV, [ 'staging', 'development' ], true ) ) {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
    ?>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php echo severcon_get_body_attributes(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    
    <?php
    /**
     * Хук для контента перед шапкой
     */
    do_action( 'severcon_before_header' );
    ?>
    
    <header id="masthead" class="site-header" role="banner">
        <div class="container">
            <div class="site-header__inner">
                
                <?php
                /**
                 * Хук для контента в начале шапки
                 */
                do_action( 'severcon_header_start' );
                ?>
                
                <!-- Логотип -->
                <div class="site-branding">
                    <?php if ( has_custom_logo() ) : ?>
                        <div class="site-logo">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <div class="site-title-wrapper">
                            <h1 class="site-title">
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                                    <?php bloginfo( 'name' ); ?>
                                </a>
                            </h1>
                            <?php
                            $description = get_bloginfo( 'description', 'display' );
                            if ( $description || is_customize_preview() ) :
                                ?>
                                <p class="site-description"><?php echo $description; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div><!-- .site-branding -->
                
                <!-- Основная навигация -->
                <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Основное меню', 'severcon' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location'  => 'primary',
                        'menu_class'      => 'primary-menu',
                        'container'       => false,
                        'fallback_cb'     => false,
                        'depth'           => 3,
                    ] );
                    ?>
                </nav><!-- #site-navigation -->
                
                <!-- Вспомогательные элементы -->
                <div class="header-actions">
                    
                    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                        <!-- Корзина WooCommerce -->
                        <div class="header-cart">
                            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-contents" aria-label="<?php esc_attr_e( 'Корзина', 'severcon' ); ?>">
                                <span class="cart-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
                                        <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Поиск -->
                    <div class="header-search">
                        <button class="search-toggle" aria-label="<?php esc_attr_e( 'Открыть поиск', 'severcon' ); ?>" aria-expanded="false">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                        <div class="search-form-wrapper">
                            <?php get_search_form(); ?>
                        </div>
                    </div>
                    
                    <!-- Мобильное меню -->
                    <button class="mobile-menu-toggle" aria-label="<?php esc_attr_e( 'Открыть меню', 'severcon' ); ?>" aria-expanded="false" aria-controls="site-navigation">
                        <span class="toggle-bar"></span>
                        <span class="toggle-bar"></span>
                        <span class="toggle-bar"></span>
                    </button>
                    
                </div><!-- .header-actions -->
                
                <?php
                /**
                 * Хук для контента в конце шапки
                 */
                do_action( 'severcon_header_end' );
                ?>
                
            </div><!-- .site-header__inner -->
        </div><!-- .container -->
        
        <?php
        /**
         * Выпадающая форма поиска
         */
        ?>
        <div class="header-search-dropdown">
            <div class="container">
                <?php
                get_search_form( [
                    'aria_label' => __( 'Поиск по сайту', 'severcon' ),
                ] );
                ?>
                <button class="search-close" aria-label="<?php esc_attr_e( 'Закрыть поиск', 'severcon' ); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div><!-- .header-search-dropdown -->
        
    </header><!-- #masthead -->
    
    <?php
    /**
     * Хук для контента после шапки
     */
    do_action( 'severcon_after_header' );
    ?>
    
    <!-- Хлебные крошки -->
    <?php if ( ! is_front_page() && function_exists( 'severcon_breadcrumbs' ) ) : ?>
        <div class="breadcrumbs-wrapper">
            <div class="container">
                <?php severcon_breadcrumbs(); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Основной контент -->
    <div id="content" class="site-content">
        <div class="container">
            
            <?php
            /**
             * Хук для контента перед основным
             */
            do_action( 'severcon_before_main_content' );
            ?>
            
            <main id="main" class="site-main" role="main">
