/**
 * Модуль управления шапкой сайта
 */

import CONFIG from '../core/config.js';
import Utils from '../core/utils.js';
import { eventManager, SEVERCON_EVENTS } from '../core/events.js';

class HeaderManager {
    constructor() {
        this.header = null;
        this.mobileToggle = null;
        this.mobileMenu = null;
        this.mobileMenuClose = null;
        this.searchToggle = null;
        this.searchOverlay = null;
        this.searchClose = null;
        this.searchInput = null;
        this.requestBtn = null;
        this.requestOverlay = null;
        this.requestClose = null;
        
        this.isMobileMenuOpen = false;
        this.isSearchOpen = false;
        this.isRequestOpen = false;
        this.lastScrollTop = 0;
        
        this.init();
    }
    
    init() {
        this.cacheElements();
        
        if (this.header) {
            this.setupStickyHeader();
            this.setupMobileMenu();
            this.setupSearch();
            this.setupRequestForm();
            this.setupEventListeners();
            
            console.log('Header module initialized');
        }
    }
    
    cacheElements() {
        this.header = Utils.getElement(CONFIG.selectors.header);
        this.mobileToggle = Utils.getElement(CONFIG.selectors.mobileToggle);
        this.mobileMenu = Utils.getElement(CONFIG.selectors.mobileMenu);
        this.mobileMenuClose = Utils.getElement('#mobileMenuClose');
        this.searchToggle = Utils.getElement(CONFIG.selectors.searchToggle);
        this.searchOverlay = Utils.getElement(CONFIG.selectors.searchOverlay);
        this.searchClose = Utils.getElement('#searchClose');
        this.searchInput = Utils.getElement('#searchOverlay input[type="search"]');
        this.requestBtn = Utils.getElement(CONFIG.selectors.requestBtn);
        this.requestOverlay = Utils.getElement(CONFIG.selectors.requestOverlay);
        this.requestClose = Utils.getElement('#requestClose');
    }
    
    setupStickyHeader() {
        if (!this.header) return;
        
        const handleScroll = Utils.throttle(() => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const headerHeight = this.header.offsetHeight;
            
            if (scrollTop > 100) {
                this.header.classList.add(CONFIG.classes.sticky);
                document.body.style.paddingTop = `${headerHeight}px`;
                
                // Скрываем/показываем при скролле вниз/вверх
                if (scrollTop > this.lastScrollTop && scrollTop > 200) {
                    this.header.classList.add('header-hidden');
                } else {
                    this.header.classList.remove('header-hidden');
                }
            } else {
                this.header.classList.remove(CONFIG.classes.sticky);
                this.header.classList.remove('header-hidden');
                document.body.style.paddingTop = '0';
            }
            
            this.lastScrollTop = scrollTop;
        }, 100);
        
        // Инициализация при загрузке
        handleScroll();
        
        // Обработчик скролла
        window.addEventListener('scroll', handleScroll);
        
        // Очистка при ресайзе
        window.addEventListener('resize', () => {
            if (this.header.classList.contains(CONFIG.classes.sticky)) {
                const headerHeight = this.header.offsetHeight;
                document.body.style.paddingTop = `${headerHeight}px`;
            }
        });
    }
    
    setupMobileMenu() {
        if (!this.mobileToggle || !this.mobileMenu) return;
        
        // Открытие мобильного меню
        Utils.on(this.mobileToggle, 'click', (e) => {
            e.preventDefault();
            this.openMobileMenu();
        });
        
        // Закрытие мобильного меню
        if (this.mobileMenuClose) {
            Utils.on(this.mobileMenuClose, 'click', (e) => {
                e.preventDefault();
                this.closeMobileMenu();
            });
        }
        
        // Закрытие по клику на ссылку
        const menuLinks = this.mobileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            Utils.on(link, 'click', () => {
                this.closeMobileMenu();
            });
        });
        
        // Закрытие по клику вне меню
        Utils.on(document, 'click', (e) => {
            if (this.isMobileMenuOpen && 
                !this.mobileMenu.contains(e.target) && 
                !this.mobileToggle.contains(e.target)) {
                this.closeMobileMenu();
            }
        });
        
        // Закрытие по ESC
        Utils.on(document, 'keydown', (e) => {
            if (e.key === 'Escape' && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    }
    
    openMobileMenu() {
        if (!this.mobileToggle || !this.mobileMenu) return;
        
        this.closeAllModals(); // Закрываем другие модальные окна
        
        this.mobileToggle.classList.add(CONFIG.classes.active);
        this.mobileMenu.classList.add(CONFIG.classes.active);
        document.body.classList.add(CONFIG.classes.open.menu);
        
        // Блокируем скролл
        document.body.style.overflow = 'hidden';
        
        this.isMobileMenuOpen = true;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.MOBILE_MENU_OPEN, {
            element: this.mobileMenu
        });
        
        console.log('Mobile menu opened');
    }
    
    closeMobileMenu() {
        if (!this.mobileToggle || !this.mobileMenu) return;
        
        this.mobileToggle.classList.remove(CONFIG.classes.active);
        this.mobileMenu.classList.remove(CONFIG.classes.active);
        document.body.classList.remove(CONFIG.classes.open.menu);
        
        // Восстанавливаем скролл
        document.body.style.overflow = '';
        
        this.isMobileMenuOpen = false;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.MOBILE_MENU_CLOSE, {
            element: this.mobileMenu
        });
        
        console.log('Mobile menu closed');
    }
    
    setupSearch() {
        if (!this.searchToggle || !this.searchOverlay) return;
        
        // Открытие поиска
        Utils.on(this.searchToggle, 'click', (e) => {
            e.preventDefault();
            this.openSearch();
        });
        
        // Закрытие поиска
        if (this.searchClose) {
            Utils.on(this.searchClose, 'click', (e) => {
                e.preventDefault();
                this.closeSearch();
            });
        }
        
        // Закрытие по клику вне поиска
        Utils.on(this.searchOverlay, 'click', (e) => {
            if (e.target === this.searchOverlay) {
                this.closeSearch();
            }
        });
        
        // Закрытие по ESC
        Utils.on(document, 'keydown', (e) => {
            if (e.key === 'Escape' && this.isSearchOpen) {
                this.closeSearch();
            }
        });
        
        // Поиск при нажатии Enter
        if (this.searchInput) {
            Utils.on(this.searchInput, 'keydown', (e) => {
                if (e.key === 'Enter' && this.searchInput.value.trim()) {
                    this.performSearch(this.searchInput.value.trim());
                }
            });
        }
    }
    
    openSearch() {
        if (!this.searchOverlay) return;
        
        this.closeAllModals(); // Закрываем другие модальные окна
        
        this.searchOverlay.classList.add(CONFIG.classes.active);
        document.body.classList.add(CONFIG.classes.open.search);
        
        // Фокус на поле ввода
        setTimeout(() => {
            if (this.searchInput) {
                this.searchInput.focus();
                this.searchInput.select();
            }
        }, 300);
        
        this.isSearchOpen = true;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.SEARCH_OPEN, {
            element: this.searchOverlay
        });
        
        console.log('Search opened');
    }
    
    closeSearch() {
        if (!this.searchOverlay) return;
        
        this.searchOverlay.classList.remove(CONFIG.classes.active);
        document.body.classList.remove(CONFIG.classes.open.search);
        
        // Очищаем поле ввода
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        
        this.isSearchOpen = false;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.SEARCH_CLOSE, {
            element: this.searchOverlay
        });
        
        console.log('Search closed');
    }
    
    performSearch(query) {
        if (!query) return;
        
        console.log('Searching for:', query);
        
        // Здесь можно добавить AJAX поиск
        // Пока просто переходим на страницу поиска
        window.location.href = `${CONFIG.ajax.home_url}?s=${encodeURIComponent(query)}`;
    }
    
    setupRequestForm() {
        if (!this.requestBtn || !this.requestOverlay) return;
        
        // Открытие формы запроса
        Utils.on(this.requestBtn, 'click', (e) => {
            e.preventDefault();
            this.openRequestForm();
        });
        
        // Закрытие формы запроса
        if (this.requestClose) {
            Utils.on(this.requestClose, 'click', (e) => {
                e.preventDefault();
                this.closeRequestForm();
            });
        }
        
        // Закрытие по клику вне формы
        Utils.on(this.requestOverlay, 'click', (e) => {
            if (e.target === this.requestOverlay) {
                this.closeRequestForm();
            }
        });
        
        // Закрытие по ESC
        Utils.on(document, 'keydown', (e) => {
            if (e.key === 'Escape' && this.isRequestOpen) {
                this.closeRequestForm();
            }
        });
        
        // Обработка отправки формы (делегирование)
        eventManager.delegate('#requestOverlay .request-form', 'submit', (e, form) => {
            e.preventDefault();
            this.handleRequestFormSubmit(form);
        });
    }
    
    openRequestForm() {
        if (!this.requestOverlay) return;
        
        this.closeAllModals(); // Закрываем другие модальные окна
        
        this.requestOverlay.classList.add(CONFIG.classes.active);
        document.body.classList.add(CONFIG.classes.open.request);
        
        this.isRequestOpen = true;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.PRODUCT_REQUEST_OPEN, {
            element: this.requestOverlay
        });
        
        console.log('Request form opened');
    }
    
    closeRequestForm() {
        if (!this.requestOverlay) return;
        
        this.requestOverlay.classList.remove(CONFIG.classes.active);
        document.body.classList.remove(CONFIG.classes.open.request);
        
        // Сбрасываем форму
        const form = this.requestOverlay.querySelector('.request-form');
        if (form) {
            form.reset();
        }
        
        this.isRequestOpen = false;
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.MODAL_CLOSE, {
            element: this.requestOverlay,
            type: 'request'
        });
        
        console.log('Request form closed');
    }
    
    async handleRequestFormSubmit(form) {
        if (!form) return;
        
        // Показываем индикатор загрузки
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
        submitBtn.disabled = true;
        
        try {
            const formData = Utils.serializeForm(form);
            
            // Генерируем событие начала отправки
            eventManager.emit(SEVERCON_EVENTS.FORM_SUBMIT_START, {
                form,
                data: formData
            });
            
            // Здесь будет AJAX отправка формы
            await new Promise(resolve => setTimeout(resolve, 1500)); // Имитация
            
            // Успешная отправка
            eventManager.emit(SEVERCON_EVENTS.FORM_SUBMIT_SUCCESS, {
                form,
                data: formData
            });
            
            // Показываем сообщение об успехе
            Utils.showNotification('Заявка отправлена! Мы свяжемся с вами в ближайшее время.', 'success');
            
            // Закрываем форму через 2 секунды
            setTimeout(() => {
                this.closeRequestForm();
            }, 2000);
            
        } catch (error) {
            // Ошибка отправки
            eventManager.emit(SEVERCON_EVENTS.FORM_SUBMIT_ERROR, {
                form,
                error: error.message
            });
            
            Utils.showNotification('Ошибка при отправке заявки. Попробуйте еще раз.', 'error');
            
        } finally {
            // Восстанавливаем кнопку
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    setupEventListeners() {
        // Обработка кнопки запроса в мобильном меню
        eventManager.delegate('.mobile-request', 'click', (e, element) => {
            e.preventDefault();
            this.closeMobileMenu();
            setTimeout(() => this.openRequestForm(), 300);
        });
        
        // Обработка изменения размера окна
        window.addEventListener('resize', Utils.debounce(() => {
            if (window.innerWidth > 900 && this.isMobileMenuOpen) {
                this.closeMobileMenu();
            }
        }, 250));
    }
    
    closeAllModals() {
        this.closeMobileMenu();
        this.closeSearch();
        this.closeRequestForm();
        
        // Закрытие быстрого просмотра (если есть)
        const quickViewOverlay = Utils.getElement(CONFIG.selectors.quickViewOverlay);
        if (quickViewOverlay && quickViewOverlay.classList.contains(CONFIG.classes.active)) {
            quickViewOverlay.classList.remove(CONFIG.classes.active);
            document.body.classList.remove(CONFIG.classes.open.modal);
        }
    }
    
    /**
     * Публичные методы для внешнего использования
     */
    
    // Открыть мобильное меню
    openMobileMenu() {
        this.openMobileMenu();
    }
    
    // Закрыть мобильное меню
    closeMobileMenu() {
        this.closeMobileMenu();
    }
    
    // Открыть поиск
    openSearch() {
        this.openSearch();
    }
    
    // Закрыть поиск
    closeSearch() {
        this.closeSearch();
    }
    
    // Открыть форму запроса
    openRequestForm() {
        this.openRequestForm();
    }
    
    // Закрыть форму запроса
    closeRequestForm() {
        this.closeRequestForm();
    }
    
    // Получить состояние
    getState() {
        return {
            isMobileMenuOpen: this.isMobileMenuOpen,
            isSearchOpen: this.isSearchOpen,
            isRequestOpen: this.isRequestOpen,
            isSticky: this.header ? this.header.classList.contains(CONFIG.classes.sticky) : false
        };
    }
    
    // Деструктор
    destroy() {
        // Очищаем все обработчики
        this.closeAllModals();
        
        // Восстанавливаем стили
        document.body.style.paddingTop = '';
        document.body.style.overflow = '';
        
        console.log('Header module destroyed');
    }
}

// Создаем и экспортируем инстанс
const headerManager = new HeaderManager();

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = headerManager;
} else {
    // Глобальный доступ
    window.SeverconHeader = headerManager;
    
    // Инициализация при загрузке документа
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.SEVERCON_HEADER_INITIALIZED) {
            window.SeverconHeader.init();
            window.SEVERCON_HEADER_INITIALIZED = true;
        }
    });
}