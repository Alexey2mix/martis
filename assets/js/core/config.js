/**
 * Конфигурация темы Severcon
 * Централизованные настройки для всей JavaScript системы
 */

(function() {
    'use strict';
    
    // Проверяем, не загружен ли конфиг уже
    if (window.severconConfig) {
        console.warn('Severcon config already loaded');
        return;
    }
    
    /**
     * Основной объект конфигурации
     */
    const severconConfig = {
        
        // ============================================================================
        // 1. СЕЛЕКТОРЫ DOM ЭЛЕМЕНТОВ
        // ============================================================================
        selectors: {
            // Основные контейнеры
            body: 'body',
            header: '.site-header',
            footer: '.site-footer',
            mainContent: '#main',
            
            // Фильтры
            filtersContainer: '.severcon-filters-container',
            filterGroup: '.filter-group',
            filterCheckbox: '.filter-checkbox',
            filterItem: '.filter-item',
            filterSearchInput: '.filter-search-input',
            applyFiltersBtn: '.apply-filters',
            clearFiltersBtn: '.clear-all-filters',
            resetFiltersBtn: '.reset-filters',
            
            // Товары
            productsContainer: '.products-container',
            productsGrid: '.products',
            productCard: '.product-card',
            noProductsFound: '.no-products-found',
            
            // Пагинация
            paginationContainer: '.pagination',
            paginationLinks: '.page-numbers',
            loadMoreBtn: '.load-more-products',
            
            // Сортировка
            sortSelect: '.orderby',
            
            // Модальные окна
            modal: '.modal',
            modalClose: '.modal-close',
            modalContent: '.modal-content',
            quickViewModal: '#quick-view-modal',
            
            // Формы
            searchForm: '.search-form',
            searchInput: '.search-field',
            
            // Мобильное меню
            mobileMenuToggle: '.mobile-menu-toggle',
            mobileMenu: '.mobile-menu',
            
            // Хлебные крошки
            breadcrumbs: '.breadcrumbs',
            
            // Уведомления
            notices: '.severcon-notice',
            ajaxLoader: '.ajax-loader'
        },
        
        // ============================================================================
        // 2. AJAX КОНФИГУРАЦИЯ
        // ============================================================================
        ajax: {
            // Основной endpoint (новый роутер)
            routerEndpoint: 'severcon_router',
            
            // Legacy endpoints для обратной совместимости
            legacyEndpoints: {
                filterProducts: 'filter_category_products',
                updateCounts: 'update_filter_counts',
                quickView: 'severcon_quick_view', // если есть
                loadNews: 'severcon_load_news'    // если есть
            },
            
            // Настройки запросов
            timeout: 30000, // 30 секунд
            maxRetries: 2,
            
            // Ключи для localStorage кэша
            cacheKeys: {
                filterCounts: 'severcon_filter_counts_',
                filterState: 'severcon_filter_state_',
                quickView: 'severcon_quick_view_'
            },
            
            // Время жизни кэша (в миллисекундах)
            cacheTTL: {
                short: 5 * 60 * 1000,    // 5 минут
                medium: 30 * 60 * 1000,  // 30 минут
                long: 2 * 60 * 60 * 1000 // 2 часа
            }
        },
        
        // ============================================================================
        // 3. ФИЛЬТРЫ
        // ============================================================================
        filters: {
            // Задержка перед применением фильтров (debounce)
            applyDebounce: 300, // мс
            
            // Задержка перед обновлением счетчиков
            countsDebounce: 500, // мс
            
            // Анимации
            animationDuration: 300,
            
            // Настройки отображения
            showZeroCounts: false, // Показывать значения с 0 товаров
            maxVisibleItems: 10,   // Максимум видимых элементов до "Показать еще"
            
            // Селекторы для динамического обновления
            countSelector: '.filter-item-count',
            disabledClass: 'disabled',
            activeClass: 'active',
            loadingClass: 'loading'
        },
        
        // ============================================================================
        // 4. ПАГИНАЦИЯ И ЗАГРУЗКА
        // ============================================================================
        pagination: {
            // Тип пагинации: 'links' | 'load-more' | 'infinite'
            type: 'links',
            
            // Настройки Load More
            loadMoreText: 'Загрузить еще',
            loadingText: 'Загрузка...',
            noMoreText: 'Товары загружены',
            
            // Infinite Scroll
            infiniteScrollOffset: 200, // Пикселей до конца для подгрузки
            throttleDelay: 100         // Задержка проверки прокрутки
        },
        
        // ============================================================================
        // 5. БЫСТРЫЙ ПРОСМОТР (QUICK VIEW)
        // ============================================================================
        quickView: {
            // Настройки модального окна
            animationDuration: 300,
            closeOnEsc: true,
            closeOnOverlayClick: true,
            
            // Селекторы внутри модального окна
            innerSelectors: {
                image: '.quick-view-image',
                title: '.quick-view-title',
                price: '.quick-view-price',
                description: '.quick-view-description',
                addToCart: '.quick-view-add-to-cart',
                variations: '.quick-view-variations'
            }
        },
        
        // ============================================================================
        // 6. АДАПТИВНОСТЬ
        // ============================================================================
        breakpoints: {
            xs: 0,      // Mobile
            sm: 576,    // Mobile landscape
            md: 768,    // Tablet
            lg: 992,    // Desktop
            xl: 1200,   // Large desktop
            xxl: 1400   // Extra large
        },
        
        // ============================================================================
        // 7. ПУТИ И URL
        // ============================================================================
        paths: {
            images: '/wp-content/themes/severcon/assets/images/',
            icons: '/wp-content/themes/severcon/assets/icons/',
            templates: '/wp-content/themes/severcon/templates/'
        },
        
        // ============================================================================
        // 8. ЛОКАЛИЗАЦИЯ
        // ============================================================================
        i18n: {
            // Общие
            loading: 'Загрузка...',
            error: 'Произошла ошибка',
            tryAgain: 'Попробовать еще раз',
            
            // Фильтры
            filters: 'Фильтры',
            clearAll: 'Очистить все',
            applyFilters: 'Применить фильтры',
            resetFilters: 'Сбросить',
            noFilters: 'Фильтры не настроены',
            
            // Товары
            productsFound: 'Найдено товаров: {count}',
            noProducts: 'Товаров не найдено',
            addToCart: 'В корзину',
            inStock: 'В наличии',
            outOfStock: 'Нет в наличии',
            
            // Пагинация
            previous: 'Назад',
            next: 'Вперед',
            page: 'Страница {current} из {total}',
            
            // Поиск
            searchPlaceholder: 'Поиск товаров...',
            noResults: 'Ничего не найдено',
            
            // Модальные окна
            closeModal: 'Закрыть'
        },
        
        // ============================================================================
        // 9. НАСТРОЙКИ РАЗРАБОТКИ
        // ============================================================================
        debug: {
            enabled: false, // Включать только при разработке!
            logLevel: 'error', // 'error' | 'warn' | 'info' | 'debug'
            
            // Что логировать
            logAjaxRequests: true,
            logAjaxResponses: true,
            logFilterChanges: true,
            logEvents: false,
            
            // Визуальные индикаторы
            showLoadingIndicators: true,
            highlightActiveElements: false
        },
        
        // ============================================================================
        // 10. КЛАССЫ CSS
        // ============================================================================
        classes: {
            // Состояния
            active: 'is-active',
            disabled: 'is-disabled',
            hidden: 'is-hidden',
            visible: 'is-visible',
            loading: 'is-loading',
            error: 'has-error',
            success: 'has-success',
            
            // Анимации
            fadeIn: 'fade-in',
            fadeOut: 'fade-out',
            slideDown: 'slide-down',
            slideUp: 'slide-up',
            
            // Модификаторы
            mobile: 'is-mobile',
            tablet: 'is-tablet',
            desktop: 'is-desktop'
        },
        
        // ============================================================================
        // 11. УТИЛИТЫ
        // ============================================================================
        utils: {
            // Настройки дебаунса по умолчанию
            defaultDebounce: 250,
            
            // Настройки троттлинга
            defaultThrottle: 100,
            
            // Анимации
            defaultAnimationDuration: 300,
            
            // Проверка поддержки браузером
            supports: {
                passiveListeners: (function() {
                    let supportsPassive = false;
                    try {
                        const opts = Object.defineProperty({}, 'passive', {
                            get: function() {
                                supportsPassive = true;
                            }
                        });
                        window.addEventListener('test', null, opts);
                        window.removeEventListener('test', null, opts);
                    } catch (e) {}
                    return supportsPassive;
                })(),
                
                intersectionObserver: 'IntersectionObserver' in window,
                resizeObserver: 'ResizeObserver' in window,
                mutationObserver: 'MutationObserver' in window
            }
        }
    };
    
    // ============================================================================
    // ИНИЦИАЛИЗАЦИЯ И ЭКСПОРТ
    // ============================================================================
    
    /**
     * Получение конфигурации
     */
    window.getSeverconConfig = function() {
        return JSON.parse(JSON.stringify(severconConfig)); // Возвращаем копию
    };
    
    /**
     * Обновление конфигурации
     */
    window.updateSeverconConfig = function(newConfig) {
        // Рекурсивное слияние объектов
        function deepMerge(target, source) {
            for (const key in source) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    if (!target[key]) target[key] = {};
                    deepMerge(target[key], source[key]);
                } else {
                    target[key] = source[key];
                }
            }
            return target;
        }
        
        deepMerge(severconConfig, newConfig);
        return getSeverconConfig();
    };
    
    /**
     * Получение конкретного значения из конфига
     */
    window.getSeverconConfigValue = function(path, defaultValue = null) {
        const keys = path.split('.');
        let result = severconConfig;
        
        for (const key of keys) {
            if (result && typeof result === 'object' && key in result) {
                result = result[key];
            } else {
                return defaultValue;
            }
        }
        
        return result !== undefined ? result : defaultValue;
    };
    
    /**
     * Проверка текущего брейкпоинта
     */
    window.getCurrentBreakpoint = function() {
        const width = window.innerWidth;
        const breakpoints = severconConfig.breakpoints;
        
        if (width >= breakpoints.xxl) return 'xxl';
        if (width >= breakpoints.xl) return 'xl';
        if (width >= breakpoints.lg) return 'lg';
        if (width >= breakpoints.md) return 'md';
        if (width >= breakpoints.sm) return 'sm';
        return 'xs';
    };
    
    /**
     * Проверка является ли устройство мобильным
     */
    window.isMobile = function() {
        const breakpoint = getCurrentBreakpoint();
        return breakpoint === 'xs' || breakpoint === 'sm';
    };
    
    /**
     * Проверка является ли устройство планшетом
     */
    window.isTablet = function() {
        return getCurrentBreakpoint() === 'md';
    };
    
    /**
     * Проверка является ли устройство десктопом
     */
    window.isDesktop = function() {
        const breakpoint = getCurrentBreakpoint();
        return breakpoint === 'lg' || breakpoint === 'xl' || breakpoint === 'xxl';
    };
    
    /**
     * Логирование в консоль (только в режиме отладки)
     */
    window.severconLog = function(type, message, data = null) {
        if (!severconConfig.debug.enabled) return;
        
        const logLevels = { error: 0, warn: 1, info: 2, debug: 3 };
        const currentLevel = logLevels[severconConfig.debug.logLevel] || 0;
        const messageLevel = logLevels[type] || 0;
        
        if (messageLevel <= currentLevel) {
            const timestamp = new Date().toISOString().split('T')[1].split('.')[0];
            const prefix = `[Severcon ${timestamp}]`;
            
            if (data) {
                console[type](prefix, message, data);
            } else {
                console[type](prefix, message);
            }
        }
    };
    
    /**
     * Инициализация конфига с данными из WordPress
     */
    function initializeConfig() {
        // Получаем данные из WordPress localization
        if (typeof severcon_ajax !== 'undefined') {
            severconConfig.ajax.nonce = severcon_ajax.nonce;
            severconConfig.ajax.url = severcon_ajax.ajax_url;
            severconConfig.i18n.loading = severcon_ajax.loading || severconConfig.i18n.loading;
        }
        
        // Получаем данные из data-атрибутов body
        const body = document.querySelector(severconConfig.selectors.body);
        if (body) {
            // Категория товаров
            const categoryId = body.getAttribute('data-category-id');
            if (categoryId) {
                severconConfig.currentCategoryId = parseInt(categoryId);
            }
            
            // Тип страницы
            const pageType = body.getAttribute('data-page-type');
            if (pageType) {
                severconConfig.pageType = pageType;
            }
            
            // Настройки отладки из data-атрибута
            const debugMode = body.getAttribute('data-debug-mode');
            if (debugMode === 'true') {
                severconConfig.debug.enabled = true;
            }
        }
        
        // Логируем инициализацию
        severconLog('info', 'Конфигурация Severcon инициализирована', {
            categoryId: severconConfig.currentCategoryId,
            pageType: severconConfig.pageType,
            breakpoint: getCurrentBreakpoint()
        });
    }
    
    // Инициализируем когда DOM готов
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeConfig);
    } else {
        initializeConfig();
    }
    
    // Экспортируем глобально
    window.severconConfig = severconConfig;
    
})();
