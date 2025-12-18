/**
 * Система управления событиями
 */

import Utils from './utils.js';

class EventManager {
    constructor() {
        this.events = new Map();
        this.globalHandlers = new Map();
    }
    
    /**
     * Регистрация события
     */
    on(eventName, handler, options = {}) {
        if (!this.events.has(eventName)) {
            this.events.set(eventName, new Set());
        }
        
        const eventHandlers = this.events.get(eventName);
        eventHandlers.add({ handler, options });
        
        // Возвращаем функцию для удаления
        return () => this.off(eventName, handler);
    }
    
    /**
     * Удаление обработчика события
     */
    off(eventName, handler) {
        if (!this.events.has(eventName)) return;
        
        const eventHandlers = this.events.get(eventName);
        
        if (handler) {
            // Удаляем конкретный обработчик
            for (const item of eventHandlers) {
                if (item.handler === handler) {
                    eventHandlers.delete(item);
                    break;
                }
            }
        } else {
            // Удаляем все обработчики события
            eventHandlers.clear();
        }
        
        // Если обработчиков не осталось, удаляем событие
        if (eventHandlers.size === 0) {
            this.events.delete(eventName);
        }
    }
    
    /**
     * Генерация события
     */
    emit(eventName, data = {}, target = document) {
        if (!this.events.has(eventName)) return;
        
        const eventHandlers = this.events.get(eventName);
        const event = new CustomEvent(`severcon:${eventName}`, {
            detail: data,
            bubbles: true,
            cancelable: true
        });
        
        // Вызываем кастомные обработчики
        eventHandlers.forEach(({ handler, options }) => {
            try {
                handler(data, event);
            } catch (error) {
                console.error(`Error in event handler for "${eventName}":`, error);
            }
        });
        
        // Также триггерим DOM событие
        target.dispatchEvent(event);
    }
    
    /**
     * Одноразовый обработчик события
     */
    once(eventName, handler) {
        const onceHandler = (data, event) => {
            handler(data, event);
            this.off(eventName, onceHandler);
        };
        
        return this.on(eventName, onceHandler);
    }
    
    /**
     * Ожидание события
     */
    waitFor(eventName, timeout = 10000) {
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => {
                this.off(eventName, eventHandler);
                reject(new Error(`Event "${eventName}" timeout`));
            }, timeout);
            
            const eventHandler = (data) => {
                clearTimeout(timer);
                resolve(data);
            };
            
            this.on(eventName, eventHandler);
        });
    }
    
    /**
     * Регистрация глобального обработчика DOM событий
     */
    delegate(selector, eventType, handler, options = {}) {
        const key = `${selector}-${eventType}`;
        
        if (this.globalHandlers.has(key)) {
            return this.globalHandlers.get(key);
        }
        
        const wrappedHandler = (event) => {
            const target = event.target;
            const element = target.closest(selector);
            
            if (element) {
                handler.call(element, event, element);
            }
        };
        
        const removeHandler = Utils.on(document, eventType, wrappedHandler, options);
        this.globalHandlers.set(key, removeHandler);
        
        return removeHandler;
    }
    
    /**
     * Очистка всех событий
     */
    destroy() {
        // Удаляем все кастомные события
        this.events.clear();
        
        // Удаляем все глобальные обработчики
        this.globalHandlers.forEach(removeHandler => {
            if (typeof removeHandler === 'function') {
                removeHandler();
            }
        });
        
        this.globalHandlers.clear();
    }
    
    /**
     * Получение списка зарегистрированных событий
     */
    getRegisteredEvents() {
        return Array.from(this.events.keys());
    }
}

// Создаем глобальный инстанс
const eventManager = new EventManager();

// Предопределенные события
const SEVERCON_EVENTS = {
    // Навигация
    MOBILE_MENU_OPEN: 'mobile-menu:open',
    MOBILE_MENU_CLOSE: 'mobile-menu:close',
    SEARCH_OPEN: 'search:open',
    SEARCH_CLOSE: 'search:close',
    MODAL_OPEN: 'modal:open',
    MODAL_CLOSE: 'modal:close',
    
    // Товары
    PRODUCT_FILTER_APPLIED: 'product-filter:applied',
    PRODUCT_FILTER_RESET: 'product-filter:reset',
    PRODUCT_QUICKVIEW_OPEN: 'product-quickview:open',
    PRODUCT_QUICKVIEW_CLOSE: 'product-quickview:close',
    PRODUCT_REQUEST_OPEN: 'product-request:open',
    PRODUCT_REQUEST_SUBMIT: 'product-request:submit',
    
    // Формы
    FORM_SUBMIT_START: 'form:submit-start',
    FORM_SUBMIT_SUCCESS: 'form:submit-success',
    FORM_SUBMIT_ERROR: 'form:submit-error',
    FORM_VALIDATION_ERROR: 'form:validation-error',
    
    // Загрузка контента
    CONTENT_LOAD_START: 'content:load-start',
    CONTENT_LOAD_SUCCESS: 'content:load-success',
    CONTENT_LOAD_ERROR: 'content:load-error',
    
    // UI события
    WINDOW_RESIZE: 'window:resize',
    WINDOW_SCROLL: 'window:scroll',
    DOCUMENT_READY: 'document:ready',
    PAGE_LOADED: 'page:loaded',
    
    // Пользовательские действия
    USER_INTERACTION: 'user:interaction',
    USER_CLICK: 'user:click',
    USER_SCROLL: 'user:scroll',
    
    // Системные
    AJAX_START: 'ajax:start',
    AJAX_COMPLETE: 'ajax:complete',
    AJAX_ERROR: 'ajax:error',
    
    // WooCommerce
    WC_CART_UPDATED: 'wc:cart-updated',
    WC_PRODUCT_ADDED: 'wc:product-added',
    WC_CHECKOUT_INIT: 'wc:checkout-init'
};

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { eventManager, SEVERCON_EVENTS };
} else {
    window.SeverconEvents = eventManager;
    window.SEVERCON_EVENTS = SEVERCON_EVENTS;
}