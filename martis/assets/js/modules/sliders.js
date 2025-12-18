/**
 * Модуль слайдеров и каруселей
 */

import CONFIG from '../core/config.js';
import Utils from '../core/utils.js';
import { eventManager, SEVERCON_EVENTS } from '../core/events.js';

class SliderManager {
    constructor() {
        this.sliders = new Map();
        this.autoPlayIntervals = new Map();
        
        this.init();
    }
    
    init() {
        this.detectSliders();
        this.setupGlobalEventListeners();
        
        console.log('Slider manager initialized');
    }
    
    detectSliders() {
        // Обнаружение и инициализация слайдеров на странице
        
        // 1. Вертикальный слайдер (главная страница)
        const verticalSlider = document.querySelector('.vertical-slider');
        if (verticalSlider) {
            this.initVerticalSlider(verticalSlider);
        }
        
        // 2. Галерея товара
        const productGallery = document.querySelector('.product-gallery-slider');
        if (productGallery) {
            this.initProductGallery(productGallery);
        }
        
        // 3. Карусель товаров
        const productCarousel = document.querySelector('.products-carousel');
        if (productCarousel) {
            this.initProductCarousel(productCarousel);
        }
        
        // 4. Слайдер новостей
        const newsSlider = document.querySelector('.news-slider');
        if (newsSlider) {
            this.initNewsSlider(newsSlider);
        }
    }
    
    initVerticalSlider(sliderElement) {
        const sliderId = 'vertical-slider-' + Math.random().toString(36).substr(2, 9);
        
        const slides = sliderElement.querySelectorAll('.slide');
        const navUp = sliderElement.querySelector('.nav-up');
        const navDown = sliderElement.querySelector('.nav-down');
        const currentSlideElement = sliderElement.querySelector('.current-slide');
        const totalSlidesElement = sliderElement.querySelector('.total-slides');
        
        if (!slides.length || !navUp || !navDown) {
            console.warn('Vertical slider elements not found');
            return;
        }
        
        let currentSlide = 0;
        const totalSlides = slides.length;
        
        // Обновление счетчика
        function updateCounter() {
            if (currentSlideElement) {
                currentSlideElement.textContent = (currentSlide + 1).toString().padStart(2, '0');
            }
            if (totalSlidesElement) {
                totalSlidesElement.textContent = totalSlides.toString().padStart(2, '0');
            }
        }
        
        // Показать слайд
        function showSlide(index, direction = 'next') {
            if (index < 0 || index >= totalSlides) return;
            
            // Анимация перехода
            slides[currentSlide].classList.remove('active');
            slides[currentSlide].classList.add('leaving');
            
            setTimeout(() => {
                slides[currentSlide].classList.remove('leaving');
                
                currentSlide = index;
                slides[currentSlide].classList.add('active');
                
                updateCounter();
                
                // Генерируем событие
                eventManager.emit('slider:slide-changed', {
                    sliderId,
                    currentSlide: currentSlide,
                    totalSlides,
                    direction
                });
            }, 300);
        }
        
        // Следующий слайд
        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next, 'next');
        }
        
        // Предыдущий слайд
        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev, 'prev');
        }
        
        // Навигация
        navDown.addEventListener('click', nextSlide);
        navUp.addEventListener('click', prevSlide);
        
        // Автопрокрутка
        function startAutoplay(interval = 5000) {
            stopAutoplay();
            
            const intervalId = setInterval(nextSlide, interval);
            this.autoPlayIntervals.set(sliderId, intervalId);
            
            // Останавливаем при наведении
            sliderElement.addEventListener('mouseenter', stopAutoplay);
            sliderElement.addEventListener('mouseleave', () => startAutoplay(interval));
        }
        
        function stopAutoplay() {
            if (this.autoPlayIntervals.has(sliderId)) {
                clearInterval(this.autoPlayIntervals.get(sliderId));
                this.autoPlayIntervals.delete(sliderId);
            }
        }
        
        // Сохранение слайдера
        this.sliders.set(sliderId, {
            element: sliderElement,
            next: nextSlide,
            prev: prevSlide,
            goTo: (index) => showSlide(index),
            startAutoplay: (interval) => startAutoplay.call(this, interval),
            stopAutoplay: stopAutoplay.bind(this),
            currentIndex: () => currentSlide,
            totalSlides: () => totalSlides
        });
        
        // Инициализация
        updateCounter();
        
        // Стартуем автопрокрутку если нет данных-autoplay="false"
        if (!sliderElement.dataset.autoplay || sliderElement.dataset.autoplay !== 'false') {
            startAutoplay.call(this, 5000);
        }
        
        console.log(`Vertical slider initialized: ${sliderId}`);
    }
    
    initProductGallery(galleryElement) {
        const galleryId = 'product-gallery-' + Math.random().toString(36).substr(2, 9);
        
        const mainImage = galleryElement.querySelector('.gallery-main img');
        const thumbs = galleryElement.querySelectorAll('.gallery-thumb');
        const prevBtn = galleryElement.querySelector('.gallery-prev');
        const nextBtn = galleryElement.querySelector('.gallery-next');
        const zoomBtn = galleryElement.querySelector('.gallery-zoom');
        
        if (!mainImage || !thumbs.length) {
            console.warn('Product gallery elements not found');
            return;
        }
        
        let currentImage = 0;
        const images = [];
        
        // Собираем все изображения
        thumbs.forEach((thumb, index) => {
            const img = thumb.querySelector('img');
            if (img && img.dataset.largeSrc) {
                images.push({
                    thumb: img.src,
                    large: img.dataset.largeSrc,
                    alt: img.alt || ''
                });
                
                // Обработка клика на миниатюру
                thumb.addEventListener('click', () => {
                    showImage(index);
                });
            }
        });
        
        // Показать изображение
        function showImage(index) {
            if (index < 0 || index >= images.length) return;
            
            currentImage = index;
            
            // Обновляем главное изображение
            mainImage.src = images[index].large;
            mainImage.alt = images[index].alt;
            
            // Обновляем активную миниатюру
            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
            
            // Генерируем событие
            eventManager.emit('gallery:image-changed', {
                galleryId,
                currentImage: currentImage,
                totalImages: images.length,
                imageSrc: images[index].large
            });
        }
        
        // Следующее изображение
        function nextImage() {
            const next = (currentImage + 1) % images.length;
            showImage(next);
        }
        
        // Предыдущее изображение
        function prevImage() {
            const prev = (currentImage - 1 + images.length) % images.length;
            showImage(prev);
        }
        
        // Навигация кнопками
        if (prevBtn) prevBtn.addEventListener('click', prevImage);
        if (nextBtn) nextBtn.addEventListener('click', nextImage);
        
        // Зум
        if (zoomBtn) {
            zoomBtn.addEventListener('click', () => {
                this.openLightbox(images, currentImage);
            });
        }
        
        // Сохранение галереи
        this.sliders.set(galleryId, {
            element: galleryElement,
            next: nextImage,
            prev: prevImage,
            goTo: (index) => showImage(index),
            currentIndex: () => currentImage,
            totalImages: () => images.length,
            getImage: (index) => images[index]
        });
        
        // Инициализация первого изображения
        if (images.length > 0) {
            showImage(0);
        }
        
        console.log(`Product gallery initialized: ${galleryId}`);
    }
    
    initProductCarousel(carouselElement) {
        const carouselId = 'product-carousel-' + Math.random().toString(36).substr(2, 9);
        
        const track = carouselElement.querySelector('.carousel-track');
        const items = carouselElement.querySelectorAll('.carousel-item');
        const prevBtn = carouselElement.querySelector('.carousel-prev');
        const nextBtn = carouselElement.querySelector('.carousel-next');
        const dots = carouselElement.querySelectorAll('.carousel-dot');
        
        if (!track || !items.length) {
            console.warn('Product carousel elements not found');
            return;
        }
        
        let currentSlide = 0;
        const itemsPerView = this.getItemsPerView(carouselElement);
        const totalSlides = Math.ceil(items.length / itemsPerView);
        
        // Обновление позиции
        function updatePosition() {
            const itemWidth = items[0].offsetWidth;
            const gap = parseInt(getComputedStyle(track).gap) || 0;
            const translateX = -currentSlide * (itemWidth + gap) * itemsPerView;
            
            track.style.transform = `translateX(${translateX}px)`;
            
            // Обновляем активную точку
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
            
            // Генерируем событие
            eventManager.emit('carousel:slide-changed', {
                carouselId,
                currentSlide,
                totalSlides,
                itemsPerView
            });
        }
        
        // Следующий слайд
        function nextSlide() {
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
            } else {
                currentSlide = 0;
            }
            updatePosition();
        }
        
        // Предыдущий слайд
        function prevSlide() {
            if (currentSlide > 0) {
                currentSlide--;
            } else {
                currentSlide = totalSlides - 1;
            }
            updatePosition();
        }
        
        // Перейти к слайду
        function goToSlide(index) {
            if (index >= 0 && index < totalSlides) {
                currentSlide = index;
                updatePosition();
            }
        }
        
        // Навигация
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Точки
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });
        
        // Автопрокрутка
        function startAutoplay(interval = 4000) {
            stopAutoplay();
            
            const intervalId = setInterval(nextSlide, interval);
            this.autoPlayIntervals.set(carouselId, intervalId);
            
            // Останавливаем при наведении
            carouselElement.addEventListener('mouseenter', stopAutoplay);
            carouselElement.addEventListener('mouseleave', () => startAutoplay(interval));
        }
        
        function stopAutoplay() {
            if (this.autoPlayIntervals.has(carouselId)) {
                clearInterval(this.autoPlayIntervals.get(carouselId));
                this.autoPlayIntervals.delete(carouselId);
            }
        }
        
        // Обработка ресайза
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newItemsPerView = this.getItemsPerView(carouselElement);
                if (newItemsPerView !== itemsPerView) {
                    // Нужно переинициализировать с новыми параметрами
                    this.destroyCarousel(carouselId);
                    this.initProductCarousel(carouselElement);
                } else {
                    updatePosition();
                }
            }, 250);
        });
        
        // Сохранение карусели
        this.sliders.set(carouselId, {
            element: carouselElement,
            next: nextSlide,
            prev: prevSlide,
            goTo: goToSlide,
            startAutoplay: (interval) => startAutoplay.call(this, interval),
            stopAutoplay: stopAutoplay.bind(this),
            currentIndex: () => currentSlide,
            totalSlides: () => totalSlides,
            itemsPerView: () => itemsPerView
        });
        
        // Инициализация
        updatePosition();
        
        // Стартуем автопрокрутку если нет данных-autoplay="false"
        if (!carouselElement.dataset.autoplay || carouselElement.dataset.autoplay !== 'false') {
            startAutoplay.call(this, 4000);
        }
        
        console.log(`Product carousel initialized: ${carouselId}`);
    }
    
    initNewsSlider(sliderElement) {
        // Аналогичная реализация для слайдера новостей
        // Можно расширить при необходимости
    }
    
    getItemsPerView(carouselElement) {
        const width = carouselElement.offsetWidth;
        
        if (width >= 1200) return 4; // desktop
        if (width >= 768) return 3;  // tablet
        if (width >= 480) return 2;  // mobile landscape
        return 1;                    // mobile portrait
    }
    
    openLightbox(images, startIndex = 0) {
        // Создание lightbox для просмотра изображений
        
        const lightbox = document.createElement('div');
        lightbox.className = 'severcon-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-container">
                <button class="lightbox-close" aria-label="Закрыть">
                    <i class="fas fa-times"></i>
                </button>
                <button class="lightbox-prev" aria-label="Предыдущее изображение">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="lightbox-image-container">
                    <img class="lightbox-image" src="${images[startIndex].large}" alt="${images[startIndex].alt}">
                </div>
                <button class="lightbox-next" aria-label="Следующее изображение">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="lightbox-caption">
                    <p class="lightbox-counter">${startIndex + 1} / ${images.length}</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        let currentIndex = startIndex;
        
        // Функции навигации
        function showImage(index) {
            if (index < 0 || index >= images.length) return;
            
            currentIndex = index;
            const image = lightbox.querySelector('.lightbox-image');
            image.src = images[index].large;
            image.alt = images[index].alt;
            
            const counter = lightbox.querySelector('.lightbox-counter');
            counter.textContent = `${index + 1} / ${images.length}`;
        }
        
        function nextImage() {
            const next = (currentIndex + 1) % images.length;
            showImage(next);
        }
        
        function prevImage() {
            const prev = (currentIndex - 1 + images.length) % images.length;
            showImage(prev);
        }
        
        // Обработчики событий
        const closeBtn = lightbox.querySelector('.lightbox-close');
        const prevBtn = lightbox.querySelector('.lightbox-prev');
        const nextBtn = lightbox.querySelector('.lightbox-next');
        const overlay = lightbox.querySelector('.lightbox-overlay');
        
        closeBtn.addEventListener('click', () => lightbox.remove());
        overlay.addEventListener('click', () => lightbox.remove());
        prevBtn.addEventListener('click', prevImage);
        nextBtn.addEventListener('click', nextImage);
        
        // Навигация клавишами
        const handleKeydown = (e) => {
            if (e.key === 'Escape') lightbox.remove();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'ArrowRight') nextImage();
        };
        
        document.addEventListener('keydown', handleKeydown);
        
        // Удаление обработчиков при закрытии
        lightbox.addEventListener('remove', () => {
            document.removeEventListener('keydown', handleKeydown);
        });
        
        // Показываем lightbox
        requestAnimationFrame(() => {
            lightbox.classList.add('active');
        });
    }
    
    setupGlobalEventListeners() {
        // Остановка всех автопрокруток при скрытии страницы
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopAllAutoplay();
            } else {
                this.resumeAllAutoplay();
            }
        });
    }
    
    stopAllAutoplay() {
        this.autoPlayIntervals.forEach((intervalId, sliderId) => {
            clearInterval(intervalId);
        });
        this.autoPlayIntervals.clear();
    }
    
    resumeAllAutoplay() {
        // Можно добавить логику возобновления автопрокрутки
        // по необходимости
    }
    
    destroyCarousel(carouselId) {
        const carousel = this.sliders.get(carouselId);
        if (carousel) {
            // Останавливаем автопрокрутку
            if (this.autoPlayIntervals.has(carouselId)) {
                clearInterval(this.autoPlayIntervals.get(carouselId));
                this.autoPlayIntervals.delete(carouselId);
            }
            
            // Удаляем из коллекции
            this.sliders.delete(carouselId);
        }
    }
    
    /**
     * Публичные методы
     */
    
    // Получить слайдер по ID
    getSlider(id) {
        return this.sliders.get(id);
    }
    
    // Получить все слайдеры
    getAllSliders() {
        return Array.from(this.sliders.keys());
    }
    
    // Создать кастомный слайдер
    createSlider(element, options = {}) {
        const sliderId = 'custom-slider-' + Math.random().toString(36).substr(2, 9);
        
        // Можно расширить для создания кастомных слайдеров
        console.log(`Custom slider created: ${sliderId}`);
        
        return sliderId;
    }
    
    // Деструктор
    destroy() {
        this.stopAllAutoplay();
        this.sliders.clear();
        
        console.log('Slider manager destroyed');
    }
}

// Создаем и экспортируем инстанс
const sliderManager = new SliderManager();

// Экспорт
if (typeof module !== 'undefined' && module.exports) {
    module.exports = sliderManager;
} else {
    // Глобальный доступ
    window.SeverconSliders = sliderManager;
    
    // Инициализация при загрузке документа
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.SEVERCON_SLIDERS_INITIALIZED) {
            window.SeverconSliders.init();
            window.SEVERCON_SLIDERS_INITIALIZED = true;
        }
    });
}