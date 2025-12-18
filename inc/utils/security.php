<?php
/**
 * Функции безопасности
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Защита от XSS атак
 */
function severcon_sanitize_output($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Валидация email
 */
function severcon_validate_email($email) {
    if (!is_email($email)) {
        return false;
    }
    
    // Дополнительная проверка от спама
    $disposable_domains = array(
        'tempmail.com',
        'mailinator.com',
        '10minutemail.com',
        'guerrillamail.com',
        // Добавьте другие временные домены
    );
    
    $domain = explode('@', $email);
    $domain = strtolower(end($domain));
    
    if (in_array($domain, $disposable_domains)) {
        return false;
    }
    
    return true;
}

/**
 * Валидация телефона
 */
function severcon_validate_phone($phone) {
    // Убираем все нецифровые символы
    $clean_phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Проверяем длину (10-15 цифр)
    if (strlen($clean_phone) < 10 || strlen($clean_phone) > 15) {
        return false;
    }
    
    // Проверяем код страны (если есть)
    if (strlen($clean_phone) > 11) {
        $country_code = substr($clean_phone, 0, strlen($clean_phone) - 10);
        if (!in_array($country_code, array('7', '8', '375', '380'))) {
            return false;
        }
    }
    
    return true;
}

/**
 * Защита от SQL инъекций в пользовательских запросах
 */
function severcon_sanitize_sql($string) {
    global $wpdb;
    return $wpdb->_real_escape($string);
}

/**
 * Валидация ID (числовое, положительное)
 */
function severcon_validate_id($id) {
    if (!is_numeric($id)) {
        return false;
    }
    
    $id = intval($id);
    
    if ($id <= 0) {
        return false;
    }
    
    return $id;
}

/**
 * Проверка reCAPTCHA (если подключена)
 */
function severcon_verify_recaptcha($response) {
    if (empty($response)) {
        return false;
    }
    
    $secret_key = get_theme_mod('recaptcha_secret_key', '');
    
    if (empty($secret_key)) {
        return true; // Если ключ не настроен, пропускаем проверку
    }
    
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $verify_data = array(
        'secret'   => $secret_key,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    );
    
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => http_build_query($verify_data),
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
        )
    );
    
    $context  = stream_context_create($options);
    $result = file_get_contents($verify_url, false, $context);
    
    if ($result === FALSE) {
        return false;
    }
    
    $result_data = json_decode($result);
    
    return $result_data->success && $result_data->score > 0.5;
}

/**
 * Защита от CSRF для форм
 */
function severcon_csrf_field($action = 'general') {
    $nonce = wp_create_nonce('severcon_csrf_' . $action);
    echo '<input type="hidden" name="csrf_token" value="' . esc_attr($nonce) . '">';
}

/**
 * Проверка CSRF токена
 */
function severcon_verify_csrf($action = 'general') {
    if (!isset($_POST['csrf_token'])) {
        return false;
    }
    
    return wp_verify_nonce($_POST['csrf_token'], 'severcon_csrf_' . $action);
}

/**
 * Очистка пользовательского ввода
 */
function severcon_clean_input($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = severcon_clean_input($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    return $input;
}

/**
 * Логирование подозрительной активности
 */
function severcon_log_security_event($event, $data = array()) {
    $log_file = WP_CONTENT_DIR . '/severcon-security.log';
    
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'event'     => $event,
        'ip'        => $_SERVER['REMOTE_ADDR'],
        'user_agent'=> isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
        'referer'   => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
        'data'      => $data
    );
    
    $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    
    // Пишем в лог файл
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    
    // Также отправляем уведомление администратору при критических событиях
    $critical_events = array('failed_login', 'xss_attempt', 'sql_injection_attempt');
    
    if (in_array($event, $critical_events)) {
        $admin_email = get_option('admin_email');
        $subject = 'Безопасность: ' . $event;
        $message = 'Произошло событие безопасности на сайте ' . home_url() . "\n\n";
        $message .= 'Событие: ' . $event . "\n";
        $message .= 'Время: ' . $log_entry['timestamp'] . "\n";
        $message .= 'IP: ' . $log_entry['ip'] . "\n";
        
        wp_mail($admin_email, $subject, $message);
    }
}

/**
 * Проверка на ботов и спам
 */
function severcon_is_spam_bot() {
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    
    $bot_indicators = array(
        'bot', 'spider', 'crawler', 'scraper', 'curl', 'wget',
        'python', 'java', 'php', 'ruby', 'perl', 'golang',
        'ahrefs', 'semrush', 'majestic', 'moz.com',
        'yandex', 'google', 'bing', 'baidu'
    );
    
    foreach ($bot_indicators as $indicator) {
        if (strpos($user_agent, $indicator) !== false) {
            return true;
        }
    }
    
    // Проверка времени заполнения формы (слишком быстро)
    if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
        $request_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        if ($request_time < 2) { // Меньше 2 секунд
            return true;
        }
    }
    
    return false;
}