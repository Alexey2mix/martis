<?php
/**
 * Шаблон: Шапка сайта
 */

$phone_number = get_theme_mod('phone_number', '+7 (495) 252-08-28');
$logo = has_custom_logo() ? get_custom_logo() : '<a href="' . esc_url(home_url('/')) . '" class="site-logo">' . get_bloginfo('name') . '</a>';
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">
    
    <!-- Верхняя панель -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar__inner">
                <div class="top-bar__contacts">
                    <?php if ($phone_number) : ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_number)); ?>" 
                           class="top-bar__phone">
                            <i class="fas fa-phone"></i>
                            <span><?php echo esc_html($phone_number); ?></span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($email = get_theme_mod('email_address')) : ?>
                        <a href="mailto:<?php echo esc_attr($email); ?>" class="top-bar__email">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo esc_html($email); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="top-bar__actions">
                    <button class="top-bar__search-btn" aria-label="<?php esc_attr_e('Поиск', 'severcon'); ?>">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <?php if (function_exists('wc_get_cart_url')) : ?>
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="top-bar__cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="top-bar__cart-count">
                                <?php echo WC()->cart->get_cart_contents_count(); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Основная шапка -->
    <header id="mainHeader" class="site-header">
        <div class="container">
            <div class="site-header__inner">
                
                <!-- Логотип -->
                <div class="site-header__logo">
                    <?php echo $logo; ?>
                </div>
                
                <!-- Основное меню -->
                <nav class="site-header__nav main-navigation" aria-label="<?php esc_attr_e('Основная навигация', 'severcon'); ?>">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'menu_class'     => 'main-menu',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'depth'          => 3,
                    ]);
                    ?>
                </nav>
                
                <!-- Действия в шапке -->
                <div class="site-header__actions">
                    <button id="requestBtn" class="site-header__request-btn btn btn-primary">
                        <i class="fas fa-envelope"></i>
                        <span><?php _e('Запросить цену', 'severcon'); ?></span>
                    </button>
                    
                    <!-- Кнопка мобильного меню -->
                    <button id="mobileToggle" class="site-header__mobile-toggle" aria-label="<?php esc_attr_e('Открыть меню', 'severcon'); ?>">
                        <span class="mobile-toggle__line"></span>
                        <span class="mobile-toggle__line"></span>
                        <span class="mobile-toggle__line"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Мобильное меню -->
    <div id="mobileMenu" class="mobile-menu">
        <div class="mobile-menu__header">
            <button id="mobileMenuClose" class="mobile-menu__close" aria-label="<?php esc_attr_e('Закрыть меню', 'severcon'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mobile-menu__body">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'mobile-menu__list',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 2,
            ]);
            ?>
            
            <div class="mobile-menu__contacts">
                <?php if ($phone_number) : ?>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_number)); ?>" 
                       class="mobile-menu__phone">
                        <i class="fas fa-phone"></i>
                        <?php echo esc_html($phone_number); ?>
                    </a>
                <?php endif; ?>
                
                <button class="mobile-menu__request mobile-request">
                    <i class="fas fa-envelope"></i>
                    <?php _e('Запросить цену', 'severcon'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Оверлей поиска -->
    <div id="searchOverlay" class="search-overlay">
        <div class="search-overlay__inner">
            <button id="searchClose" class="search-overlay__close" aria-label="<?php esc_attr_e('Закрыть поиск', 'severcon'); ?>">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="search-overlay__content">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           class="search-form__input" 
                           placeholder="<?php esc_attr_e('Что вы ищете?', 'severcon'); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s"
                           autocomplete="off">
                    
                    <button type="submit" class="search-form__submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <?php 
                // Популярные поисковые запросы
                $popular_searches = get_theme_mod('popular_searches', []);
                if (!empty($popular_searches)) : 
                ?>
                    <div class="search-overlay__popular">
                        <h4 class="search-overlay__popular-title">
                            <?php _e('Популярные запросы:', 'severcon'); ?>
                        </h4>
                        <div class="search-overlay__popular-tags">
                            <?php foreach ($popular_searches as $search) : ?>
                                <a href="<?php echo esc_url(home_url('/?s=' . urlencode($search))); ?>" 
                                   class="search-overlay__popular-tag">
                                    <?php echo esc_html($search); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Основной контент -->
    <main class="site-main">