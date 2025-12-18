<?php
/**
 * Шаблон: Подвал сайта
 */

$footer_logo = get_theme_mod('footer_logo') ? wp_get_attachment_image(get_theme_mod('footer_logo'), 'full') : '';
$copyright = get_theme_mod('footer_copyright', sprintf(__('© %s Все права защищены.', 'severcon'), date('Y')));
?>

        </main><!-- .site-main -->
        
        <!-- Подвал сайта -->
        <footer class="site-footer">
            <div class="site-footer__top">
                <div class="container">
                    <div class="site-footer__grid">
                        
                        <!-- Колонка 1: Логотип и описание -->
                        <div class="site-footer__col site-footer__col--logo">
                            <?php if ($footer_logo) : ?>
                                <div class="site-footer__logo">
                                    <?php echo $footer_logo; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (is_active_sidebar('footer-logo')) : ?>
                                <div class="site-footer__widget">
                                    <?php dynamic_sidebar('footer-logo'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php 
                            $footer_description = get_theme_mod('footer_description', '');
                            if ($footer_description) : 
                            ?>
                                <div class="site-footer__description">
                                    <?php echo wp_kses_post($footer_description); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Социальные сети -->
                            <?php 
                            $social_links = [];
                            $social_platforms = [
                                'facebook'  => ['icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
                                'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
                                'youtube'   => ['icon' => 'fab fa-youtube', 'label' => 'YouTube'],
                                'telegram'  => ['icon' => 'fab fa-telegram-plane', 'label' => 'Telegram'],
                                'vkontakte' => ['icon' => 'fab fa-vk', 'label' => 'ВКонтакте'],
                            ];
                            
                            foreach ($social_platforms as $platform => $data) {
                                $url = get_theme_mod('social_' . $platform, '');
                                if ($url) {
                                    $social_links[$platform] = [
                                        'url'   => $url,
                                        'icon'  => $data['icon'],
                                        'label' => $data['label']
                                    ];
                                }
                            }
                            
                            if (!empty($social_links)) : 
                            ?>
                                <div class="site-footer__social">
                                    <h4 class="site-footer__social-title">
                                        <?php _e('Мы в соцсетях:', 'severcon'); ?>
                                    </h4>
                                    <div class="site-footer__social-links">
                                        <?php foreach ($social_links as $platform => $link) : ?>
                                            <a href="<?php echo esc_url($link['url']); ?>" 
                                               class="site-footer__social-link site-footer__social-link--<?php echo esc_attr($platform); ?>"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               aria-label="<?php echo esc_attr($link['label']); ?>">
                                                <i class="<?php echo esc_attr($link['icon']); ?>"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Колонка 2: Каталог -->
                        <div class="site-footer__col site-footer__col--catalog">
                            <?php if (is_active_sidebar('footer-catalog')) : ?>
                                <?php dynamic_sidebar('footer-catalog'); ?>
                            <?php else : ?>
                                <h3 class="site-footer__title"><?php _e('Каталог', 'severcon'); ?></h3>
                                <?php
                                wp_nav_menu([
                                    'theme_location' => 'footer',
                                    'menu_class'     => 'site-footer__menu',
                                    'container'      => false,
                                    'fallback_cb'    => 'severcon_footer_menu_fallback',
                                    'depth'          => 1,
                                ]);
                                
                                function severcon_footer_menu_fallback() {
                                    $categories = get_categories([
                                        'taxonomy'   => 'product_cat',
                                        'hide_empty' => true,
                                        'number'     => 6,
                                    ]);
                                    
                                    if ($categories) {
                                        echo '<ul class="site-footer__menu">';
                                        foreach ($categories as $category) {
                                            echo '<li class="site-footer__menu-item">';
                                            echo '<a href="' . get_term_link($category) . '" class="site-footer__menu-link">';
                                            echo esc_html($category->name);
                                            echo '</a>';
                                            echo '</li>';
                                        }
                                        echo '</ul>';
                                    }
                                }
                                ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Колонка 3: Поддержка -->
                        <div class="site-footer__col site-footer__col--support">
                            <?php if (is_active_sidebar('footer-support')) : ?>
                                <?php dynamic_sidebar('footer-support'); ?>
                            <?php else : ?>
                                <h3 class="site-footer__title"><?php _e('Поддержка', 'severcon'); ?></h3>
                                <ul class="site-footer__menu">
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/dostavka/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Доставка и оплата', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/garantiya/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Гарантия', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/vopros-otvet/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Вопрос-ответ', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/kontakty/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Контакты', 'severcon'); ?>
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Колонка 4: О компании -->
                        <div class="site-footer__col site-footer__col--about">
                            <?php if (is_active_sidebar('footer-about')) : ?>
                                <?php dynamic_sidebar('footer-about'); ?>
                            <?php else : ?>
                                <h3 class="site-footer__title"><?php _e('О компании', 'severcon'); ?></h3>
                                <ul class="site-footer__menu">
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/o-kompanii/')); ?>" class="site-footer__menu-link">
                                            <?php _e('О нас', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/novosti/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Новости', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/partnery/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Партнерам', 'severcon'); ?>
                                        </a>
                                    </li>
                                    <li class="site-footer__menu-item">
                                        <a href="<?php echo esc_url(home_url('/vacancies/')); ?>" class="site-footer__menu-link">
                                            <?php _e('Вакансии', 'severcon'); ?>
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Колонка 5: Контакты -->
                        <div class="site-footer__col site-footer__col--contacts">
                            <?php if (is_active_sidebar('footer-contacts')) : ?>
                                <?php dynamic_sidebar('footer-contacts'); ?>
                            <?php else : ?>
                                <h3 class="site-footer__title"><?php _e('Контакты', 'severcon'); ?></h3>
                                
                                <div class="site-footer__contacts">
                                    <?php if ($phone = get_theme_mod('phone_number')) : ?>
                                        <div class="site-footer__contact site-footer__contact--phone">
                                            <i class="fas fa-phone"></i>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>">
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($email = get_theme_mod('email_address')) : ?>
                                        <div class="site-footer__contact site-footer__contact--email">
                                            <i class="fas fa-envelope"></i>
                                            <a href="mailto:<?php echo esc_attr($email); ?>">
                                                <?php echo esc_html($email); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($address = get_theme_mod('company_address')) : ?>
                                        <div class="site-footer__contact site-footer__contact--address">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo esc_html($address); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($work_hours = get_theme_mod('work_hours')) : ?>
                                        <div class="site-footer__contact site-footer__contact--hours">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo esc_html($work_hours); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Кнопка обратного звонка -->
                                <button class="site-footer__callback-btn btn btn-secondary" data-modal-open="callback">
                                    <i class="fas fa-phone-volume"></i>
                                    <?php _e('Заказать звонок', 'severcon'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                    </div><!-- .site-footer__grid -->
                </div><!-- .container -->
            </div><!-- .site-footer__top -->
            
            <!-- Нижняя часть подвала -->
            <div class="site-footer__bottom">
                <div class="container">
                    <div class="site-footer__bottom-inner">
                        <!-- Копирайт -->
                        <div class="site-footer__copyright">
                            <?php echo wp_kses_post($copyright); ?>
                        </div>
                        
                        <!-- Дополнительные ссылки -->
                        <div class="site-footer__links">
                            <a href="<?php echo esc_url(home_url('/politika-konfidentsialnosti/')); ?>" 
                               class="site-footer__link">
                                <?php _e('Политика конфиденциальности', 'severcon'); ?>
                            </a>
                            
                            <a href="<?php echo esc_url(home_url('/polzovatelskoe-soglashenie/')); ?>" 
                               class="site-footer__link">
                                <?php _e('Пользовательское соглашение', 'severcon'); ?>
                            </a>
                            
                            <?php if (function_exists('the_privacy_policy_link')) : ?>
                                <?php the_privacy_policy_link('', '<span class="site-footer__sep">|</span>'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </footer><!-- .site-footer -->
        
        <!-- Модальные окна -->
        
        <!-- Форма запроса цены -->
        <div id="requestOverlay" class="modal-overlay">
            <div class="modal-container">
                <div class="modal-content">
                    <button id="requestClose" class="modal-close" aria-label="<?php esc_attr_e('Закрыть', 'severcon'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="modal-header">
                        <h2 class="modal-title"><?php _e('Запросить цену', 'severcon'); ?></h2>
                    </div>
                    
                    <div class="modal-body">
                        <?php echo do_shortcode('[severcon_request_form]'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Форма обратного звонка -->
        <div id="callbackOverlay" class="modal-overlay">
            <div class="modal-container">
                <div class="modal-content">
                    <button class="modal-close modal-close--callback" aria-label="<?php esc_attr_e('Закрыть', 'severcon'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="modal-header">
                        <h2 class="modal-title"><?php _e('Заказать звонок', 'severcon'); ?></h2>
                    </div>
                    
                    <div class="modal-body">
                        <form class="callback-form" method="post">
                            <?php wp_nonce_field('severcon_callback', 'severcon_callback_nonce'); ?>
                            
                            <div class="form-group">
                                <label for="callback_name"><?php _e('Ваше имя *', 'severcon'); ?></label>
                                <input type="text" 
                                       id="callback_name" 
                                       name="name" 
                                       class="form-control" 
                                       required 
                                       minlength="2">
                            </div>
                            
                            <div class="form-group">
                                <label for="callback_phone"><?php _e('Телефон *', 'severcon'); ?></label>
                                <input type="tel" 
                                       id="callback_phone" 
                                       name="phone" 
                                       class="form-control" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="callback_time"><?php _e('Удобное время для звонка', 'severcon'); ?></label>
                                <select id="callback_time" name="time" class="form-control">
                                    <option value=""><?php _e('Не важно', 'severcon'); ?></option>
                                    <option value="9-12">9:00 - 12:00</option>
                                    <option value="12-15">12:00 - 15:00</option>
                                    <option value="15-18">15:00 - 18:00</option>
                                    <option value="18-20">18:00 - 20:00</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="callback_message"><?php _e('Сообщение', 'severcon'); ?></label>
                                <textarea id="callback_message" 
                                          name="message" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="<?php esc_attr_e('Ваш вопрос или комментарий...', 'severcon'); ?>"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <?php _e('Заказать звонок', 'severcon'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Быстрый просмотр товара -->
        <div id="quickViewOverlay" class="modal-overlay modal-overlay--quickview">
            <div class="modal-container modal-container--quickview">
                <div class="modal-content modal-content--quickview">
                    <button id="quickViewClose" class="modal-close modal-close--quickview" aria-label="<?php esc_attr_e('Закрыть', 'severcon'); ?>">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="quick-view-content">
                        <!-- Контент загружается через AJAX -->
                    </div>
                </div>
            </div>
        </div>
        
        <?php wp_footer(); ?>
        
        <!-- Яндекс.Метрика и другие счетчики -->
        <?php if ($yandex_metrika = get_theme_mod('yandex_metrika')) : ?>
            <!-- Yandex.Metrika counter -->
            <?php echo $yandex_metrika; ?>
            <!-- /Yandex.Metrika counter -->
        <?php endif; ?>
        
        <?php if ($google_analytics = get_theme_mod('google_analytics')) : ?>
            <!-- Google Analytics -->
            <?php echo $google_analytics; ?>
            <!-- End Google Analytics -->
        <?php endif; ?>
        
    </div><!-- .site-wrapper -->
</body>
</html>