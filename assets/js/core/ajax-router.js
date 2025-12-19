/**
 * Единый AJAX клиент для работы с роутером
 */
class SeverconAjaxClient {
    constructor(config) {
        this.config = config;
        this.pendingRequests = new Map();
    }
    
    /**
     * Основной метод отправки запроса
     */
    async request(action, data = {}, options = {}) {
        const {
            useLegacy = false,
            method = 'POST',
            headers = {},
            timeout = 30000
        } = options;
        
        // Подготовка данных
        const requestData = {
            action: useLegacy ? this.config.ajax.legacyEndpoints[action] || action : this.config.ajax.action,
            router_action: useLegacy ? null : action,
            nonce: this.config.ajax.nonce,
            ...data
        };
        
        // Для нового роутера передаем action внутри данных
        if (!useLegacy) {
            requestData.action = 'severcon_router';
            requestData.router_action = action;
        }
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        try {
            const response = await fetch(this.config.ajax.url, {
                method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    ...headers
                },
                body: new URLSearchParams(requestData),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new SeverconAjaxError(
                    result.error?.message || 'Unknown error',
                    result.error?.code || 'unknown',
                    result.error?.data
                );
            }
            
            return result.data;
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new SeverconAjaxError('Request timeout', 'timeout');
            }
            
            if (error instanceof SeverconAjaxError) {
                throw error;
            }
            
            throw new SeverconAjaxError(
                'Network error',
                'network_error',
                { originalError: error.message }
            );
        }
    }
    
    /**
     * Специфичные методы для удобства
     */
    async filterProducts(data) {
        return this.request('filter_products', data);
    }
    
    async updateFilterCounts(data) {
        return this.request('update_filter_counts', data);
    }
    
    async quickView(productId) {
        return this.request('quick_view', { product_id: productId });
    }
    
    async loadNews(page) {
        return this.request('load_news', { page });
    }
    
    /**
     * Тестовый запрос
     */
    async testConnection() {
        return this.request('test_connection', {}, { useLegacy: false });
    }
}

/**
 * Кастомный класс ошибки AJAX
 */
class SeverconAjaxError extends Error {
    constructor(message, code, data = null) {
        super(message);
        this.name = 'SeverconAjaxError';
        this.code = code;
        this.data = data;
    }
}

// Экспортируем синглтон
const severconAjax = new SeverconAjaxClient(severconConfig);

// Для обратной совместимости
if (typeof window.severcon_ajax !== 'undefined') {
    window.severcon_ajax.router = severconAjax;
} else {
    window.severcon_ajax = { router: severconAjax };
}

export default severconAjax;
