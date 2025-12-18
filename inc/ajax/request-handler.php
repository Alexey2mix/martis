<?php
/**
 * Обработчик форм запросов
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Инициализация обработчиков запросов
 */
function severcon_init_request_handlers() {
    add_action('wp_ajax_submit_request_form', 'severcon_ajax_submit_request_form');
    add_action('wp_ajax_nopriv_submit_request_form', 'severcon_ajax_submit_request_form');
}
add_action('init', 'severcon_init_request_handlers');

/**
 * AJAX обработка формы запроса
 */
function severcon_ajax_submit_request_form() {
    // Проверка безопасности
    if (!severcon_verify_ajax_request('severcon_ajax_nonce')) {
        return;
    }
    
    // Проверка на спам-бота
    if (severcon_is_spam_bot()) {
        severcon_log_security_event('spam_bot_request', $_POST);
        wp_send_json_error(array(
            'message' => __('Обнаружена подозрительная активность', 'severcon'),
            'code'    => 'spam_detected'
        ), 403);
    }
    
    // Получение и валидация данных
    $data = severcon_validate_request_data($_POST);
    
    if (is_wp_error($data)) {
        wp_send_json_error(array(
            'message' => $data->get_error_message(),
            'code'    => $data->get_error_code(),
            'fields'  => $data->get_error_data()
        ));
    }
    
    // Сохранение заявки
    $request_id = severcon_save_request($data);
    
    if (is_wp_error($request_id)) {
        wp_send_json_error(array(
            'message' => __('Ошибка при сохранении заявки', 'severcon'),
            'code'    => 'save_error'
        ));
    }
    
    // Отправка уведомлений
    severcon_send_request_notifications($data, $request_id);
    
    // Ответ клиенту
    wp_send_json_success(array(
        'message' => __('Ваша заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.', 'severcon'),
        'request_id' => $request_id
    ));
    
    wp_die();
}

/**
 * Валидация данных формы запроса
 */
function severcon_validate_request_data($post_data) {
    $errors = new WP_Error();
    $validated_data = array();
    
    // Имя
    $name = trim(severcon_get_array_value($post_data, 'name', ''));
    if (empty($name)) {
        $errors->add('empty_name', __('Введите ваше имя', 'severcon'), 'name');
    } elseif (strlen($name) < 2) {
        $errors->add('short_name', __('Имя слишком короткое', 'severcon'), 'name');
    } elseif (strlen($name) > 100) {
        $errors->add('long_name', __('Имя слишком длинное', 'severcon'), 'name');
    } else {
        $validated_data['name'] = sanitize_text_field($name);
    }
    
    // Телефон (обязательное поле)
    $phone = trim(severcon_get_array_value($post_data, 'phone', ''));
    if (empty($phone)) {
        $errors->add('empty_phone', __('Введите ваш телефон', 'severcon'), 'phone');
    } elseif (!severcon_validate_phone($phone)) {
        $errors->add('invalid_phone', __('Введите корректный телефон', 'severcon'), 'phone');
    } else {
        $validated_data['phone'] = severcon_format_phone($phone);
    }
    
    // Email (необязательное поле)
    $email = trim(severcon_get_array_value($post_data, 'email', ''));
    if (!empty($email)) {
        if (!severcon_validate_email($email)) {
            $errors->add('invalid_email', __('Введите корректный email', 'severcon'), 'email');
        } else {
            $validated_data['email'] = sanitize_email($email);
        }
    }
    
    // Компания (необязательное поле)
    $company = trim(severcon_get_array_value($post_data, 'company', ''));
    if (!empty($company)) {
        $validated_data['company'] = sanitize_text_field($company);
    }
    
    // Сообщение
    $message = trim(severcon_get_array_value($post_data, 'message', ''));
    if (!empty($message)) {
        if (strlen($message) > 2000) {
            $errors->add('long_message', __('Сообщение слишком длинное', 'severcon'), 'message');
        } else {
            $validated_data['message'] = sanitize_textarea_field($message);
        }
    }
    
    // ID товара (если есть)
    $product_id = severcon_get_array_value($post_data, 'product_id', 0);
    if ($product_id) {
        $validated_data['product_id'] = severcon_validate_id($product_id);
    }
    
    // Название товара
    $product_name = trim(severcon_get_array_value($post_data, 'product_name', ''));
    if (!empty($product_name)) {
        $validated_data['product_name'] = sanitize_text_field($product_name);
    }
    
    // URL страницы
    $page_url = severcon_get_array_value($post_data, 'page_url', '');
    if (!empty($page_url)) {
        $validated_data['page_url'] = esc_url_raw($page_url);
    }
    
    // Проверка honeypot (защита от спама)
    $honeypot = severcon_get_array_value($post_data, 'website', '');
    if (!empty($honeypot)) {
        $errors->add('spam_detected', __('Обнаружен спам', 'severcon'));
    }
    
    // Проверка времени заполнения формы
    $form_time = severcon_get_array_value($post_data, 'form_time', 0);
    $current_time = time();
    if ($form_time && ($current_time - $form_time) < 3) {
        $errors->add('fast_form', __('Форма заполнена слишком быстро', 'severcon'));
    }
    
    // Если есть ошибки - возвращаем их
    if ($errors->has_errors()) {
        return $errors;
    }
    
    // Добавляем метаданные
    $validated_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $validated_data['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $validated_data['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $validated_data['created_at'] = current_time('mysql');
    
    return $validated_data;
}

/**
 * Сохранение заявки в базе данных
 */
function severcon_save_request($data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'severcon_requests';
    
    // Проверяем существование таблицы
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Создаем таблицу если её нет
        severcon_create_requests_table();
    }
    
    // Подготовка данных для сохранения
    $insert_data = array(
        'name'         => $data['name'],
        'phone'        => $data['phone'],
        'email'        => isset($data['email']) ? $data['email'] : '',
        'company'      => isset($data['company']) ? $data['company'] : '',
        'message'      => isset($data['message']) ? $data['message'] : '',
        'product_id'   => isset($data['product_id']) ? $data['product_id'] : 0,
        'product_name' => isset($data['product_name']) ? $data['product_name'] : '',
        'page_url'     => isset($data['page_url']) ? $data['page_url'] : '',
        'ip_address'   => $data['ip_address'],
        'user_agent'   => $data['user_agent'],
        'referer'      => $data['referer'],
        'status'       => 'new',
        'created_at'   => $data['created_at']
    );
    
    // Вставляем данные
    $result = $wpdb->insert($table_name, $insert_data);
    
    if ($result === false) {
        return new WP_Error('db_error', __('Ошибка базы данных', 'severcon'));
    }
    
    return $wpdb->insert_id;
}

/**
 * Создание таблицы для хранения заявок
 */
function severcon_create_requests_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'severcon_requests';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        phone varchar(50) NOT NULL,
        email varchar(100) DEFAULT '',
        company varchar(100) DEFAULT '',
        message text,
        product_id bigint(20) DEFAULT 0,
        product_name varchar(255) DEFAULT '',
        page_url varchar(500) DEFAULT '',
        ip_address varchar(45) DEFAULT '',
        user_agent text,
        referer varchar(500) DEFAULT '',
        status varchar(20) DEFAULT 'new',
        notes text,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Отправка уведомлений о новой заявке
 */
function severcon_send_request_notifications($data, $request_id) {
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // Тема письма
    $subject = sprintf(__('Новая заявка с сайта %s (#%d)', 'severcon'), $site_name, $request_id);
    
    // Тело письма
    $message = sprintf(__('Новая заявка с сайта %s', 'severcon'), $site_name) . "\n\n";
    $message .= __('Детали заявки:', 'severcon') . "\n";
    $message .= __('────────────────', 'severcon') . "\n\n";
    
    $message .= sprintf(__('ID заявки: #%d', 'severcon'), $request_id) . "\n";
    $message .= sprintf(__('Имя: %s', 'severcon'), $data['name']) . "\n";
    $message .= sprintf(__('Телефон: %s', 'severcon'), $data['phone']) . "\n";
    
    if (!empty($data['email'])) {
        $message .= sprintf(__('Email: %s', 'severcon'), $data['email']) . "\n";
    }
    
    if (!empty($data['company'])) {
        $message .= sprintf(__('Компания: %s', 'severcon'), $data['company']) . "\n";
    }
    
    if (!empty($data['product_id'])) {
        $message .= sprintf(__('Товар ID: %d', 'severcon'), $data['product_id']) . "\n";
    }
    
    if (!empty($data['product_name'])) {
        $message .= sprintf(__('Название товара: %s', 'severcon'), $data['product_name']) . "\n";
    }
    
    if (!empty($data['message'])) {
        $message .= "\n" . __('Сообщение:', 'severcon') . "\n";
        $message .= $data['message'] . "\n";
    }
    
    $message .= "\n" . __('Техническая информация:', 'severcon') . "\n";
    $message .= __('────────────────', 'severcon') . "\n\n";
    
    $message .= sprintf(__('IP адрес: %s', 'severcon'), $data['ip_address']) . "\n";
    $message .= sprintf(__('Время: %s', 'severcon'), $data['created_at']) . "\n";
    
    if (!empty($data['page_url'])) {
        $message .= sprintf(__('Страница: %s', 'severcon'), $data['page_url']) . "\n";
    }
    
    $message .= "\n" . __('Ссылка для управления заявкой:', 'severcon') . "\n";
    $message .= admin_url('admin.php?page=severcon-requests&action=edit&request=' . $request_id) . "\n";
    
    // Заголовки письма
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . $admin_email . '>'
    );
    
    // Отправка письма администратору
    wp_mail($admin_email, $subject, $message, $headers);
    
    // Отправка копии на дополнительные email если настроены
    $additional_emails = get_theme_mod('request_additional_emails', '');
    if (!empty($additional_emails)) {
        $emails = array_map('trim', explode(',', $additional_emails));
        foreach ($emails as $email) {
            if (is_email($email)) {
                wp_mail($email, $subject, $message, $headers);
            }
        }
    }
    
    // Отправка подтверждения клиенту если указан email
    if (!empty($data['email'])) {
        severcon_send_client_confirmation($data, $request_id);
    }
}

/**
 * Отправка подтверждения клиенту
 */
function severcon_send_client_confirmation($data, $request_id) {
    $site_name = get_bloginfo('name');
    $site_email = get_option('admin_email');
    $site_phone = get_theme_mod('phone_number', '');
    
    // Тема письма
    $subject = sprintf(__('Ваша заявка #%d принята', 'severcon'), $request_id);
    
    // Тело письма
    $message = sprintf(__('Уважаемый %s,', 'severcon'), $data['name']) . "\n\n";
    $message .= __('Благодарим вас за обращение в компанию', 'severcon') . ' ' . $site_name . "!\n\n";
    $message .= __('Ваша заявка успешно принята и находится в обработке.', 'severcon') . "\n";
    $message .= __('Наш менеджер свяжется с вами в ближайшее время.', 'severcon') . "\n\n";
    
    $message .= __('Детали вашей заявки:', 'severcon') . "\n";
    $message .= __('────────────────', 'severcon') . "\n\n";
    
    $message .= sprintf(__('ID заявки: #%d', 'severcon'), $request_id) . "\n";
    $message .= sprintf(__('Имя: %s', 'severcon'), $data['name']) . "\n";
    $message .= sprintf(__('Телефон: %s', 'severcon'), $data['phone']) . "\n";
    
    if (!empty($data['email'])) {
        $message .= sprintf(__('Email: %s', 'severcon'), $data['email']) . "\n";
    }
    
    if (!empty($data['product_name'])) {
        $message .= sprintf(__('Товар: %s', 'severcon'), $data['product_name']) . "\n";
    }
    
    if (!empty($data['message'])) {
        $message .= "\n" . __('Ваше сообщение:', 'severcon') . "\n";
        $message .= $data['message'] . "\n";
    }
    
    $message .= "\n" . __('Наши контакты:', 'severcon') . "\n";
    $message .= __('────────────────', 'severcon') . "\n\n";
    
    if (!empty($site_phone)) {
        $message .= sprintf(__('Телефон: %s', 'severcon'), $site_phone) . "\n";
    }
    
    $message .= sprintf(__('Email: %s', 'severcon'), $site_email) . "\n";
    $message .= sprintf(__('Сайт: %s', 'severcon'), home_url()) . "\n";
    
    $message .= "\n" . __('С уважением,', 'severcon') . "\n";
    $message .= $site_name . "\n";
    
    // Заголовки письма
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $site_name . ' <' . $site_email . '>',
        'Reply-To: ' . $site_email
    );
    
    // Отправка письма клиенту
    wp_mail($data['email'], $subject, $message, $headers);
}

/**
 * Шорткод для формы запроса
 */
function severcon_request_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'product_id'   => 0,
        'product_name' => '',
        'title'        => __('Запросить цену', 'severcon'),
        'button_text'  => __('Отправить заявку', 'severcon'),
        'show_product' => true,
    ), $atts, 'severcon_request_form');
    
    ob_start();
    ?>
    <div class="severcon-request-form-wrapper">
        <form class="severcon-request-form" method="post" data-product-id="<?php echo esc_attr($atts['product_id']); ?>">
            <?php wp_nonce_field('severcon_request_form', 'severcon_request_nonce'); ?>
            <input type="hidden" name="form_time" value="<?php echo time(); ?>">
            <input type="hidden" name="product_id" value="<?php echo esc_attr($atts['product_id']); ?>">
            <input type="hidden" name="product_name" value="<?php echo esc_attr($atts['product_name']); ?>">
            <input type="hidden" name="page_url" value="<?php echo esc_url(get_permalink()); ?>">
            
            <!-- Honeypot поле для защиты от спама -->
            <div class="honeypot-field" style="display: none;">
                <label for="website">Вебсайт</label>
                <input type="text" id="website" name="website">
            </div>
            
            <div class="form-header">
                <h3 class="form-title"><?php echo esc_html($atts['title']); ?></h3>
                
                <?php if ($atts['show_product'] && !empty($atts['product_name'])) : ?>
                    <div class="form-product-info">
                        <strong><?php _e('Товар:', 'severcon'); ?></strong>
                        <span><?php echo esc_html($atts['product_name']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="request_name"><?php _e('Ваше имя *', 'severcon'); ?></label>
                        <input type="text" 
                               id="request_name" 
                               name="name" 
                               class="form-control" 
                               required 
                               minlength="2" 
                               maxlength="100"
                               placeholder="<?php _e('Иван Иванов', 'severcon'); ?>">
                        <div class="form-error" data-field="name"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="request_phone"><?php _e('Телефон *', 'severcon'); ?></label>
                        <input type="tel" 
                               id="request_phone" 
                               name="phone" 
                               class="form-control" 
                               required
                               placeholder="+7 (___) ___-__-__">
                        <div class="form-error" data-field="phone"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="request_email"><?php _e('Email', 'severcon'); ?></label>
                        <input type="email" 
                               id="request_email" 
                               name="email" 
                               class="form-control"
                               placeholder="example@mail.ru">
                        <div class="form-error" data-field="email"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="request_company"><?php _e('Компания', 'severcon'); ?></label>
                        <input type="text" 
                               id="request_company" 
                               name="company" 
                               class="form-control"
                               placeholder="<?php _e('Название компании', 'severcon'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="request_message"><?php _e('Сообщение', 'severcon'); ?></label>
                    <textarea id="request_message" 
                              name="message" 
                              class="form-control" 
                              rows="4"
                              maxlength="2000"
                              placeholder="<?php _e('Ваше сообщение или вопросы...', 'severcon'); ?>"></textarea>
                    <div class="form-error" data-field="message"></div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="submit-button">
                        <span class="button-text"><?php echo esc_html($atts['button_text']); ?></span>
                        <span class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                    
                    <div class="form-notice">
                        <?php _e('* - обязательные для заполнения поля', 'severcon'); ?>
                    </div>
                </div>
            </div>
            
            <div class="form-success" style="display: none;">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4><?php _e('Спасибо!', 'severcon'); ?></h4>
                <p><?php _e('Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.', 'severcon'); ?></p>
            </div>
        </form>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('severcon_request_form', 'severcon_request_form_shortcode');