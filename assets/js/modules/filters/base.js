/**
 * Базовый модуль фильтрации товаров
 */

import CONFIG from '../../core/config.js';
import Utils from '../../core/utils.js';
import { eventManager, SEVERCON_EVENTS } from '../../core/events.js';
import AjaxManager from '../../core/ajax-manager.js';

class BaseFilters {
    constructor(options = {}) {
        this.options = {
            containerSelector: '.filters-container',
            toggleBtnSelector: '.filters-toggle-btn',
            applyBtnSelector: '.apply-filters',
            resetBtnSelector: '.reset-filters',
            productsGridSelector: '.products-grid.category-products-grid',
            activeFiltersSelector: '#activeFilters',
            filterCheckboxSelector: '.filter-checkbox',
            filterGroupSelector: '.filter-group',
            ...options
        };
        
        this.elements = {};
        this.activeFilters = {};
        this.categoryId = 0;
        this.currentPage = 1;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.cacheElements();
        
        if (this.elements.productsGrid) {
            this.categoryId = this.elements.productsGrid.dataset.categoryId || 0;
            
            if (this.categoryId) {
                this.setupEventListeners();
                this.loadInitialFilters();
                
                console.log('Base filters initialized for category:', this.categoryId);
            } else {
                console.warn('No category ID found for filters');
            }
        }
    }
    
    cacheElements() {
        this.elements = {
            container: Utils.getElement(this.options.containerSelector),
            toggleBtn: Utils.getElement(this.options.toggleBtnSelector),
            applyBtn: Utils.getElement(this.options.applyBtnSelector),
            resetBtn: Utils.getElement(this.options.resetBtnSelector),
            productsGrid: Utils.getElement(this.options.productsGridSelector),
            activeFiltersContainer: Utils.getElement(this.options.activeFiltersSelector),
        };
    }
    
    setupEventListeners() {
        // Переключение видимости фильтров (мобильная версия)
        if (this.elements.toggleBtn) {
            Utils.on(this.elements.toggleBtn, 'click', () => {
                this.toggleFiltersVisibility();
            });
        }
        
        // Применение фильтров
        if (this.elements.applyBtn) {
            Utils.on(this.elements.applyBtn, 'click', () => {
                this.applyFilters();
            });
        }
        
        // Сброс фильтров
        if (this.elements.resetBtn) {
            Utils.on(this.elements.resetBtn, 'click', (e) => {
                e.preventDefault();
                this.resetFilters();
            });
        }
        
        // Изменение чекбоксов
        eventManager.delegate(
            this.options.filterCheckboxSelector,
            'change',
            (e, checkbox) => {
                this.handleCheckboxChange(checkbox);
            }
        );
        
        // Сортировка
        const orderbySelect = Utils.getElement('select.orderby');
        if (orderbySelect) {
            Utils.on(orderbySelect, 'change', () => {
                this.currentPage = 1;
                this.applyFilters();
            });
        }
        
        // Удаление активных фильтров
        eventManager.delegate('.remove-filter', 'click', (e, button) => {
            e.preventDefault();
            this.removeActiveFilter(
                button.dataset.attribute,
                button.dataset.value
            );
        });
        
        // Пагинация
        eventManager.delegate('.page-numbers a', 'click', (e, link) => {
            e.preventDefault();
            const page = parseInt(link.dataset.page) || 1;
            this.loadPage(page);
        });
    }
    
    toggleFiltersVisibility() {
        if (!this.elements.container || !this.elements.toggleBtn) return;
        
        const isActive = this.elements.container.classList.toggle(CONFIG.classes.active);
        this.elements.toggleBtn.classList.toggle(CONFIG.classes.active, isActive);
        
        // Меняем иконку
        const icon = this.elements.toggleBtn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-sliders-h', !isActive);
            icon.classList.toggle('fa-times', isActive);
        }
    }
    
    handleCheckboxChange(checkbox) {
        const attribute = checkbox.name.replace('[]', '');
        const value = checkbox.value;
        const isChecked = checkbox.checked;
        
        if (isChecked) {
            if (!this.activeFilters[attribute]) {
                this.activeFilters[attribute] = [];
            }
            if (!this.activeFilters[attribute].includes(value)) {
                this.activeFilters[attribute].push(value);
            }
        } else {
            if (this.activeFilters[attribute]) {
                const index = this.activeFilters[attribute].indexOf(value);
                if (index > -1) {
                    this.activeFilters[attribute].splice(index, 1);
                }
                if (this.activeFilters[attribute].length === 0) {
                    delete this.activeFilters[attribute];
                }
            }
        }
        
        this.updateActiveFiltersDisplay();
    }
    
    updateActiveFiltersDisplay() {
        if (!this.elements.activeFiltersContainer) return;
        
        const activeFilters = this.collectActiveFilters();
        
        if (activeFilters.length > 0) {
            let html = '<span>Активные фильтры:</span>';
            
            activeFilters.forEach(filter => {
                html += `
                    <span class="active-filter-tag">
                        ${filter.attributeLabel}: ${filter.text}
                        <button class="remove-filter" 
                                data-attribute="${filter.attribute}" 
                                data-value="${filter.value}">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            });
            
            this.elements.activeFiltersContainer.innerHTML = html;
            this.elements.activeFiltersContainer.style.display = 'block';
        } else {
            this.elements.activeFiltersContainer.innerHTML = '';
            this.elements.activeFiltersContainer.style.display = 'none';
        }
    }
    
    collectActiveFilters() {
        const activeFilters = [];
        
        document.querySelectorAll(this.options.filterGroupSelector).forEach(group => {
            const attribute = group.dataset.attribute;
            const attributeLabel = group.querySelector('.filter-group-title')?.textContent || attribute;
            
            group.querySelectorAll(`${this.options.filterCheckboxSelector}:checked`).forEach(checkbox => {
                const value = checkbox.value;
                const text = checkbox.nextElementSibling?.textContent || value;
                
                activeFilters.push({
                    attribute,
                    attributeLabel,
                    value,
                    text
                });
            });
        });
        
        return activeFilters;
    }
    
    removeActiveFilter(attribute, value) {
        const checkbox = document.querySelector(
            `.filter-group[data-attribute="${attribute}"] .filter-checkbox[value="${value}"]`
        );
        
        if (checkbox) {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        }
        
        this.applyFilters();
    }
    
    async applyFilters(page = 1) {
        if (this.isLoading || !this.categoryId) return;
        
        this.currentPage = page;
        this.isLoading = true;
        
        // Показываем лоадер
        this.showLoader();
        
        // Собираем активные фильтры
        const filters = this.prepareFilters();
        
        // Получаем сортировку
        const orderby = this.getCurrentOrderBy();
        
        try {
            // Генерируем событие начала фильтрации
            eventManager.emit(SEVERCON_EVENTS.PRODUCT_FILTER_APPLIED, {
                filters,
                orderby,
                page
            });
            
            // Отправляем запрос
            const result = await AjaxManager.filterProducts({
                categoryId: this.categoryId,
                filters,
                orderby,
                page,
                per_page: CONFIG.filters.per_page
            });
            
            if (result.success) {
                this.handleFilterSuccess(result.data);
            } else {
                this.handleFilterError(result.error);
            }
            
        } catch (error) {
            this.handleFilterError(error);
            
        } finally {
            this.isLoading = false;
            this.hideLoader();
        }
    }
    
    prepareFilters() {
        const filters = {};
        
        document.querySelectorAll(this.options.filterGroupSelector).forEach(group => {
            const attribute = group.dataset.attribute;
            const selected = [];
            
            group.querySelectorAll(`${this.options.filterCheckboxSelector}:checked`).forEach(checkbox => {
                selected.push(checkbox.value);
            });
            
            if (selected.length > 0) {
                filters[attribute] = selected;
            }
        });
        
        return filters;
    }
    
    getCurrentOrderBy() {
        const orderbySelect = Utils.getElement('select.orderby');
        return orderbySelect ? orderbySelect.value : 'menu_order';
    }
    
    showLoader() {
        if (!this.elements.productsGrid) return;
        
        this.elements.productsGrid.classList.add(CONFIG.classes.loading);
        
        const loader = document.createElement('div');
        loader.className = 'filter-loader';
        loader.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
        
        this.elements.productsGrid.innerHTML = '';
        this.elements.productsGrid.appendChild(loader);
    }
    
    hideLoader() {
        if (!this.elements.productsGrid) return;
        
        this.elements.productsGrid.classList.remove(CONFIG.classes.loading);
    }
    
    handleFilterSuccess(data) {
        if (!this.elements.productsGrid) return;
        
        // Обновляем сетку товаров
        this.elements.productsGrid.innerHTML = data.html;
        
        // Обновляем счетчик
        this.updateProductsCount(data.count);
        
        // Обновляем пагинацию
        this.updatePagination(data.max_pages, this.currentPage);
        
        // Инициализируем кнопки быстрого просмотра в новых товарах
        this.initQuickViewButtons();
        
        // Прокручиваем к началу сетки товаров
        this.elements.productsGrid.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        console.log('Filters applied successfully. Products found:', data.count);
    }
    
    handleFilterError(error) {
        if (!this.elements.productsGrid) return;
        
        this.elements.productsGrid.innerHTML = `
            <div class="filter-error">
                <p>Ошибка загрузки товаров</p>
                <button class="btn btn-primary reset-filters-ajax">Сбросить фильтры</button>
            </div>
        `;
        
        // Обработка кнопки сброса
        const resetBtn = this.elements.productsGrid.querySelector('.reset-filters-ajax');
        if (resetBtn) {
            Utils.on(resetBtn, 'click', () => {
                this.resetFilters();
            });
        }
        
        console.error('Filter error:', error);
    }
    
    updateProductsCount(count) {
        const countElement = Utils.getElement('.woocommerce-result-count');
        if (countElement) {
            countElement.textContent = `Найдено товаров: ${count}`;
        }
    }
    
    updatePagination(maxPages, currentPage) {
        const paginationContainer = Utils.getElement('.woocommerce-pagination');
        
        if (!paginationContainer) return;
        
        if (maxPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }
        
        let paginationHTML = '<ul class="page-numbers">';
        
        // Предыдущая страница
        if (currentPage > 1) {
            paginationHTML += `
                <li>
                    <a class="prev" href="#" data-page="${currentPage - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
        }
        
        // Номера страниц
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(maxPages, startPage + maxVisible - 1);
        
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        // Первая страница
        if (startPage > 1) {
            paginationHTML += `
                <li><a href="#" data-page="1">1</a></li>
                ${startPage > 2 ? '<li><span class="dots">…</span></li>' : ''}
            `;
        }
        
        // Основной диапазон
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<li><span class="current">${i}</span></li>`;
            } else {
                paginationHTML += `<li><a href="#" data-page="${i}">${i}</a></li>`;
            }
        }
        
        // Последняя страница
        if (endPage < maxPages) {
            paginationHTML += `
                ${endPage < maxPages - 1 ? '<li><span class="dots">…</span></li>' : ''}
                <li><a href="#" data-page="${maxPages}">${maxPages}</a></li>
            `;
        }
        
        // Следующая страница
        if (currentPage < maxPages) {
            paginationHTML += `
                <li>
                    <a class="next" href="#" data-page="${currentPage + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
        }
        
        paginationHTML += '</ul>';
        
        paginationContainer.innerHTML = paginationHTML;
        paginationContainer.style.display = 'block';
    }
    
    loadPage(page) {
        if (page !== this.currentPage) {
            this.applyFilters(page);
        }
    }
    
    async resetFilters() {
        // Сбрасываем все чекбоксы
        document.querySelectorAll(this.options.filterCheckboxSelector).forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Очищаем активные фильтры
        this.activeFilters = {};
        
        // Обновляем отображение
        this.updateActiveFiltersDisplay();
        
        // Сбрасываем страницу
        this.currentPage = 1;
        
        // Применяем фильтры (без фильтров)
        await this.applyFilters(1);
        
        // Генерируем событие
        eventManager.emit(SEVERCON_EVENTS.PRODUCT_FILTER_RESET);
        
        console.log('Filters reset');
    }
    
    initQuickViewButtons() {
        // Делегирование для кнопок быстрого просмотра
        eventManager.delegate('.quick-view-btn', 'click', (e, button) => {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = button.dataset.productId;
            if (productId) {
                eventManager.emit(SEVERCON_EVENTS.PRODUCT_QUICKVIEW_OPEN, {
                    productId: parseInt(productId)
                });
            }
        });
    }
    
    async loadInitialFilters() {
        // Загружаем начальные данные фильтров
        try {
            const result = await AjaxManager.updateFilterCounts({
                categoryId: this.categoryId,
                filters: {}
            });
            
            if (result.success) {
                this.updateFilterCounts(result.data);
            }
        } catch (error) {
            console.warn('Failed to load initial filter counts:', error);
        }
    }
    
    updateFilterCounts(countsData) {
        // Обновляем счетчики в фильтрах
        Object.entries(countsData).forEach(([taxonomy, terms]) => {
            const attribute = taxonomy.replace('pa_', '');
            const group = document.querySelector(`.filter-group[data-attribute="${attribute}"]`);
            
            if (!group) return;
            
            Object.entries(terms).forEach(([termSlug, count]) => {
                const item = group.querySelector(`.filter-item[data-term-slug="${termSlug}"]`);
                if (item) {
                    const countElement = item.querySelector('.filter-item-count');
                    if (countElement) {
                        countElement.textContent = `(${count})`;
                    }
                }
            });
        });
    }
    
    /**
     * Публичные методы
     */
    
    // Применить фильтры
    applyFilters(page = 1) {
        return this.applyFilters(page);
    }
    
    // Сбросить фильтры
    resetFilters() {
        return this.resetFilters();
    }
    
    // Получить активные фильтры
    getActiveFilters() {
        return { ...this.activeFilters };
    }
    
    // Установить фильтр
    setFilter(attribute, values) {
        if (!Array.isArray(values)) {
            values = [values];
        }
        
        // Находим и отмечаем чекбоксы
        values.forEach(value => {
            const checkbox = document.querySelector(
                `.filter-group[data-attribute="${attribute}"] .filter-checkbox[value="${value}"]`
            );
            
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
        
        return this.applyFilters(1);
    }
    
    // Очистить фильтр
    clearFilter(attribute) {
        const checkboxes = document.querySelectorAll(
            `.filter-group[data-attribute="${attribute}"] .filter-checkbox:checked`
        );
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        });
        
        return this.applyFilters(1);
    }
    
    // Деструктор
    destroy() {
        // Очищаем обработчики событий
        eventManager.off(SEVERCON_EVENTS.PRODUCT_FILTER_APPLIED);
        eventManager.off(SEVERCON_EVENTS.PRODUCT_FILTER_RESET);
        
        console.log('Base filters destroyed');
    }
}

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BaseFilters;
} else {
    // Глобальная инициализация
    document.addEventListener('DOMContentLoaded', () => {
        if (Utils.elementExists('.filters-container')) {
            window.SeverconBaseFilters = new BaseFilters();
        }
    });
}