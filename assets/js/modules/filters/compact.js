/**
 * Компактные/умные фильтры
 */

import CONFIG from '../../core/config.js';
import Utils from '../../core/utils.js';
import { eventManager, SEVERCON_EVENTS } from '../../core/events.js';
import AjaxManager from '../../core/ajax-manager.js';
import BaseFilters from './base.js';

class CompactFilters extends BaseFilters {
    constructor(options = {}) {
        super({
            containerSelector: '.compact-filters',
            toggleBtnSelector: '.toggle-all-filters',
            applyBtnSelector: '.apply-filters',
            resetBtnSelector: '.reset-filters',
            productsGridSelector: '.products-grid.category-products-grid',
            filterCheckboxSelector: '.filter-checkbox',
            filterGroupSelector: '.compact-filter-group',
            ...options
        });
        
        this.originalCounts = new Map();
        this.filterStates = new Map();
        this.isCompactView = true;
        
        this.initCompact();
    }
    
    initCompact() {
        if (this.elements.container) {
            this.setupCompactEventListeners();
            this.saveOriginalCounts();
            this.updateActiveFilterTags();
            
            console.log('Compact filters initialized');
        }
    }
    
    setupCompactEventListeners() {
        // Переключение всех фильтров
        if (this.elements.toggleBtn) {
            Utils.on(this.elements.toggleBtn, 'click', () => {
                this.toggleAllFilters();
            });
        }
        
        // Переключение групп фильтров
        eventManager.delegate('.filter-group-toggle', 'click', (e, button) => {
            e.stopPropagation();
            this.toggleFilterGroup(button);
        });
        
        // Выбор/сброс всей группы
        eventManager.delegate('.group-action-select-all', 'click', (e, button) => {
            e.stopPropagation();
            this.selectAllInGroup(button.closest('.compact-filter-group'));
        });
        
        eventManager.delegate('.group-action-reset', 'click', (e, button) => {
            e.stopPropagation();
            this.resetGroup(button.closest('.compact-filter-group'));
        });
        
        // Поиск в фильтрах
        eventManager.delegate('.filter-search', 'input', (e, input) => {
            this.filterSearch(input);
        });
        
        // Кнопка "Показать ещё"
        eventManager.delegate('.show-more-filters', 'click', (e, button) => {
            e.preventDefault();
            this.toggleShowMore(button);
        });
        
        // Обработка кликов по элементам фильтра
        eventManager.delegate('.filter-grid-item', 'click', (e, item) => {
            if (item.classList.contains('unavailable')) return;
            
            const checkbox = item.querySelector('.filter-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                item.classList.toggle('selected', checkbox.checked);
                this.updateActiveFilterTags();
            }
        });
        
        // Удаление тегов активных фильтров
        eventManager.delegate('.remove-filter-tag', 'click', (e, button) => {
            e.preventDefault();
            this.removeFilterTag(
                button.dataset.attribute,
                button.dataset.value
            );
        });
        
        // Очистка всех фильтров
        const clearAllBtn = Utils.getElement('.clear-all-filters');
        if (clearAllBtn) {
            Utils.on(clearAllBtn, 'click', (e) => {
                e.preventDefault();
                this.resetAllFilters();
            });
        }
    }
    
    toggleAllFilters() {
        if (!this.elements.toggleBtn || !this.elements.container) return;
        
        const filtersCollapse = this.elements.container.querySelector('.filters-collapse');
        if (!filtersCollapse) return;
        
        const isActive = this.elements.toggleBtn.classList.toggle(CONFIG.classes.active);
        filtersCollapse.classList.toggle(CONFIG.classes.active, isActive);
        
        const toggleIcon = this.elements.toggleBtn.querySelector('.toggle-icon');
        if (toggleIcon) {
            toggleIcon.classList.toggle('fa-chevron-down', !isActive);
            toggleIcon.classList.toggle('fa-chevron-up', isActive);
        }
    }
    
    toggleFilterGroup(button) {
        const target = button.dataset.target;
        const content = Utils.getElement(`#${target}`);
        
        if (!content) return;
        
        const isActive = button.classList.toggle(CONFIG.classes.active);
        
        if (isActive) {
            content.style.display = 'block';
            content.style.height = '0';
            
            requestAnimationFrame(() => {
                const height = content.scrollHeight;
                content.style.height = `${height}px`;
                
                setTimeout(() => {
                    content.style.height = '';
                    content.style.display = '';
                }, 200);
            });
        } else {
            const height = content.scrollHeight;
            content.style.height = `${height}px`;
            
            requestAnimationFrame(() => {
                content.style.height = '0';
                
                setTimeout(() => {
                    content.style.display = 'none';
                    content.style.height = '';
                }, 200);
            });
        }
        
        const toggleIcon = button.querySelector('.toggle-icon');
        if (toggleIcon) {
            toggleIcon.classList.toggle('fa-chevron-down', !isActive);
            toggleIcon.classList.toggle('fa-chevron-up', isActive);
        }
    }
    
    selectAllInGroup(group) {
        if (!group) return;
        
        const availableItems = group.querySelectorAll('.filter-grid-item:not(.unavailable)');
        const checkboxes = Array.from(availableItems).map(item => 
            item.querySelector('.filter-checkbox')
        ).filter(cb => cb);
        
        if (checkboxes.length === 0) return;
        
        // Проверяем, все ли уже выбраны
        const allChecked = checkboxes.every(cb => cb.checked);
        
        if (allChecked) {
            // Снимаем выбор со всех
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                const item = checkbox.closest('.filter-grid-item');
                if (item) item.classList.remove('selected');
            });
        } else {
            // Выбираем все доступные
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                const item = checkbox.closest('.filter-grid-item');
                if (item) item.classList.add('selected');
            });
        }
        
        this.updateActiveFilterTags();
    }
    
    resetGroup(group) {
        if (!group) return;
        
        // Сбрасываем чекбоксы
        group.querySelectorAll('.filter-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Сбрасываем классы
        group.querySelectorAll('.filter-grid-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Сбрасываем поиск
        const searchInput = group.querySelector('.filter-search');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
        
        this.updateActiveFilterTags();
    }
    
    filterSearch(input) {
        const searchText = input.value.toLowerCase();
        const attribute = input.dataset.attribute;
        const group = input.closest('.compact-filter-group');
        
        if (!group) return;
        
        const grid = group.querySelector('.filter-grid');
        const filteredCount = group.querySelector('.filtered-count');
        
        if (!grid) return;
        
        let visibleCount = 0;
        
        grid.querySelectorAll('.filter-grid-item').forEach(item => {
            const termName = item.dataset.termName?.toLowerCase() || '';
            const matches = termName.includes(searchText);
            
            if (matches) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Показываем счетчик отфильтрованных
        if (searchText.length > 0) {
            if (!filteredCount) {
                const countElement = document.createElement('div');
                countElement.className = 'filtered-count';
                group.querySelector('.filter-group-header')?.appendChild(countElement);
            }
            
            const countEl = filteredCount || group.querySelector('.filtered-count');
            if (countEl) {
                countEl.textContent = `Найдено: ${visibleCount}`;
                countEl.style.display = 'block';
            }
        } else {
            if (filteredCount) {
                filteredCount.style.display = 'none';
            }
        }
    }
    
    toggleShowMore(button) {
        const target = button.dataset.target;
        const grid = Utils.getElement(`#grid-${target}`);
        
        if (!grid) return;
        
        const isExpanded = grid.classList.toggle('show-all');
        
        const showMoreText = button.querySelector('.show-more-text');
        const showLessText = button.querySelector('.show-less-text');
        
        if (showMoreText) showMoreText.style.display = isExpanded ? 'none' : 'inline';
        if (showLessText) showLessText.style.display = isExpanded ? 'inline' : 'none';
        
        button.classList.toggle('active', isExpanded);
    }
    
    saveOriginalCounts() {
        document.querySelectorAll('.filter-item').forEach(item => {
            const countText = item.querySelector('.filter-item-count')?.textContent || '';
            const countMatch = countText.match(/\((\d+)\)/);
            
            if (countMatch && countMatch[1]) {
                this.originalCounts.set(item, parseInt(countMatch[1]));
            }
        });
    }
    
    updateActiveFilterTags() {
        let activeFiltersContainer = Utils.getElement('.active-filters-container');
        
        // Создаем контейнер если его нет
        if (!activeFiltersContainer) {
            const filtersElement = Utils.getElement('.compact-filters');
            if (filtersElement) {
                filtersElement.insertAdjacentHTML('beforebegin', `
                    <div class="active-filters-container">
                        <div class="active-filters-tags"></div>
                        <button class="clear-all-filters">Очистить все</button>
                    </div>
                `);
                activeFiltersContainer = Utils.getElement('.active-filters-container');
                
                // Обработка кнопки "Очистить все"
                const clearBtn = activeFiltersContainer.querySelector('.clear-all-filters');
                if (clearBtn) {
                    Utils.on(clearBtn, 'click', (e) => {
                        e.preventDefault();
                        this.resetAllFilters();
                    });
                }
            }
        }
        
        if (!activeFiltersContainer) return;
        
        const activeFilters = this.collectActiveFilters();
        const activeFilterTags = activeFiltersContainer.querySelector('.active-filters-tags');
        
        if (!activeFilterTags) return;
        
        if (activeFilters.length > 0) {
            let html = '';
            
            activeFilters.forEach(filter => {
                html += `
                    <span class="active-filter-tag">
                        ${filter.attributeLabel}: ${filter.text}
                        <button class="remove-filter-tag" 
                                data-attribute="${filter.attribute}" 
                                data-value="${filter.value}">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
            });
            
            activeFilterTags.innerHTML = html;
            
            // Показываем кнопку "Очистить все"
            const clearBtn = activeFiltersContainer.querySelector('.clear-all-filters');
            if (clearBtn) {
                clearBtn.style.display = 'inline-block';
            }
        } else {
            activeFilterTags.innerHTML = '';
            
            // Скрываем кнопку "Очистить все"
            const clearBtn = activeFiltersContainer.querySelector('.clear-all-filters');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
        }
    }
    
    collectActiveFilters() {
        const activeFilters = [];
        
        document.querySelectorAll('.compact-filter-group').forEach(group => {
            const attribute = group.dataset.attribute;
            const attributeLabel = group.querySelector('.filter-group-title')?.textContent || attribute;
            
            group.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
                const value = checkbox.value;
                const text = checkbox.closest('.filter-grid-item')?.dataset.termName || value;
                
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
    
    removeFilterTag(attribute, value) {
        const checkbox = document.querySelector(
            `.compact-filter-group[data-attribute="${attribute}"] .filter-checkbox[value="${value}"]`
        );
        
        if (checkbox) {
            checkbox.checked = false;
            const item = checkbox.closest('.filter-grid-item');
            if (item) item.classList.remove('selected');
            
            this.applyFilters();
            this.updateActiveFilterTags();
        }
    }
    
    async resetAllFilters() {
        console.log('Resetting all compact filters...');
        
        // 1. Сбрасываем все чекбоксы
        document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // 2. Сбрасываем классы выбора
        document.querySelectorAll('.filter-grid-item').forEach(item => {
            item.classList.remove('selected', 'unavailable', 'zero-items');
        });
        
        // 3. Восстанавливаем оригинальные счетчики
        this.originalCounts.forEach((count, item) => {
            const countElement = item.querySelector('.filter-item-count');
            if (countElement) {
                countElement.textContent = `(${count})`;
            }
        });
        
        // 4. Сбрасываем поиск
        document.querySelectorAll('.filter-search').forEach(input => {
            input.value = '';
            input.dispatchEvent(new Event('input'));
        });
        
        // 5. Сбрасываем расширенные списки
        document.querySelectorAll('.filter-grid').forEach(grid => {
            grid.classList.remove('show-all');
        });
        
        document.querySelectorAll('.show-more-filters').forEach(button => {
            const showMoreText = button.querySelector('.show-more-text');
            const showLessText = button.querySelector('.show-less-text');
            
            if (showMoreText) showMoreText.style.display = 'inline';
            if (showLessText) showLessText.style.display = 'none';
            
            button.classList.remove('active');
        });
        
        // 6. Показываем все элементы
        document.querySelectorAll('.filter-grid-item').forEach(item => {
            item.style.display = '';
            item.style.opacity = '1';
        });
        
        // 7. Показываем все группы
        document.querySelectorAll('.compact-filter-group').forEach(group => {
            group.classList.remove('group-empty');
            group.style.display = '';
        });
        
        // 8. Обновляем теги
        this.updateActiveFilterTags();
        
        // 9. Применяем фильтрацию
        await super.resetFilters();
        
        console.log('All compact filters reset');
    }
    
    async applyFilters(page = 1) {
        await super.applyFilters(page);
        
        // После успешной фильтрации обновляем состояние фильтров
        if (this.categoryId) {
            await this.updateAndHideUnavailableFilters();
        }
    }
    
    async updateAndHideUnavailableFilters() {
        console.log('Updating compact filters to hide unavailable options...');
        
        try {
            const result = await AjaxManager.updateFilterCounts({
                categoryId: this.categoryId,
                filters: this.activeFilters
            });
            
            if (result.success && result.data) {
                this.applyFilterVisibility(result.data, this.activeFilters);
            }
        } catch (error) {
            console.error('Error updating filter counts:', error);
        }
    }
    
    applyFilterVisibility(countsData, activeFilters) {
        // Для каждого атрибута в ответе
        Object.entries(countsData).forEach(([taxonomy, terms]) => {
            const attribute = taxonomy.replace('pa_', '');
            const group = Utils.getElement(`.compact-filter-group[data-attribute="${attribute}"]`);
            
            if (!group) return;
            
            let visibleInGroup = 0;
            
            // Сначала обрабатываем все элементы группы
            group.querySelectorAll('.filter-grid-item').forEach(item => {
                const termSlug = item.dataset.termSlug;
                const checkbox = item.querySelector('.filter-checkbox');
                const isSelected = checkbox?.checked || false;
                
                // Проверяем, доступен ли этот термин
                const isAvailable = terms.hasOwnProperty(termSlug);
                
                if (!isAvailable && !isSelected) {
                    // Недоступный и не выбранный - скрываем полностью
                    item.classList.add('filter-unavailable');
                    item.style.display = 'none';
                    if (checkbox) checkbox.disabled = true;
                } else {
                    // Доступный или выбранный - показываем
                    const count = isAvailable ? terms[termSlug] : 0;
                    const countElement = item.querySelector('.filter-item-count');
                    if (countElement) {
                        countElement.textContent = `(${count})`;
                    }
                    
                    if (count === 0 && !isSelected) {
                        // Доступен, но 0 товаров и не выбран - делаем полупрозрачным
                        item.classList.add('filter-zero');
                        item.style.opacity = '0.5';
                    } else {
                        item.classList.remove('filter-zero');
                        item.style.opacity = '1';
                    }
                    
                    item.classList.remove('filter-unavailable');
                    item.style.display = '';
                    if (checkbox) checkbox.disabled = false;
                    visibleInGroup++;
                }
            });
            
            // Управляем видимостью всей группы
            if (visibleInGroup === 0) {
                group.classList.add('group-empty');
                group.style.display = 'none';
            } else {
                group.classList.remove('group-empty');
                group.style.display = '';
            }
        });
        
        // Скрываем группы без данных
        document.querySelectorAll('.compact-filter-group').forEach(group => {
            const attribute = group.dataset.attribute;
            const taxonomy = 'pa_' + attribute;
            
            if (!countsData.hasOwnProperty(taxonomy)) {
                group.classList.add('group-empty');
                group.style.display = 'none';
            }
        });
    }
    
    /**
     * Публичные методы
     */
    
    // Переключить все фильтры
    toggleAllFilters() {
        this.toggleAllFilters();
    }
    
    // Сбросить все фильтры
    resetAllFilters() {
        return this.resetAllFilters();
    }
    
    // Обновить видимость фильтров
    updateFilterVisibility() {
        return this.updateAndHideUnavailableFilters();
    }
    
    // Деструктор
    destroy() {
        super.destroy();
        this.originalCounts.clear();
        this.filterStates.clear();
        
        console.log('Compact filters destroyed');
    }
}

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CompactFilters;
} else {
    // Глобальная инициализация
    document.addEventListener('DOMContentLoaded', () => {
        if (Utils.elementExists('.compact-filters')) {
            window.SeverconCompactFilters = new CompactFilters();
        }
    });
}