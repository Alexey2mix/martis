/**
 * Модуль управления модальными окнами
 */

import CONFIG from '../core/config.js';
import Utils from '../core/utils.js';
import { eventManager, SEVERCON_EVENTS } from '../core/events.js';
import AjaxManager from '../core/ajax-manager.js';

class ModalManager {
    constructor() {
        this.modals = new Map();
        this.activeModal = null;
        this.modalStack = [];
        this.escapeHandlers = new Map();
        
        this.init();
    }
    
    init() {
        this.setupGlobalEventListeners();
        this.registerDefaultModals();
        
        console.log('Modal manager initialized');
    }
    
    setupGlobalEventListeners() {
        // Закрытие по ESC
        Utils.on(document, 'keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal);
            }
        });
        
        // Закрытие по клику на overlay
        Utils.on(document, 'click', (e) => {
            if (this.activeModal && e.target.classList.contains('modal-overlay')) {
                this.close(this.activeModal);
            }
        });
        
        // Делегирование для кнопок закрытия
        eventManager.delegate('[data-modal-close]', 'click', (e, element) => {
            e.preventDefault();
            const modalId = element.dataset.modalClose || this.activeModal;
            if (modalId) {
                this.close(modalId);
            }
        });
        
        // Делегирование для открытия модальных окон
        eventManager.delegate('[data-modal-open]', 'click', (e, element) => {
            e.preventDefault();
            const modalId = element.dataset.modalOpen;
            const options = this.parseOptions(element.dataset.modalOptions);
            
            if (modalId) {
                this.open(modalId, options);
            }
        });
    }
    
    registerDefaultModals() {
        // Регистрируем стандартные модальные окна
        this.register('search', {
            selector: '#searchOverlay',
            closeOnEsc: true,
            closeOnOverlayClick: true,
            removeOnClose: false,
            classes: {
                active: 'active',
                body: 'search-open'
            }
        });
        
        this.register('request', {
            selector: '#requestOverlay',
            closeOnEsc: true,
            closeOnOverlayClick: true,
            removeOnClose: false,
            classes: {
                active: 'active',
                body: 'request-open'
            }
        });
        
        this.register('quickview', {
            selector: '#quickViewOverlay',
            closeOnEsc: true,
            closeOnOverlayClick: true,
            removeOnClose: false,
            classes: {
                active: 'active',
                body: 'modal-open'
            },
            onOpen: (modal) => this.handleQuickViewOpen(modal),
            onClose: (modal) => this.handleQuickViewClose(modal)
        });
    }
    
    register(id, options) {
        const modal = {
            id,
            element: Utils.getElement(options.selector),
            options: {
                closeOnEsc: true,
                closeOnOverlayClick: true,
                removeOnClose: false,
                classes: {
                    active: 'active',
                    body: ''
                },
                onOpen: null,
                onClose: null,
                onBeforeOpen: null,
                onBeforeClose: null,
                ...options
            },
            isOpen: false
        };
        
        if (modal.element) {
            this.modals.set(id, modal);
            console.log(`Modal registered: ${id}`);
        } else {
            console.warn(`Modal element not found: ${options.selector}`);
        }
        
        return modal;
    }
    
    async open(id, customOptions = {}) {
        const modal = this.modals.get(id);
        
        if (!modal) {
            console.error(`Modal not found: ${id}`);
            return false;
        }
        
        if (modal.isOpen) {
            console.log(`Modal already open: ${id}`);
            return true;
        }
        
        // Вызываем колбэк перед открытием
        if (modal.options.onBeforeOpen) {
            const shouldOpen = await modal.options.onBeforeOpen(modal, customOptions);
            if (shouldOpen === false) return false;
        }
        
        // Закрываем предыдущее модальное окно
        if (this.activeModal && this.activeModal !== id) {
            await this.close(this.activeModal);
        }
        
        // Применяем кастомные опции
        const options = { ...modal.options, ...customOptions };
        
        // Показываем модальное окно
        modal.element.classList.add(options.classes.active);
        
        if (options.classes.body) {
            document.body.classList.add(options.classes.body);
        }
        
        // Блокируем скролл body
        document.body.style.overflow = 'hidden';
        
        modal.isOpen = true;
        this.activeModal = id;
        this.modalStack.push(id);
        
        // Фокус на первом фокусируемом элементе
        this.focusFirstElement(modal.element);
        
        // Вызываем колбэк после открытия
        if (options.onOpen) {
            await options.onOpen(modal, customOptions);
        }
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.MODAL_OPEN, {
            id,
            element: modal.element,
            options
        });
        
        console.log(`Modal opened: ${id}`);
        return true;
    }
    
    async close(id) {
        const modal = this.modals.get(id);
        
        if (!modal || !modal.isOpen) {
            return false;
        }
        
        // Вызываем колбэк перед закрытием
        if (modal.options.onBeforeClose) {
            const shouldClose = await modal.options.onBeforeClose(modal);
            if (shouldClose === false) return false;
        }
        
        // Скрываем модальное окно
        modal.element.classList.remove(modal.options.classes.active);
        
        if (modal.options.classes.body) {
            document.body.classList.remove(modal.options.classes.body);
        }
        
        // Восстанавливаем скролл body если нет других открытых модалок
        if (this.modalStack.length === 1) {
            document.body.style.overflow = '';
        }
        
        modal.isOpen = false;
        
        // Удаляем из стека
        const index = this.modalStack.indexOf(id);
        if (index > -1) {
            this.modalStack.splice(index, 1);
        }
        
        // Обновляем активное модальное окно
        this.activeModal = this.modalStack.length > 0 
            ? this.modalStack[this.modalStack.length - 1] 
            : null;
        
        // Возвращаем фокус
        this.restoreFocus();
        
        // Вызываем колбэк после закрытия
        if (modal.options.onClose) {
            await modal.options.onClose(modal);
        }
        
        // Удаляем модальное окно из DOM если нужно
        if (modal.options.removeOnClose && modal.element.parentNode) {
            modal.element.remove();
            this.modals.delete(id);
        }
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.MODAL_CLOSE, {
            id,
            element: modal.element
        });
        
        console.log(`Modal closed: ${id}`);
        return true;
    }
    
    closeAll() {
        const closePromises = [];
        
        this.modals.forEach(modal => {
            if (modal.isOpen) {
                closePromises.push(this.close(modal.id));
            }
        });
        
        return Promise.all(closePromises);
    }
    
    focusFirstElement(modalElement) {
        const focusableElements = modalElement.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        } else {
            modalElement.setAttribute('tabindex', '-1');
            modalElement.focus();
        }
    }
    
    restoreFocus() {
        // Возвращаем фокус к элементу, который открыл модальное окно
        const lastFocused = document.activeElement;
        const modalOpener = document.querySelector(`[data-modal-open="${this.activeModal}"]`);
        
        if (modalOpener) {
            modalOpener.focus();
        } else if (lastFocused && lastFocused !== document.body) {
            lastFocused.focus();
        }
    }
    
    async handleQuickViewOpen(modal) {
        const productId = modal.productId;
        
        if (!productId) {
            console.error('No product ID for quick view');
            return false;
        }
        
        // Показываем индикатор загрузки
        const content = modal.element.querySelector('.quick-view-content');
        if (content) {
            content.innerHTML = `
                <div class="quick-view-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Загрузка товара...</p>
                </div>
            `;
        }
        
        try {
            // Загружаем данные товара
            const result = await AjaxManager.getQuickView(productId);
            
            if (result.success && result.data) {
                if (content) {
                    content.innerHTML = result.data;
                    
                    // Инициализируем галерею
                    this.initQuickViewGallery(modal.element);
                    
                    // Инициализируем кнопки
                    this.initQuickViewButtons(modal.element);
                }
            } else {
                throw new Error(result.error || 'Failed to load product');
            }
            
        } catch (error) {
            console.error('Quick view error:', error);
            
            if (content) {
                content.innerHTML = `
                    <div class="quick-view-error">
                        <p>Ошибка загрузки товара</p>
                        <button class="btn btn-primary" onclick="window.SeverconModals.close('quickview')">
                            Закрыть
                        </button>
                    </div>
                `;
            }
        }
    }
    
    handleQuickViewClose(modal) {
        // Очищаем содержимое при закрытии
        const content = modal.element.querySelector('.quick-view-content');
        if (content) {
            content.innerHTML = '';
        }
        
        // Очищаем productId
        delete modal.productId;
    }
    
    initQuickViewGallery(modalElement) {
        const thumbs = modalElement.querySelectorAll('.quick-view-thumb');
        const mainImage = modalElement.querySelector('.quick-view-image-main img');
        
        if (!thumbs.length || !mainImage) return;
        
        thumbs.forEach(thumb => {
            Utils.on(thumb, 'click', () => {
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                
                const img = thumb.querySelector('img');
                if (img) {
                    const newSrc = img.src
                        .replace('-300x300', '')
                        .replace('-150x150', '')
                        .replace('-100x100', '');
                    mainImage.src = newSrc;
                }
            });
        });
    }
    
    initQuickViewButtons(modalElement) {
        // Кнопка "Запросить цену" в быстром просмотре
        const requestBtn = modalElement.querySelector('.quick-view-request');
        if (requestBtn) {
            Utils.on(requestBtn, 'click', (e) => {
                e.preventDefault();
                
                const productId = requestBtn.dataset.productId;
                const productName = requestBtn.dataset.productName;
                
                // Закрываем быстрый просмотр
                this.close('quickview');
                
                // Открываем форму запроса
                setTimeout(() => {
                    this.openRequestFormWithProduct(productId, productName);
                }, 300);
            });
        }
        
        // Навигационные стрелки
        const navArrows = modalElement.querySelectorAll('.nav-arrow');
        navArrows.forEach(arrow => {
            Utils.on(arrow, 'click', (e) => {
                e.preventDefault();
                
                const productId = arrow.dataset.productId;
                if (productId) {
                    this.openQuickView(productId);
                }
            });
        });
    }
    
    openQuickView(productId) {
        const modal = this.modals.get('quickview');
        
        if (modal) {
            modal.productId = productId;
            this.open('quickview');
        }
    }
    
    openRequestFormWithProduct(productId, productName) {
        this.open('request').then(() => {
            // Устанавливаем данные товара в форму
            setTimeout(() => {
                const messageField = Utils.getElement('#request-message');
                if (messageField) {
                    messageField.value = `Запрос цены на товар: ${productName}`;
                    messageField.focus();
                }
                
                // Можно также установить скрытые поля
                const productIdField = Utils.getElement('#request-product-id');
                if (productIdField) {
                    productIdField.value = productId;
                }
            }, 300);
        });
    }
    
    parseOptions(optionsString) {
        if (!optionsString) return {};
        
        try {
            return JSON.parse(optionsString);
        } catch (error) {
            console.error('Failed to parse modal options:', error);
            return {};
        }
    }
    
    /**
     * Публичные методы
     */
    
    // Открыть модальное окно
    open(id, options = {}) {
        return this.open(id, options);
    }
    
    // Закрыть модальное окно
    close(id) {
        return this.close(id);
    }
    
    // Закрыть все модальные окна
    closeAll() {
        return this.closeAll();
    }
    
    // Проверить, открыто ли модальное окно
    isOpen(id) {
        const modal = this.modals.get(id);
        return modal ? modal.isOpen : false;
    }
    
    // Получить активное модальное окно
    getActiveModal() {
        return this.activeModal;
    }
    
    // Открыть быстрый просмотр товара
    openQuickView(productId) {
        this.openQuickView(productId);
    }
    
    // Деструктор
    destroy() {
        this.closeAll();
        this.modals.clear();
        this.escapeHandlers.clear();
        this.modalStack = [];
        
        console.log('Modal manager destroyed');
    }
}

// Создаем и экспортируем инстанс
const modalManager = new ModalManager();

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = modalManager;
} else {
    // Глобальный доступ
    window.SeverconModals = modalManager;
    
    // Инициализация при загрузке документа
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.SEVERCON_MODALS_INITIALIZED) {
            window.SeverconModals.init();
            window.SEVERCON_MODALS_INITIALIZED = true;
        }
    });
}