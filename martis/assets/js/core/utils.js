/**
 * Утилиты и вспомогательные функции
 */

import CONFIG from './config.js';

const Utils = {
    
    // ===== DOM УТИЛИТЫ =====
    
    /**
     * Безопасное получение элемента DOM
     */
    getElement(selector, context = document) {
        const element = context.querySelector(selector);
        if (!element) {
            console.warn(`Element not found: ${selector}`);
        }
        return element;
    },
    
    /**
     * Безопасное получение всех элементов DOM
     */
    getElements(selector, context = document) {
        const elements = context.querySelectorAll(selector);
        if (elements.length === 0) {
            console.warn(`No elements found: ${selector}`);
        }
        return elements;
    },
    
    /**
     * Проверка существования элемента
     */
    elementExists(selector, context = document) {
        return context.querySelector(selector) !== null;
    },
    
    /**
     * Добавление/удаление класса с проверкой
     */
    toggleClass(element, className, force) {
        if (!element || !className) return;
        
        if (force === undefined) {
            element.classList.toggle(className);
        } else {
            element.classList[force ? 'add' : 'remove'](className);
        }
    },
    
    /**
     * Показать/скрыть элемент
     */
    toggleVisibility(element, show, displayStyle = 'block') {
        if (!element) return;
        
        if (show) {
            element.style.display = displayStyle;
            element.setAttribute('aria-hidden', 'false');
        } else {
            element.style.display = 'none';
            element.setAttribute('aria-hidden', 'true');
        }
    },
    
    /**
     * Установка атрибутов доступности
     */
    setAriaAttributes(element, attributes) {
        if (!element) return;
        
        Object.entries(attributes).forEach(([key, value]) => {
            element.setAttribute(key, value);
        });
    },
    
    // ===== СТИЛИ И АНИМАЦИИ =====
    
    /**
     * Плавное появление элемента
     */
    fadeIn(element, duration = 300) {
        return new Promise((resolve) => {
            if (!element) {
                resolve();
                return;
            }
            
            element.style.opacity = 0;
            element.style.display = 'block';
            element.style.transition = `opacity ${duration}ms`;
            
            requestAnimationFrame(() => {
                element.style.opacity = 1;
                
                setTimeout(() => {
                    element.style.transition = '';
                    resolve();
                }, duration);
            });
        });
    },
    
    /**
     * Плавное исчезновение элемента
     */
    fadeOut(element, duration = 300) {
        return new Promise((resolve) => {
            if (!element) {
                resolve();
                return;
            }
            
            element.style.opacity = 1;
            element.style.transition = `opacity ${duration}ms`;
            
            requestAnimationFrame(() => {
                element.style.opacity = 0;
                
                setTimeout(() => {
                    element.style.display = 'none';
                    element.style.transition = '';
                    resolve();
                }, duration);
            });
        });
    },
    
    /**
     * Debounce функция
     */
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
    },
    
    /**
     * Throttle функция
     */
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
    },
    
    // ===== ФОРМАТИРОВАНИЕ ДАННЫХ =====
    
    /**
     * Форматирование цены
     */
    formatPrice(price, currency = '₽') {
        if (typeof price !== 'number') {
            price = parseFloat(price) || 0;
        }
        
        return new Intl.NumberFormat('ru-RU', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(price) + ' ' + currency;
    },
    
    /**
     * Форматирование телефона
     */
    formatPhone(phone) {
        const cleaned = ('' + phone).replace(/\D/g, '');
        
        if (cleaned.length === 11) {
            return cleaned.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '+$1 ($2) $3-$4-$5');
        }
        
        if (cleaned.length === 10) {
            return cleaned.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '($1) $2-$3-$4');
        }
        
        return phone;
    },
    
    /**
     * Обрезка текста с многоточием
     */
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        
        return text.substr(0, maxLength).trim() + '...';
    },
    
    // ===== РАБОТА С URL =====
    
    /**
     * Получение GET параметров
     */
    getUrlParams() {
        const params = {};
        const queryString = window.location.search.substring(1);
        const pairs = queryString.split('&');
        
        pairs.forEach(pair => {
            const [key, value] = pair.split('=');
            if (key) {
                params[decodeURIComponent(key)] = decodeURIComponent(value || '');
            }
        });
        
        return params;
    },
    
    /**
     * Обновление URL без перезагрузки страницы
     */
    updateUrlParams(params, replace = false) {
        const url = new URL(window.location);
        
        Object.entries(params).forEach(([key, value]) => {
            if (value === null || value === '') {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, value);
            }
        });
        
        if (replace) {
            window.history.replaceState({}, '', url);
        } else {
            window.history.pushState({}, '', url);
        }
    },
    
    // ===== СОБЫТИЯ И КОЛЛБЭКИ =====
    
    /**
     * Безопасное добавление обработчика события
     */
    on(element, event, handler, options = {}) {
        if (!element || !event || !handler) return;
        
        element.addEventListener(event, handler, options);
        
        // Возвращаем функцию для удаления
        return () => element.removeEventListener(event, handler, options);
    },
    
    /**
     * Делегирование событий
     */
    delegate(container, selector, event, handler) {
        if (!container || !selector || !event || !handler) return;
        
        const wrappedHandler = (e) => {
            if (e.target.matches(selector) || e.target.closest(selector)) {
                handler.call(e.target, e);
            }
        };
        
        container.addEventListener(event, wrappedHandler);
        
        // Возвращаем функцию для удаления
        return () => container.removeEventListener(event, wrappedHandler);
    },
    
    /**
     * Генерация уникального ID
     */
    generateId(prefix = 'id') {
        return prefix + '_' + Math.random().toString(36).substr(2, 9);
    },
    
    // ===== ВАЛИДАЦИЯ =====
    
    /**
     * Валидация email
     */
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    /**
     * Валидация телефона
     */
    isValidPhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 15;
    },
    
    // ===== LOCAL STORAGE =====
    
    /**
     * Сохранение в localStorage
     */
    setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    },
    
    /**
     * Получение из localStorage
     */
    getStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return defaultValue;
        }
    },
    
    /**
     * Удаление из localStorage
     */
    removeStorage(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    },
    
    // ===== ОБРАБОТКА ОШИБОК =====
    
    /**
     * Показать сообщение об ошибке
     */
    showError(message, container = null) {
        console.error('Severcon Error:', message);
        
        // Можно добавить UI для показа ошибок
        if (container && container instanceof HTMLElement) {
            const errorElement = document.createElement('div');
            errorElement.className = 'severcon-error-message';
            errorElement.innerHTML = `
                <div class="error-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${message}</span>
                </div>
            `;
            
            container.appendChild(errorElement);
            
            // Автоудаление через 5 секунд
            setTimeout(() => {
                if (errorElement.parentNode) {
                    errorElement.remove();
                }
            }, 5000);
        }
    },
    
    /**
     * Показать уведомление
     */
    showNotification(message, type = 'success', duration = 3000) {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `severcon-notification notification-${type}`;
        notification.setAttribute('role', 'alert');
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" aria-label="Закрыть">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Добавляем в body
        document.body.appendChild(notification);
        
        // Показываем с анимацией
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Обработка закрытия
        const closeBtn = notification.querySelector('.notification-close');
        const closeNotification = () => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        };
        
        closeBtn.addEventListener('click', closeNotification);
        
        // Автозакрытие
        if (duration > 0) {
            setTimeout(closeNotification, duration);
        }
        
        return closeNotification;
    },
    
    // ===== ЗАГРУЗКА РЕСУРСОВ =====
    
    /**
     * Ленивая загрузка изображений
     */
    lazyLoadImages(selector = 'img[data-src]') {
        const images = this.getElements(selector);
        
        if (!('IntersectionObserver' in window)) {
            // Fallback для старых браузеров
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
            });
            return;
        }
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => observer.observe(img));
    },
    
    /**
     * Динамическая загрузка скрипта
     */
    loadScript(url, attributes = {}) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${url}"]`)) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = url;
            
            Object.entries(attributes).forEach(([key, value]) => {
                script.setAttribute(key, value);
            });
            
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load script: ${url}`));
            
            document.head.appendChild(script);
        });
    },
    
    /**
     * Динамическая загрузка стилей
     */
    loadStyles(url, attributes = {}) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`link[href="${url}"][rel="stylesheet"]`)) {
                resolve();
                return;
            }
            
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            
            Object.entries(attributes).forEach(([key, value]) => {
                link.setAttribute(key, value);
            });
            
            link.onload = () => resolve();
            link.onerror = () => reject(new Error(`Failed to load styles: ${url}`));
            
            document.head.appendChild(link);
        });
    },
    
    // ===== РАБОТА С ФОРМАМИ =====
    
    /**
     * Сериализация формы в объект
     */
    serializeForm(form) {
        const data = {};
        const formData = new FormData(form);
        
        for (const [key, value] of formData.entries()) {
            if (data[key]) {
                if (!Array.isArray(data[key])) {
                    data[key] = [data[key]];
                }
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        
        return data;
    },
    
    /**
     * Валидация формы
     */
    validateForm(form, rules = {}) {
        const errors = {};
        const elements = form.elements;
        
        Array.from(elements).forEach(element => {
            if (element.name && rules[element.name]) {
                const rule = rules[element.name];
                const value = element.value.trim();
                
                if (rule.required && !value) {
                    errors[element.name] = rule.message || 'Это поле обязательно для заполнения';
                } else if (rule.email && !this.isValidEmail(value)) {
                    errors[element.name] = rule.message || 'Введите корректный email';
                } else if (rule.phone && !this.isValidPhone(value)) {
                    errors[element.name] = rule.message || 'Введите корректный телефон';
                } else if (rule.minLength && value.length < rule.minLength) {
                    errors[element.name] = rule.message || `Минимальная длина: ${rule.minLength} символов`;
                } else if (rule.maxLength && value.length > rule.maxLength) {
                    errors[element.name] = rule.message || `Максимальная длина: ${rule.maxLength} символов`;
                } else if (rule.pattern && !rule.pattern.test(value)) {
                    errors[element.name] = rule.message || 'Неверный формат';
                }
            }
        });
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    },
    
    /**
     * Показать ошибки формы
     */
    showFormErrors(form, errors) {
        // Очищаем предыдущие ошибки
        const errorElements = form.querySelectorAll('.form-error');
        errorElements.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        
        // Показываем новые ошибки
        Object.entries(errors).forEach(([field, message]) => {
            const errorElement = form.querySelector(`[data-field="${field}"]`) || 
                               form.querySelector(`.error-${field}`);
            
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                
                // Добавляем класс ошибки к полю ввода
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('has-error');
                    
                    // Убираем класс при исправлении
                    const removeError = () => {
                        input.classList.remove('has-error');
                        errorElement.style.display = 'none';
                        input.removeEventListener('input', removeError);
                    };
                    
                    input.addEventListener('input', removeError, { once: true });
                }
            }
        });
    }
};

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
} else {
    window.SeverconUtils = Utils;
}