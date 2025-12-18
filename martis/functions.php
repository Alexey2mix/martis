<?php
/**
 * Настройки Customizer для темы Severcon
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация настроек в Customizer
 */
function severcon_customize_register($wp_customize) {
    
    // ===== СЕКЦИЯ: КОНТАКТНАЯ ИНФОРМАЦИЯ =====
    $wp_customize->add_section('severcon_contacts', array(
        'title'    => __('Контакты', 'severcon'),
        'priority' => 30,
    ));
    
    // Основной телефон
    $wp_customize->add_setting('phone_number', array(
        'default'           => '+7 (495) 252-08-28',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('phone_number', array(
        'label'       => __('Основной телефон', 'severcon'),
        'section'     => 'severcon_contacts',
        'type'        => 'text',
        'description' => __('Введите телефон в формате +7 (XXX) XXX-XX-XX', 'severcon'),
    ));
    
    // Дополнительный телефон
    $wp_customize->add_setting('phone_number_secondary', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('phone_number_secondary', array(
        'label'       => __('Дополнительный телефон', 'severcon'),
        'section'     => 'severcon_contacts',
        'type'        => 'text',
    ));
    
    // Email
    $wp_customize->add_setting('email_address', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('email_address', array(
        'label'       => __('Email для связи', 'severcon'),
        'section'     => 'severcon_contacts',
        'type'        => 'email',
    ));
    
    // Адрес
    $wp_customize->add_setting('company_address', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('company_address', array(
        'label'       => __('Адрес компании', 'severcon'),
        'section'     => 'severcon_contacts',
        'type'        => 'textarea',
    ));
    
    // ===== СЕКЦИЯ: СОЦИАЛЬНЫЕ СЕТИ =====
    $wp_customize->add_section('severcon_social', array(
        'title'    => __('Социальные сети', 'severcon'),
        'priority' => 35,
    ));
    
    $social_networks = array(
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'telegram'  => 'Telegram',
        'whatsapp'  => 'WhatsApp',
        'vkontakte' => 'ВКонтакте',
    );
    
    foreach ($social_networks as $key => $name) {
        $wp_customize->add_setting('social_' . $key, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('social_' . $key, array(
            'label'       => $name,
            'section'     => 'severcon_social',
            'type'        => 'url',
        ));
    }
    
    // ===== СЕКЦИЯ: ГЛАВНАЯ СТРАНИЦА =====
    $wp_customize->add_panel('severcon_homepage', array(
        'title'    => __('Главная страница', 'severcon'),
        'priority' => 40,
    ));
    
    // Подсекция: Слайдер
    $wp_customize->add_section('severcon_slider', array(
        'title'    => __('Слайдер', 'severcon'),
        'panel'    => 'severcon_homepage',
        'priority' => 10,
    ));
    
    for ($i = 1; $i <= 3; $i++) {
        // Заголовок слайда
        $wp_customize->add_setting('slide_' . $i . '_title', array(
            'default'           => sprintf(__('Заголовок слайда %d', 'severcon'), $i),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('slide_' . $i . '_title', array(
            'label'       => sprintf(__('Заголовок слайда %d', 'severcon'), $i),
            'section'     => 'severcon_slider',
            'type'        => 'text',
        ));
        
        // Изображение слайда
        $wp_customize->add_setting('slide_' . $i . '_image', array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control(new WP_Customize_Image_Control(
            $wp_customize,
            'slide_' . $i . '_image',
            array(
                'label'       => sprintf(__('Изображение слайда %d', 'severcon'), $i),
                'section'     => 'severcon_slider',
                'settings'    => 'slide_' . $i . '_image',
            )
        ));
        
        // Текст кнопки
        $wp_customize->add_setting('slide_' . $i . '_button', array(
            'default'           => __('Подробнее', 'severcon'),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('slide_' . $i . '_button', array(
            'label'       => sprintf(__('Текст кнопки слайда %d', 'severcon'), $i),
            'section'     => 'severcon_slider',
            'type'        => 'text',
        ));
        
        // URL кнопки
        $wp_customize->add_setting('slide_' . $i . '_url', array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('slide_' . $i . '_url', array(
            'label'       => sprintf(__('Ссылка кнопки слайда %d', 'severcon'), $i),
            'section'     => 'severcon_slider',
            'type'        => 'url',
        ));
    }
    
    // Подсекция: Оборудование
    $wp_customize->add_section('severcon_equipment', array(
        'title'    => __('Блок оборудования', 'severcon'),
        'panel'    => 'severcon_homepage',
        'priority' => 20,
    ));
    
    $wp_customize->add_setting('equipment_title', array(
        'default'           => __('Наше оборудование', 'severcon'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('equipment_title', array(
        'label'       => __('Заголовок блока', 'severcon'),
        'section'     => 'severcon_equipment',
        'type'        => 'text',
    ));
    
    // Основная карточка
    $wp_customize->add_setting('equipment_main_title', array(
        'default'           => __('Климатическое оборудование', 'severcon'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('equipment_main_title', array(
        'label'       => __('Заголовок основной карточки', 'severcon'),
        'section'     => 'severcon_equipment',
        'type'        => 'text',
    ));
    
    $wp_customize->add_setting('equipment_main_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control(
        $wp_customize,
        'equipment_main_image',
        array(
            'label'       => __('Изображение основной карточки', 'severcon'),
            'section'     => 'severcon_equipment',
        )
    ));
    
    $wp_customize->add_setting('equipment_main_link', array(
        'default'           => '/catalog/',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('equipment_main_link', array(
        'label'       => __('Ссылка основной карточки', 'severcon'),
        'section'     => 'severcon_equipment',
        'type'        => 'url',
    ));
    
    // Дополнительные карточки
    for ($i = 1; $i <= 2; $i++) {
        $wp_customize->add_setting('equipment_sub' . $i . '_title', array(
            'default'           => sprintf(__('Оборудование %d', 'severcon'), $i),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('equipment_sub' . $i . '_title', array(
            'label'       => sprintf(__('Заголовок карточки %d', 'severcon'), $i),
            'section'     => 'severcon_equipment',
            'type'        => 'text',
        ));
        
        $wp_customize->add_setting('equipment_sub' . $i . '_image', array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control(new WP_Customize_Image_Control(
            $wp_customize,
            'equipment_sub' . $i . '_image',
            array(
                'label'       => sprintf(__('Изображение карточки %d', 'severcon'), $i),
                'section'     => 'severcon_equipment',
            )
        ));
        
        $wp_customize->add_setting('equipment_sub' . $i . '_link', array(
            'default'           => '/catalog/',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('equipment_sub' . $i . '_link', array(
            'label'       => sprintf(__('Ссылка карточки %d', 'severcon'), $i),
            'section'     => 'severcon_equipment',
            'type'        => 'url',
        ));
    }
    
    // Подсекция: Преимущества
    $wp_customize->add_section('severcon_advantages', array(
        'title'    => __('Блок преимуществ', 'severcon'),
        'panel'    => 'severcon_homepage',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('advantages_title', array(
        'default'           => __('Наши преимущества', 'severcon'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('advantages_title', array(
        'label'       => __('Заголовок блока', 'severcon'),
        'section'     => 'severcon_advantages',
        'type'        => 'text',
    ));
    
    for ($i = 1; $i <= 3; $i++) {
        $wp_customize->add_setting('advantage_' . $i . '_icon', array(
            'default'           => 'fas fa-award',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('advantage_' . $i . '_icon', array(
            'label'       => sprintf(__('Иконка преимущества %d', 'severcon'), $i),
            'section'     => 'severcon_advantages',
            'type'        => 'text',
            'description' => __('Используйте классы Font Awesome, например: fas fa-award', 'severcon'),
        ));
        
        $wp_customize->add_setting('advantage_' . $i . '_title', array(
            'default'           => sprintf(__('Преимущество %d', 'severcon'), $i),
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('advantage_' . $i . '_title', array(
            'label'       => sprintf(__('Заголовок преимущества %d', 'severcon'), $i),
            'section'     => 'severcon_advantages',
            'type'        => 'text',
        ));
        
        $wp_customize->add_setting('advantage_' . $i . '_description', array(
            'default'           => sprintf(__('Описание преимущества %d', 'severcon'), $i),
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'postMessage',
        ));
        
        $wp_customize->add_control('advantage_' . $i . '_description', array(
            'label'       => sprintf(__('Описание преимущества %d', 'severcon'), $i),
            'section'     => 'severcon_advantages',
            'type'        => 'textarea',
            'rows'        => 3,
        ));
    }
    
    // ===== СЕКЦИЯ: ФУТЕР =====
    $wp_customize->add_section('severcon_footer', array(
        'title'    => __('Футер', 'severcon'),
        'priority' => 50,
    ));
    
    $wp_customize->add_setting('footer_copyright', array(
        'default'           => sprintf(__('© %s Все права защищены.', 'severcon'), date('Y')),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control('footer_copyright', array(
        'label'       => __('Текст копирайта', 'severcon'),
        'section'     => 'severcon_footer',
        'type'        => 'text',
    ));
    
    $wp_customize->add_setting('footer_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control(
        $wp_customize,
        'footer_logo',
        array(
            'label'       => __('Логотип в футере', 'severcon'),
            'section'     => 'severcon_footer',
        )
    ));
    
    // ===== СЕКЦИЯ: НАСТРОЙКИ ФОРМ =====
    $wp_customize->add_section('severcon_forms', array(
        'title'    => __('Формы', 'severcon'),
        'priority' => 60,
    ));
    
    $wp_customize->add_setting('request_additional_emails', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('request_additional_emails', array(
        'label'       => __('Дополнительные email для уведомлений', 'severcon'),
        'section'     => 'severcon_forms',
        'type'        => 'text',
        'description' => __('Перечислите email через запятую', 'severcon'),
    ));
    
    $wp_customize->add_setting('recaptcha_site_key', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('recaptcha_site_key', array(
        'label'       => __('reCAPTCHA Site Key', 'severcon'),
        'section'     => 'severcon_forms',
        'type'        => 'text',
        'description' => __('Для защиты форм от спама', 'severcon'),
    ));
    
    $wp_customize->add_setting('recaptcha_secret_key', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('recaptcha_secret_key', array(
        'label'       => __('reCAPTCHA Secret Key', 'severcon'),
        'section'     => 'severcon_forms',
        'type'        => 'text',
    ));
    
    // ===== СЕКЦИЯ: ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ =====
    $wp_customize->add_section('severcon_additional', array(
        'title'    => __('Дополнительные настройки', 'severcon'),
        'priority' => 100,
    ));
    
    $wp_customize->add_setting('enable_analytics', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('enable_analytics', array(
        'label'       => __('Включить Google Analytics', 'severcon'),
        'section'     => 'severcon_additional',
        'type'        => 'checkbox',
    ));
    
    $wp_customize->add_setting('analytics_code', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('analytics_code', array(
        'label'       => __('Код Google Analytics', 'severcon'),
        'section'     => 'severcon_additional',
        'type'        => 'textarea',
        'rows'        => 4,
        'description' => __('Вставьте код отслеживания GA4', 'severcon'),
        'active_callback' => function() {
            return get_theme_mod('enable_analytics', false);
        }
    ));
    
    $wp_customize->add_setting('custom_css', array(
        'default'           => '',
        'sanitize_callback' => 'wp_strip_all_tags',
    ));
    
    $wp_customize->add_control('custom_css', array(
        'label'       => __('Дополнительный CSS', 'severcon'),
        'section'     => 'severcon_additional',
        'type'        => 'textarea',
        'rows'        => 10,
        'description' => __('Добавьте свои стили CSS', 'severcon'),
    ));
}
add_action('customize_register', 'severcon_customize_register');

/**
 * Добавление динамического CSS из настроек Customizer
 */
function severcon_customizer_css() {
    $css = '';
    
    // Кастомный CSS из настроек
    $custom_css = get_theme_mod('custom_css', '');
    if (!empty($custom_css)) {
        $css .= '/* Customizer CSS */' . "\n";
        $css .= wp_strip_all_tags($custom_css) . "\n";
    }
    
    if (!empty($css)) {
        wp_add_inline_style('severcon-main', $css);
    }
}
add_action('wp_enqueue_scripts', 'severcon_customizer_css', 100);

/**
 * Добавление Google Analytics кода
 */
function severcon_add_analytics_code() {
    if (!get_theme_mod('enable_analytics', false)) {
        return;
    }
    
    $analytics_code = get_theme_mod('analytics_code', '');
    
    if (empty($analytics_code)) {
        return;
    }
    
    // Удаляем теги script если они есть
    $analytics_code = str_replace(array('<script>', '</script>'), '', $analytics_code);
    
    echo '<!-- Google Analytics -->' . "\n";
    echo '<script>' . "\n";
    echo $analytics_code . "\n";
    echo '</script>' . "\n";
    echo '<!-- End Google Analytics -->' . "\n";
}
add_action('wp_head', 'severcon_add_analytics_code', 90);

/**
 * Добавление рекапчи в формы
 */
function severcon_add_recaptcha_script() {
    $site_key = get_theme_mod('recaptcha_site_key', '');
    
    if (empty($site_key)) {
        return;
    }
    
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $site_key, array(), null, true);
    
    wp_add_inline_script('google-recaptcha', '
        grecaptcha.ready(function() {
            document.addEventListener("DOMContentLoaded", function() {
                var forms = document.querySelectorAll(".severcon-request-form");
                forms.forEach(function(form) {
                    form.addEventListener("submit", function(e) {
                        e.preventDefault();
                        
                        grecaptcha.execute("' . esc_js($site_key) . '", {action: "submit"}).then(function(token) {
                            var input = document.createElement("input");
                            input.type = "hidden";
                            input.name = "recaptcha_token";
                            input.value = token;
                            form.appendChild(input);
                            
                            // Отправляем форму
                            var formData = new FormData(form);
                            // ... отправка AJAX ...
                        });
                    });
                });
            });
        });
    ');
}
add_action('wp_enqueue_scripts', 'severcon_add_recaptcha_script');

/**
 * Live Preview для Customizer
 */
function severcon_customizer_live_preview() {
    wp_enqueue_script(
        'severcon-customizer-preview',
        SEVERCON_THEME_URI . '/assets/js/admin/customizer-preview.js',
        array('jquery', 'customize-preview'),
        SEVERCON_THEME_VERSION,
        true
    );
    
    wp_localize_script('severcon-customizer-preview', 'severcon_customizer', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('customizer_preview_nonce'),
    ));
}
add_action('customize_preview_init', 'severcon_customizer_live_preview');