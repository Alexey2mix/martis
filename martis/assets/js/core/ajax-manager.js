/**
 * Менеджер AJAX запросов
 */

import CONFIG from './config.js';
import Utils from './utils.js';

class AjaxManager {
    constructor() {
        this.queue = [];
        this.activeRequests = 0;
        this.maxConcurrent = 3;
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 минут
    }
    
    /**
     * Отправка AJAX запроса
     */
    async request(options) {
        const {
            action,
            data = {},
            method = 'POST',
            cache = false,
            cacheKey = null,
            timeout = 30000,
            retry = 3,
            retryDelay = 1000
        } = options;
        
        // Проверка кэша
        if (cache && cacheKey) {
            const cached = this.getFromCache(cacheKey);
            if (cached !== null) {
                return cached;
            }
        }
        
        // Подготовка данных
        const requestData = {
            action,
            ...data,
            nonce: CONFIG.ajax.nonce
        };
        
        // Формирование запроса
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        let attempts = 0;
        
        while (attempts < retry) {
            try {
                const response = await fetch(CONFIG.ajax.url, {
                    method,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(requestData),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                // Кэширование результата
                if (cache && cacheKey && result.success) {
                    this.saveToCache(cacheKey, result);
                }
                
                return result;
                
            } catch (error) {
                attempts++;
                
                if (attempts >= retry) {
                    throw this.handleError(error);
                }
                
                // Экспоненциальная задержка перед повторной попыткой
                await new Promise(resolve => 
                    setTimeout(resolve, retryDelay * Math.pow(2, attempts - 1))
                );
            }
        }
    }
    
    /**
     * Обработка ошибок
     */
    handleError(error) {
        console.error('AJAX Error:', error);
        
        if (error.name === 'AbortError') {
            return {
                success: false,
                data: null,
                error: {
                    code: 'timeout',
                    message: CONFIG.ajax.i18n.timeout || 'Превышено время ожидания'
                }
            };
        }
        
        if (error.message.includes('Failed to fetch')) {
            return {
                success: false,
                data: null,
                error: {
                    code: 'network_error',
                    message: CONFIG.ajax.i18n.network_error || 'Ошибка сети. Проверьте соединение.'
                }
            };
        }
        
        return {
            success: false,
            data: null,
            error: {
                code: 'unknown_error',
                message: CONFIG.ajax.i18n.unknown_error || 'Произошла неизвестная ошибка'
            }
        };
    }
    
    /**
     * Кэширование данных
     */
    saveToCache(key, data) {
        const cacheItem = {
            data,
            timestamp: Date.now()
        };
        
        this.cache.set(key, cacheItem);
        
        // Автоочистка устаревшего кэша
        setTimeout(() => {
            this.cache.delete(key);
        }, this.cacheTimeout);
    }
    
    /**
     * Получение данных из кэша
     */
    getFromCache(key) {
        const cacheItem = this.cache.get(key);
        
        if (!cacheItem) {
            return null;
        }
        
        // Проверка на устаревание
        if (Date.now() - cacheItem.timestamp > this.cacheTimeout) {
            this.cache.delete(key);
            return null;
        }
        
        return cacheItem.data;
    }
    
    /**
     * Очистка кэша
     */
    clearCache(pattern = null) {
        if (pattern) {
            const regex = new RegExp(pattern);
            for (const key of this.cache.keys()) {
                if (regex.test(key)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }
    
    /**
     * Фильтрация товаров
     */
    async filterProducts(options) {
        const {
            categoryId,
            filters = {},
            orderby = 'menu_order',
            page = 1,
            per_page = CONFIG.filters.per_page
        } = options;
        
        const cacheKey = `filter_${categoryId}_${JSON.stringify(filters)}_${orderby}_${page}_${per_page}`;
        
        try {
            const result = await this.request({
                action: 'filter_category_products',
                data: {
                    category_id: categoryId,
                    filters,
                    orderby,
                    page,
                    per_page,
                    nonce: CONFIG.ajax.filter_nonce
                },
                cache: true,
                cacheKey
            });
            
            return result;
            
        } catch (error) {
            Utils.showError('Ошибка фильтрации товаров');
            return {
                success: false,
                data: {
                    html: '<div class="filter-error">Ошибка загрузки товаров</div>',
                    count: 0,
                    max_pages: 0
                }
            };
        }
    }
    
    /**
     * Обновление счетчиков фильтров
     */
    async updateFilterCounts(options) {
        const { categoryId, filters = {} } = options;
        
        const cacheKey = `filter_counts_${categoryId}_${JSON.stringify(filters)}`;
        
        try {
            const result = await this.request({
                action: 'update_filter_counts',
                data: {
                    category_id: categoryId,
                    filters
                },
                cache: true,
                cacheKey,
                timeout: 15000
            });
            
            return result;
            
        } catch (error) {
            console.warn('Failed to update filter counts:', error);
            return {
                success: false,
                data: {}
            };
        }
    }
    
    /**
     * Быстрый просмотр товара
     */
    async getQuickView(productId) {
        const cacheKey = `quick_view_${productId}`;
        
        try {
            const result = await this.request({
                action: 'get_quick_view',
                data: { product_id: productId },
                cache: true,
                cacheKey
            });
            
            return result;
            
        } catch (error) {
            Utils.showError('Ошибка загрузки товара');
            return {
                success: false,
                data: null,
                error: 'Не удалось загрузить товар'
            };
        }
    }
    
    /**
     * Загрузка дополнительных новостей
     */
    async loadMoreNews(page) {
        try {
            const result = await this.request({
                action: 'load_more_news',
                data: { page },
                cache: false
            });
            
            return result;
            
        } catch (error) {
            return {
                success: false,
                data: null,
                error: 'Ошибка загрузки новостей'
            };
        }
    }
    
    /**
     * Отправка формы запроса
     */
    async submitRequest(formData) {
        try {
            const result = await this.request({
                action: 'submit_request_form',
                data: formData,
                cache: false,
                timeout: 60000
            });
            
            return result;
            
        } catch (error) {
            return {
                success: false,
                data: null,
                error: 'Ошибка отправки формы'
            };
        }
    }
    
    /**
     * Пакетная обработка запросов
     */
    async batchRequests(requests) {
        const results = [];
        
        // Ограничение параллельных запросов
        const chunks = this.chunkArray(requests, this.maxConcurrent);
        
        for (const chunk of chunks) {
            const promises = chunk.map(req => this.request(req));
            const chunkResults = await Promise.allSettled(promises);
            results.push(...chunkResults);
        }
        
        return results.map((result, index) => {
            if (result.status === 'fulfilled') {
                return {
                    success: true,
                    data: result.value,
                    request: requests[index]
                };
            } else {
                return {
                    success: false,
                    error: result.reason,
                    request: requests[index]
                };
            }
        });
    }
    
    /**
     * Разделение массива на чанки
     */
    chunkArray(array, size) {
        const chunks = [];
        for (let i = 0; i < array.length; i += size) {
            chunks.push(array.slice(i, i + size));
        }
        return chunks;
    }
    
    /**
     * Отмена всех активных запросов
     */
    abortAll() {
        // Реализация отмены запросов через AbortController
        this.queue.forEach(request => {
            if (request.abort) {
                request.abort();
            }
        });
        
        this.queue = [];
        this.activeRequests = 0;
    }
}

// Создаем глобальный инстанс
const ajaxManager = new AjaxManager();

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ajaxManager;
} else {
    window.SeverconAjax = ajaxManager;
}