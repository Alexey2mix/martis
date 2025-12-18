/**
 * Конфигурация темы Severcon
 */

const SEVERCON_CONFIG = {
    // Основные селекторы
    selectors: {
        body: 'body',
        header: '#mainHeader',
        mobileToggle: '#mobileToggle',
        mobileMenu: '#mobileMenu',
        searchToggle: '#searchToggle',
        searchOverlay: '#searchOverlay',
        requestBtn: '#requestBtn',
        requestOverlay: '#requestOverlay',
        quickViewOverlay: '#quickViewOverlay',
        productsGrid: '.products-grid.category-products-grid',
        filtersContainer: '.filters-container',
        compactFilters: '.compact-filters',
    },
    
    // Классы состояний
    classes: {
        active: 'active',
        sticky: 'header-sticky',
        loading: 'loading',
        open: {
            menu: 'menu-open',
            search: 'search-open',
            request: 'request-open',
            modal: 'modal-open',
        }
    },
    
    // Настройки AJAX
    ajax: {
        url: severcon_ajax?.ajax_url || '/wp-admin/admin-ajax.php',
        nonce: severcon_ajax?.nonce || '',
        filter_nonce: severcon_ajax?.filter_nonce || '',
        i18n: severcon_ajax?.i18n || {
            loading: 'Загрузка...',
            no_products: 'Товаров не найдено',
            apply_filters: 'Применить фильтры'
        }
    },
    
    // Настройки фильтров
    filters: {
        per_page: 12,
        show_all_limit: 24,
        debounce_delay: 300,
    },
    
    // Настройки слайдеров
    sliders: {
        vertical: {
            autoplay: false,
            interval: 5000,
            transition: 500,
        },
        productGallery: {
            transition: 300,
        }
    },
    
    // Брейкпоинты
    breakpoints: {
        mobile: 768,
        tablet: 992,
        desktop: 1200,
    },
    
    // Флаги
    flags: {
        is_mobile: window.innerWidth < 768,
        is_tablet: window.innerWidth >= 768 && window.innerWidth < 992,
        is_desktop: window.innerWidth >= 992,
        touch_enabled: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
    }
};

// Экспорт для модулей
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SEVERCON_CONFIG;
}