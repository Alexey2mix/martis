/**
 * Основной файл JavaScript темы Severcon
 * Точка входа для инициализации всех модулей
 */

// Проверка поддержки необходимых возможностей
if (!('Promise' in window)) {
    console.error('This browser does not support Promises. Please update your browser.');
}

// Основной класс темы
class SeverconTheme {
    constructor() {
        this.modules = new Map();
        this.isInitialized = false;
        this.config = window.severcon_ajax || {};
        
        // Инициализация при полной загрузке DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }
    
    async init() {
        if (this.isInitialized) return;
        
        console.log('Initializing Severcon theme...');
        
        try {
            // 1. Инициализация утилит
            await this.initUtils();
            
            // 2. Инициализация ядра
            await this.initCore();
            
            // 3. Инициализация модулей
            await this.initModules();
            
            // 4. Инициализация компонентов
            await this.initComponents();
            
            this.isInitialized = true;
            
            // Генерируем событие готовности
            this.dispatchEvent('severcon:ready', {
                timestamp: new Date().toISOString(),
                modules: Array.from(this.modules.keys())
            });
            
            console.log('Severcon theme initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize Severcon theme:', error);
            this.handleInitError(error);
        }
    }
    
    async initUtils() {
        // Загружаем утилиты если они еще не загружены
        if (!window.SeverconUtils) {
            // В продакшене утилиты должны быть уже загружены
            console.warn('SeverconUtils not found. Loading fallback...');
            await this.loadFallbackUtils();
        }
        
        // Проверяем поддержку необходимых API
        this.checkBrowserSupport();
    }
    
    async initCore() {
        // Инициализация менеджера событий
        if (!window.SeverconEvents) {
            console.warn('SeverconEvents not found');
        }
        
        // Инициализация AJAX менеджера
        if (!window.SeverconAjax) {
            console.warn('SeverconAjax not found');
        }
        
        // Инициализация конфигурации
        if (!window.SEVERCON_CONFIG) {
            console.warn('SEVERCON_CONFIG not found');
        }
    }
    
    async initModules() {
        const modulesToLoad = [];
        
        // Определяем, какие модули нужны на текущей странице
        if (this.isElementPresent('#mainHeader')) {
            modulesToLoad.push('header');
        }
        
        if (this.isElementPresent('.filters-container') || 
            this.isElementPresent('.compact-filters')) {
            modulesToLoad.push('filters');
        }
        
        if (this.isElementPresent('#quickViewOverlay') || 
            this.isElementPresent('.quick-view-btn')) {
            modulesToLoad.push('modals');
        }
        
        if (this.isElementPresent('.vertical-slider')) {
            modulesToLoad.push('sliders');
        }
        
        if (this.isElementPresent('.product-gallery') || 
            this.isElementPresent('.product-thumbnails')) {
            modulesToLoad.push('gallery');
        }
        
        if (this.isElementPresent('.news-grid') || 
            this.isElementPresent('#load-more-news')) {
            modulesToLoad.push('news');
        }
        
        // Загружаем и инициализируем модули
        for (const moduleName of modulesToLoad) {
            try {
                await this.loadModule(moduleName);
            } catch (error) {
                console.error(`Failed to load module ${moduleName}:`, error);
            }
        }
    }
    
    async initComponents() {
        // Инициализация компонентов, которые не требуют отдельных модулей
        
        // 1. Кнопки "Запросить цену"
        this.initProductRequestButtons();
        
        // 2. Галерея товара на странице товара
        this.initProductGallery();
        
        // 3. Вертикальный слайдер
        this.initVerticalSlider();
        
        // 4. Кнопка "Показать все"
        this.initViewAllButton();
        
        // 5. Обработчики скролла и ресайза
        this.initWindowHandlers();
        
        // 6. Ленивая загрузка изображений
        this.initLazyLoading();
        
        // 7. Формы
        this.initForms();
    }
    
    async loadModule(moduleName) {
        // В продакшене модули должны быть уже загружены
        // Здесь только инициализация
        
        switch (moduleName) {
            case 'header':
                if (window.SeverconHeader && typeof window.SeverconHeader.init === 'function') {
                    window.SeverconHeader.init();
                    this.modules.set('header', window.SeverconHeader);
                }
                break;
                
            case 'filters':
                // Определяем тип фильтров
                if (this.isElementPresent('.compact-filters')) {
                    if (window.SeverconCompactFilters) {
                        this.modules.set('filters', window.SeverconCompactFilters);
                    } else if (window.SeverconBaseFilters) {
                        this.modules.set('filters', window.SeverconBaseFilters);
                    }
                } else if (this.isElementPresent('.filters-container')) {
                    if (window.SeverconBaseFilters) {
                        this.modules.set('filters', window.SeverconBaseFilters);
                    }
                }
                break;
                
            case 'modals':
                if (window.SeverconModals && typeof window.SeverconModals.init === 'function') {
                    window.SeverconModals.init();
                    this.modules.set('modals', window.SeverconModals);
                }
                break;
                
            case 'sliders':
                // Инициализация будет в компонентах
                break;
                
            case 'gallery':
                // Инициализация будет в компонентах
                break;
                
            case 'news':
                if (window.SeverconNews) {
                    this.modules.set('news', window.SeverconNews);
                }
                break;
        }
    }
    
    initProductRequestButtons() {
        // Делегирование для кнопок запроса цены
        
        // Для кнопок на странице товара
        document.addEventListener('click', (e) => {
            const requestBtn = e.target.closest('.request-product-btn');
            if (requestBtn) {
                e.preventDefault();
                this.handleProductRequest(
                    requestBtn.dataset.productId,
                    requestBtn.dataset.productName
                );
            }
        });
        
        // Для кнопок на странице категории
        document.addEventListener('click', (e) => {
            const priceBtn = e.target.closest('.request-price-btn');
            if (priceBtn) {
                e.preventDefault();
                this.handleProductRequest(
                    priceBtn.dataset.productId,
                    priceBtn.dataset.productName
                );
            }
        });
    }
    
    handleProductRequest(productId, productName) {
        // Открываем форму запроса
        if (window.SeverconModals) {
            window.SeverconModals.openRequestFormWithProduct(productId, productName);
        } else if (window.SeverconHeader) {
            window.SeverconHeader.openRequestForm();
            
            // Устанавливаем данные товара
            setTimeout(() => {
                const messageField = document.getElementById('request-message');
                if (messageField) {
                    messageField.value = `Запрос цены на товар: ${productName}`;
                    messageField.focus();
                }
            }, 300);
        }
    }
    
    initProductGallery() {
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.querySelector('.main-product-image img');
        
        if (!thumbnails.length || !mainImage) return;
        
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const img = this.querySelector('img');
                if (img) {
                    const newSrc = img.src
                        .replace('-150x150', '')
                        .replace('-300x300', '')
                        .replace('-100x100', '');
                    mainImage.src = newSrc;
                    
                    // Генерируем событие
                    this.dispatchEvent('product:gallery-change', {
                        imageSrc: newSrc
                    });
                }
            });
        });
    }
    
    initVerticalSlider() {
        const slider = document.querySelector('.vertical-slider');
        if (!slider) return;
        
        const slides = slider.querySelectorAll('.slide');
        const navUp = slider.querySelector('.nav-up');
        const navDown = slider.querySelector('.nav-down');
        const currentSlideElement = slider.querySelector('.current-slide');
        
        if (!slides.length || !navUp || !navDown || !currentSlideElement) return;
        
        let currentSlide = 0;
        
        function updateCounter() {
            const slideNumber = (currentSlide + 1).toString().padStart(2, '0');
            currentSlideElement.textContent = slideNumber;
        }
        
        function showSlide(index) {
            slides.forEach(slide => {
                slide.classList.remove('active', 'prev');
            });
            
            slides[currentSlide].classList.add('prev');
            currentSlide = index;
            slides[currentSlide].classList.add('active');
            updateCounter();
        }
        
        function nextSlide() {
            let next = currentSlide + 1;
            if (next >= slides.length) next = 0;
            showSlide(next);
        }
        
        function prevSlide() {
            let prev = currentSlide - 1;
            if (prev < 0) prev = slides.length - 1;
            showSlide(prev);
        }
        
        navDown.addEventListener('click', nextSlide);
        navUp.addEventListener('click', prevSlide);
        
        // Автопрокрутка (опционально)
        let autoplayInterval;
        
        function startAutoplay() {
            stopAutoplay();
            autoplayInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }
        
        // Стартуем автопрокрутку
        startAutoplay();
        
        // Останавливаем при наведении
        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', startAutoplay);
        
        // Инициализация
        updateCounter();
        
        // Сохраняем ссылки для внешнего доступа
        this.slider = {
            next: nextSlide,
            prev: prevSlide,
            goTo: showSlide,
            startAutoplay,
            stopAutoplay,
            currentIndex: () => currentSlide,
            totalSlides: () => slides.length
        };
    }
    
    initViewAllButton() {
        const viewAllButton = document.querySelector('.view-all-products a');
        if (!viewAllButton) return;
        
        viewAllButton.addEventListener('click', function(e) {
            const button = this;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
            button.style.pointerEvents = 'none';
            
            // Восстанавливаем через 3 секунды на случай ошибки
            setTimeout(() => {
                button.innerHTML = button.dataset.originalText || 'Показать все';
                button.style.pointerEvents = '';
            }, 3000);
        });
        
        // Сохраняем оригинальный текст
        viewAllButton.dataset.originalText = viewAllButton.textContent;
    }
    
    initWindowHandlers() {
        // Обработчики скролла и ресайза с throttling
        
        const handleScroll = () => {
            // Можно добавить общую логику скролла здесь
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Генерируем событие
            this.dispatchEvent('window:scroll', { scrollTop });
        };
        
        const handleResize = () => {
            // Обновляем флаги устройства
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 992;
            const isDesktop = window.innerWidth >= 992;
            
            // Генерируем событие
            this.dispatchEvent('window:resize', {
                width: window.innerWidth,
                height: window.innerHeight,
                isMobile,
                isTablet,
                isDesktop
            });
        };
        
        // Используем throttle для производительности
        window.addEventListener('scroll', this.throttle(handleScroll, 100));
        window.addEventListener('resize', this.debounce(handleResize, 250));
        
        // Инициализация при загрузке
        handleResize();
    }
    
    initLazyLoading() {
        // Ленивая загрузка изображений
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img[data-src]');
            
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }
    
    initForms() {
        // Общая инициализация форм
        
        // Валидация форм
        document.addEventListener('submit', (e) => {
            const form = e.target;
            
            if (form.classList.contains('severcon-form')) {
                e.preventDefault();
                this.handleFormSubmit(form);
            }
        });
        
        // Маска для телефона
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.formatPhoneInput(e.target);
            });
        });
    }
    
    async handleFormSubmit(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Показываем индикатор загрузки
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(form);
            
            // Можно добавить валидацию
            const isValid = this.validateForm(form);
            if (!isValid) {
                throw new Error('Form validation failed');
            }
            
            // Отправка формы
            const response = await fetch(form.action || this.config.ajax_url, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                // Успешная отправка
                this.showFormSuccess(form, result.message);
            } else {
                throw new Error(result.message || 'Form submission failed');
            }
            
        } catch (error) {
            console.error('Form submission error:', error);
            this.showFormError(form, error.message);
            
        } finally {
            // Восстанавливаем кнопку
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    validateForm(form) {
        let isValid = true;
        const errors = [];
        
        // Простая валидация обязательных полей
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                errors.push(`Поле "${field.name}" обязательно для заполнения`);
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        // Валидация email
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                isValid = false;
                errors.push('Введите корректный email');
                field.classList.add('error');
            }
        });
        
        // Показ ошибок
        if (errors.length > 0) {
            this.showFormErrors(form, errors);
        }
        
        return isValid;
    }
    
    showFormSuccess(form, message) {
        // Скрываем форму
        form.style.display = 'none';
        
        // Показываем сообщение об успехе
        const successMessage = document.createElement('div');
        successMessage.className = 'form-success-message';
        successMessage.innerHTML = `
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Спасибо!</h3>
            <p>${message || 'Ваше сообщение успешно отправлено.'}</p>
        `;
        
        form.parentNode.appendChild(successMessage);
        
        // Автоскрытие через 5 секунд
        setTimeout(() => {
            successMessage.remove();
            form.style.display = '';
            form.reset();
        }, 5000);
    }
    
    showFormError(form, errorMessage) {
        // Показываем ошибку
        let errorContainer = form.querySelector('.form-error-container');
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'form-error-container';
            form.prepend(errorContainer);
        }
        
        errorContainer.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span>${errorMessage || 'Произошла ошибка. Попробуйте еще раз.'}</span>
            </div>
        `;
        
        // Автоскрытие через 5 секунд
        setTimeout(() => {
            errorContainer.remove();
        }, 5000);
    }
    
    showFormErrors(form, errors) {
        const errorContainer = document.createElement('div');
        errorContainer.className = 'form-errors-list';
        
        errors.forEach(error => {
            const errorElement = document.createElement('div');
            errorElement.className = 'form-error-item';
            errorElement.textContent = error;
            errorContainer.appendChild(errorElement);
        });
        
        form.prepend(errorContainer);
        
        // Автоскрытие
        setTimeout(() => {
            errorContainer.remove();
        }, 5000);
    }
    
    formatPhoneInput(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 0) {
            if (value[0] === '8') {
                value = '7' + value.substring(1);
            }
            
            let formatted = '+7 ';
            
            if (value.length > 1) {
                formatted += '(' + value.substring(1, 4);
            }
            if (value.length >= 4) {
                formatted += ') ' + value.substring(4, 7);
            }
            if (value.length >= 7) {
                formatted += '-' + value.substring(7, 9);
            }
            if (value.length >= 9) {
                formatted += '-' + value.substring(9, 11);
            }
            
            input.value = formatted;
        }
    }
    
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    isElementPresent(selector) {
        return document.querySelector(selector) !== null;
    }
    
    loadFallbackUtils() {
        // Fallback утилиты если основные не загрузились
        window.SeverconUtils = {
            getElement: (selector) => document.querySelector(selector),
            getElements: (selector) => document.querySelectorAll(selector),
            elementExists: (selector) => document.querySelector(selector) !== null,
            on: (element, event, handler) => element.addEventListener(event, handler),
            throttle: (func, limit) => {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            },
            debounce: (func, wait) => {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        };
    }
    
    checkBrowserSupport() {
        const requiredFeatures = [
            'querySelector',
            'addEventListener',
            'classList',
            'Promise',
            'fetch',
            'IntersectionObserver'
        ];
        
        const unsupported = [];
        
        requiredFeatures.forEach(feature => {
            if (!(feature in window)) {
                unsupported.push(feature);
            }
        });
        
        if (unsupported.length > 0) {
            console.warn('Unsupported features:', unsupported);
            
            // Можно показать предупреждение пользователю
            if (unsupported.includes('Promise') || unsupported.includes('fetch')) {
                this.showBrowserWarning();
            }
        }
    }
    
    showBrowserWarning() {
        const warning = document.createElement('div');
        warning.className = 'browser-warning';
        warning.innerHTML = `
            <div class="warning-content">
                <p>Ваш браузер устарел. Некоторые функции сайта могут работать некорректно.</p>
                <p>Рекомендуем обновить браузер или использовать современный браузер.</p>
                <button class="close-warning">×</button>
            </div>
        `;
        
        document.body.appendChild(warning);
        
        // Кнопка закрытия
        const closeBtn = warning.querySelector('.close-warning');
        closeBtn.addEventListener('click', () => {
            warning.remove();
        });
        
        // Автоскрытие через 10 секунд
        setTimeout(() => {
            if (warning.parentNode) {
                warning.remove();
            }
        }, 10000);
    }
    
    handleInitError(error) {
        console.error('Theme initialization error:', error);
        
        // Показываем пользовательское сообщение
        const errorMessage = document.createElement('div');
        errorMessage.className = 'theme-error-message';
        errorMessage.innerHTML = `
            <div class="error-content">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Произошла ошибка при загрузке страницы. Пожалуйста, обновите страницу.</p>
                <button onclick="window.location.reload()">Обновить страницу</button>
            </div>
        `;
        
        document.body.appendChild(errorMessage);
    }
    
    dispatchEvent(name, detail = {}) {
        const event = new CustomEvent(name, {
            detail,
            bubbles: true,
            cancelable: true
        });
        
        document.dispatchEvent(event);
    }
    
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Публичные методы
     */
    
    // Получить модуль по имени
    getModule(name) {
        return this.modules.get(name);
    }
    
    // Получить все модули
    getAllModules() {
        return Array.from(this.modules.keys());
    }
    
    // Проверить инициализацию
    isInitialized() {
        return this.isInitialized;
    }
    
    // Переинициализация
    reinitialize() {
        this.isInitialized = false;
        return this.init();
    }
    
    // Деструктор
    destroy() {
        // Останавливаем все модули
        this.modules.forEach(module => {
            if (module && typeof module.destroy === 'function') {
                module.destroy();
            }
        });
        
        this.modules.clear();
        this.isInitialized = false;
        
        console.log('Severcon theme destroyed');
    }
}

// Создаем глобальный инстанс темы
window.SeverconTheme = new SeverconTheme();

// Экспорт для модулей
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.SeverconTheme;
}

// Инициализация при загрузке
(function() {
    // Защита от повторной инициализации
    if (!window.SEVERCON_THEME_INITIALIZED) {
        window.SeverconTheme.init();
        window.SEVERCON_THEME_INITIALIZED = true;
    }
})();