</main>

<!-- Модальное окно быстрого просмотра -->
<div id="quickViewOverlay" class="modal-overlay" style="display: none;">
    <div class="modal-container quick-view-container">
        <button class="modal-close" id="quickViewClose">&times;</button>
        <div class="quick-view-content">
            <!-- Загружается через AJAX -->
        </div>
    </div>
</div>

<!-- Основной футер -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Блок 1 - Логотип и информация -->
            <div class="footer-column footer-logo-column">
                <?php if (is_active_sidebar('footer-logo')) : ?>
                    <?php dynamic_sidebar('footer-logo'); ?>
                <?php else : ?>
                    <div class="footer-logo">
                        <a href="<?php echo home_url(); ?>" class="logo">
                            <span class="logo-main">SEVERCON</span>
                            <span class="logo-subtitle">Официальный дистрибьютор</span>
                        </a>
                    </div>
                    <div class="footer-copyright">
                        &copy; <?php echo date('Y'); ?> СЕВЕРКОН. Все права защищены.
                    </div>
                    <div class="footer-privacy">
                        <a href="/privacy-policy">Политика конфиденциальности</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Блок 2 - Каталог -->
            <div class="footer-column">
                <?php if (is_active_sidebar('footer-catalog')) : ?>
                    <?php dynamic_sidebar('footer-catalog'); ?>
                <?php else : ?>
                    <h3>КАТАЛОГ</h3>
                    <?php 
                    $product_categories = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => true,
                        'number' => 6
                    ));
                    
                    if ($product_categories && !is_wp_error($product_categories)): ?>
                        <ul class="footer-menu">
                            <?php foreach ($product_categories as $category): ?>
                                <li><a href="<?php echo get_term_link($category); ?>"><?php echo $category->name; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="footer-menu">
                            <li><a href="#">Кондиционеры</a></li>
                            <li><a href="#">Системы вентиляции</a></li>
                            <li><a href="#">Отопительное оборудование</a></li>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Блок 3 - Поддержка -->
            <div class="footer-column">
                <?php if (is_active_sidebar('footer-support')) : ?>
                    <?php dynamic_sidebar('footer-support'); ?>
                <?php else : ?>
                    <h3>ПОДДЕРЖКА</h3>
                    <ul class="footer-menu">
                        <li><a href="/warranty">Гарантия и сервис</a></li>
                        <li><a href="/installation">Монтаж и установка</a></li>
                        <li><a href="/delivery">Доставка и оплата</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Блок 4 - О компании -->
            <div class="footer-column">
                <?php if (is_active_sidebar('footer-about')) : ?>
                    <?php dynamic_sidebar('footer-about'); ?>
                <?php else : ?>
                    <h3>О КОМПАНИИ</h3>
                    <ul class="footer-menu">
                        <li><a href="/about">О нас</a></li>
                        <li><a href="/news">Новости</a></li>
                        <li><a href="/contacts">Контакты</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Блок 5 - Контакты -->
            <div class="footer-column footer-contact-column">
                <div class="contact-card">
                    <h3>СВЯЖИТЕСЬ С НАМИ</h3>
                    <div class="contact-info">
                        <div class="contact-phone">
                            <div class="phone-number"><?php echo esc_html(get_theme_mod('phone_number', '+7 (495) 252-08-28')); ?></div>
                        </div>
                        <div class="contact-email">
                            <a href="mailto:<?php echo esc_attr(get_theme_mod('email_address', 'info@severcon.ru')); ?>" class="email-address">
                                <?php echo esc_html(get_theme_mod('email_address', 'info@severcon.ru')); ?>
                            </a>
                        </div>
                    </div>
                    <div class="social-links">
                        <?php
                        $social_title = get_theme_mod('social_networks_title', 'Мы в соцсетях:');
                        if (!empty($social_title)) {
                            echo '<h4>' . esc_html($social_title) . '</h4>';
                        }
                        ?>
                        <div class="social-icons">
                            <?php
                            $social_data = get_theme_mod('social_networks_data', "ВКонтакте|fab fa-vk|https://vk.com/severcon\nTelegram|fab fa-telegram-plane|https://t.me/severcon");
                            $social_lines = explode("\n", $social_data);
                            
                            foreach ($social_lines as $line) {
                                $line = trim($line);
                                if (!empty($line)) {
                                    $parts = explode('|', $line);
                                    $social_name = isset($parts[0]) ? trim($parts[0]) : '';
                                    $social_icon = isset($parts[1]) ? trim($parts[1]) : '';
                                    $social_url = isset($parts[2]) ? trim($parts[2]) : '#';
                                    
                                    if (!empty($social_name) && !empty($social_icon)) {
                                        if ($social_url !== '#' && !preg_match('/^(https?:\/\/|\/)/', $social_url)) {
                                            $social_url = '#' . $social_url;
                                        }
                                        
                                        echo '<a href="' . esc_url($social_url) . '" class="social-link" aria-label="' . esc_attr($social_name) . '">';
                                        echo '<i class="' . esc_attr($social_icon) . '"></i>';
                                        echo '</a>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>