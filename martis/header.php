<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Верхняя панель с брендами -->
<div class="top-brands-bar">
    <div class="container">
        <div class="brands-content">
            <div class="brands-label">
                <?php echo esc_html(get_theme_mod('distributor_text', 'Официальный дистрибьютор климатической техники')); ?>
            </div>
            <div class="brands-logos">
                <?php
                $brands_data = get_theme_mod('brands_data', "ENERGOLUX|https://energolux.com\nFERRUM|https://ferrum.com\nTITAN|/brand/titan\nELECTRA|#");
                $brands_lines = explode("\n", $brands_data);
                
                foreach ($brands_lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $parts = explode('|', $line);
                        $brand_name = isset($parts[0]) ? trim($parts[0]) : '';
                        $brand_url = isset($parts[1]) ? trim($parts[1]) : '#';
                        
                        if (!empty($brand_name)) {
                            if ($brand_url !== '#' && !preg_match('/^(https?:\/\/|\/)/', $brand_url)) {
                                $brand_url = '#' . $brand_url;
                            }
                            
                            echo '<a href="' . esc_url($brand_url) . '" class="brand-logo">' . esc_html($brand_name) . '</a>';
                        }
                    }
                }
                ?>
            </div>
            <div class="top-actions">
                <a href="mailto:<?php echo esc_attr(get_theme_mod('email_address', 'info@severcon.ru')); ?>" class="email-link">
                    <?php echo esc_html(get_theme_mod('email_address', 'info@severcon.ru')); ?>
                </a>
                <button class="search-toggle" id="searchToggle">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Основная шапка -->
<header class="main-header" id="mainHeader" 
        data-main-logo="<?php echo esc_url(get_theme_mod('main_logo')); ?>"
        data-sticky-logo="<?php echo esc_url(get_theme_mod('sticky_logo')); ?>">
    <div class="container">
        <div class="header-main">
            <!-- Логотип -->
            <div class="logo-section">
                <a href="<?php echo home_url(); ?>" class="logo">
                    <?php
                    $main_logo = get_theme_mod('main_logo');
                    $logo_text = get_theme_mod('logo_text', 'SEVERCON');
                    $logo_subtitle = get_theme_mod('logo_subtitle', 'Официальный дистрибьютор');
                    
                    if ($main_logo) {
                        echo '<img src="' . esc_url($main_logo) . '" alt="' . esc_attr($logo_text) . '" class="logo-image">';
                    } else {
                        echo '<span class="logo-main">' . esc_html($logo_text) . '</span>';
                        if ($logo_subtitle) {
                            echo '<span class="logo-subtitle">' . esc_html($logo_subtitle) . '</span>';
                        }
                    }
                    ?>
                </a>
            </div>

            <!-- Основная навигация -->
            <nav class="main-navigation">
                <?php wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'nav-menu',
                    'container' => false,
                    'fallback_cb' => false
                )); ?>
            </nav>

            <!-- Контактная информация -->
            <div class="contact-section">
                <button class="btn btn-primary" id="requestBtn">Заявка на оборудование</button>
                <div class="contact-phone">
                    <span class="phone-number"><?php echo esc_html(get_theme_mod('phone_number', '+7 (495) 252-08-28')); ?></span>
                </div>
            </div>

            <!-- Мобильное меню -->
            <button class="mobile-menu-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Мобильное меню -->
<div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" id="mobileMenuClose">×</button>
    
    <?php wp_nav_menu(array(
        'theme_location' => 'primary',
        'menu_class' => 'mobile-nav-menu',
        'container' => false
    )); ?>
    
    <div class="mobile-contacts">
        <button class="btn btn-primary mobile-request">Заявка на оборудование</button>
        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', get_theme_mod('phone_number', '+7 (495) 252-08-28'))); ?>" class="mobile-phone">
            <?php echo esc_html(get_theme_mod('phone_number', '+7 (495) 252-08-28')); ?>
        </a>
        <a href="mailto:<?php echo esc_attr(get_theme_mod('email_address', 'info@severcon.ru')); ?>" class="mobile-email">
            <?php echo esc_html(get_theme_mod('email_address', 'info@severcon.ru')); ?>
        </a>
    </div>
</div>

<!-- Поисковая панель -->
<div class="modal-overlay" id="searchOverlay">
    <div class="modal-container">
        <button class="modal-close" id="searchClose">×</button>
        <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="search-form">
            <input type="search" placeholder="Введите поисковый запрос..." name="s" autocomplete="off">
            <button type="submit" class="btn btn-primary">Найти</button>
        </form>
    </div>
</div>

<!-- Форма заявки -->
<div class="modal-overlay" id="requestOverlay">
    <div class="modal-container">
        <button class="modal-close" id="requestClose">×</button>
        <h3>Заявка на оборудование</h3>
        <form class="request-form">
            <div class="form-group">
                <input type="text" placeholder="Ваше имя" required>
            </div>
            <div class="form-group">
                <input type="tel" placeholder="Телефон" required>
            </div>
            <div class="form-group">
                <input type="email" placeholder="E-mail">
            </div>
            <div class="form-group">
                <textarea placeholder="Сообщение или интересующее оборудование"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-submit">Отправить заявку</button>
        </form>
    </div>
</div>

<main class="main-content">