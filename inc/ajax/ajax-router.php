<?php
/**
 * Единый AJAX роутер для темы Severcon
 * Централизованное управление всеми AJAX запросами
 */

if (!defined('ABSPATH')) {
    exit;
}

class Severcon_AJAX_Router {
    
    /**
     * @var array Зарегистрированные действия
     */
    private $actions = [];
    
    /**
     * @var Severcon_AJAX_Router Единственный экземпляр класса
     */
    private static $instance = null;
    
    /**
     * Получение экземпляра синглтона
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Приватный конструктор
     */
    private function __construct() {
        // Регистрируем основные действия при инициализации
        add_action('init', [$this, 'register_core_actions']);
        
        // Регистрируем обработчик всех AJAX запросов
        add_action('wp_ajax_severcon_router', [$this, 'handle_request']);
        add_action('wp_ajax_nopriv_severcon_router', [$this, 'handle_request']);
    }
    
    /**
     * Регистрация основного действия
     */
    public function register($action, $callback, $config = []) {
        $defaults = [
            'require_nonce' => true,
            'capability' => '', // Требуемая capability (если пусто - доступно всем)
            'validate_callback' => null, // Дополнительная проверка
            'description' => '', // Описание для разработчика
            'log_request' => WP_DEBUG, // Логировать запросы в режиме отладки
        ];
        
        $this->actions[$action] = array_merge($defaults, $config);
        $this->actions[$action]['callback'] = $callback;
        
        return $this;
    }
    
    /**
     * Регистрация основных действий системы
     */
    public function register_core_actions() {
        // Фильтрация товаров
        $this->register('filter_products', 'severcon_ajax_filter_category_products', [
            'description' => 'Фильтрация и пагинация товаров категории',
            'validate_callback' => function($data) {
                return !empty($data['category_id']) && is_numeric($data['category_id']);
            }
        ]);
        
        // Обновление счетчиков фильтров
        $this->register('update_filter_counts', 'severcon_ajax_optimized_update_filter_counts', [
            'description' => 'Обновление счетчиков товаров для фильтров',
            'validate_callback' => function($data) {
                return !empty($data['category_id']) && is_numeric($data['category_id']);
            }
        ]);
        
        // Быстрый просмотр товара
        $this->register('quick_view', 'severcon_ajax_quick_view_product', [
            'description' => 'Быстрый просмотр товара',
            'validate_callback' => function($data) {
                return !empty($data['product_id']) && is_numeric($data['product_id']);
            }
        ]);
        
        // Загрузка новостей
        $this->register('load_news', 'severcon_ajax_load_more_news', [
            'description' => 'Загрузка дополнительных новостей',
            'validate_callback' => function($data) {
                return !empty($data['page']) && is_numeric($data['page']);
            }
        ]);
        
        // Тестовый endpoint для проверки
        $this->register('test_connection', [$this, 'test_connection_handler'], [
            'require_nonce' => false,
            'description' => 'Тестовый endpoint для проверки соединения'
        ]);
        
        // Дополнительные действия можно добавлять через хук
        do_action('severcon_ajax_register_actions', $this);
    }
    
    /**
     * Основной обработчик AJAX запросов
     */
    public function handle_request() {
        // Получаем данные запроса
        $request_data = $this->get_request_data();
        $action = $request_data['action'] ?? '';
        
        // Логируем запрос в режиме отладки
        if (WP_DEBUG && apply_filters('severcon_ajax_log_requests', true)) {
            error_log('Severcon AJAX Request: ' . $action . ' - ' . wp_json_encode($request_data));
        }
        
        // Проверяем наличие действия
        if (empty($action) || !isset($this->actions[$action])) {
            $this->send_error('Действие не найдено', 'action_not_found', 404);
        }
        
        $action_config = $this->actions[$action];
        
        // Проверка nonce
        if ($action_config['require_nonce']) {
            if (!$this->verify_nonce($request_data['nonce'] ?? '')) {
                $this->send_error('Ошибка безопасности', 'invalid_nonce', 403);
            }
        }
        
        // Проверка прав доступа
        if (!empty($action_config['capability'])) {
            if (!current_user_can($action_config['capability'])) {
                $this->send_error('Недостаточно прав', 'insufficient_permissions', 403);
            }
        }
        
        // Дополнительная проверка
        if (is_callable($action_config['validate_callback'])) {
            $validation_result = call_user_func($action_config['validate_callback'], $request_data);
            if (false === $validation_result) {
                $this->send_error('Неверные параметры запроса', 'invalid_parameters', 400);
            }
        }
        
        // Выполняем действие
        try {
            $result = call_user_func($action_config['callback'], $request_data);
            
            if (is_wp_error($result)) {
                $this->send_error(
                    $result->get_error_message(),
                    $result->get_error_code(),
                    400
                );
            }
            
            $this->send_success($result);
            
        } catch (Exception $e) {
            $error_data = [
                'message' => 'Внутренняя ошибка сервера',
                'code' => 'server_error'
            ];
            
            if (WP_DEBUG) {
                $error_data['debug'] = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            
            $this->send_error(
                $error_data['message'],
                $error_data['code'],
                500,
                WP_DEBUG ? $error_data['debug'] : []
            );
        }
        
        wp_die();
    }
    
    /**
     * Получение данных запроса
     */
    private function get_request_data() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if ('POST' === $method) {
            // Для POST запросов
            $data = $_POST;
            
            // Проверяем JSON в raw body
            $input = file_get_contents('php://input');
            if (!empty($input) && $json_data = json_decode($input, true)) {
                $data = array_merge($data, $json_data);
            }
        } else {
            // Для GET запросов
            $data = $_GET;
        }
        
        // Очищаем данные
        return $this->sanitize_request_data($data);
    }
    
    /**
     * Очистка данных запроса
     */
    private function sanitize_request_data($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_request_data($value);
            } else {
                // Разные типы данных требуют разной санитизации
                if (in_array($key, ['category_id', 'product_id', 'page', 'per_page'])) {
                    $sanitized[$key] = intval($value);
                } elseif (in_array($key, ['nonce', 'action'])) {
                    $sanitized[$key] = sanitize_text_field($value);
                } elseif ($key === 'filters' && is_string($value)) {
                    // Пытаемся декодировать JSON фильтров
                    $decoded = json_decode(stripslashes($value), true);
                    $sanitized[$key] = is_array($decoded) ? $this->sanitize_filters($decoded) : [];
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Очистка данных фильтров
     */
    private function sanitize_filters($filters) {
        $sanitized = [];
        
        foreach ($filters as $taxonomy => $terms) {
            $sanitized_taxonomy = sanitize_text_field($taxonomy);
            $sanitized[$sanitized_taxonomy] = [];
            
            if (is_array($terms)) {
                foreach ($terms as $term) {
                    $sanitized[$sanitized_taxonomy][] = sanitize_text_field($term);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Проверка nonce
     */
    private function verify_nonce($nonce) {
        return wp_verify_nonce($nonce, 'severcon_ajax_nonce');
    }
    
    /**
     * Успешный ответ
     */
    private function send_success($data = [], $status_code = 200) {
        status_header($status_code);
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => current_time('timestamp')
        ];
        
        wp_send_json($response);
    }
    
    /**
     * Ошибка
     */
    private function send_error($message, $code = 'error', $status_code = 400, $additional_data = []) {
        status_header($status_code);
        
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ],
            'timestamp' => current_time('timestamp')
        ];
        
        if (!empty($additional_data)) {
            $response['error']['data'] = $additional_data;
        }
        
        wp_send_json($response);
    }
    
    /**
     * Тестовый обработчик
     */
    public function test_connection_handler($data) {
        return [
            'status' => 'ok',
            'message' => 'AJAX роутер работает корректно',
            'server_time' => current_time('mysql'),
            'received_data' => $data
        ];
    }
    
    /**
     * Получение списка зарегистрированных действий
     * Для администраторов в режиме отладки
     */
    public function get_registered_actions() {
        $actions = [];
        
        foreach ($this->actions as $action => $config) {
            $actions[$action] = [
                'description' => $config['description'],
                'require_nonce' => $config['require_nonce'],
                'capability' => $config['capability']
            ];
        }
        
        return $actions;
    }
}

/**
 * Инициализация AJAX роутера
 */
function severcon_init_ajax_router() {
    return Severcon_AJAX_Router::get_instance();
}

// Инициализируем сразу
add_action('init', 'severcon_init_ajax_router', 5);

/**
 * Вспомогательная функция для быстрой регистрации действий
 */
function severcon_register_ajax_action($action, $callback, $config = []) {
    $router = Severcon_AJAX_Router::get_instance();
    return $router->register($action, $callback, $config);
}

/**
 * Генерация nonce для AJAX запросов
 */
function severcon_get_ajax_nonce() {
    return wp_create_nonce('severcon_ajax_nonce');
}
