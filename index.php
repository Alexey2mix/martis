<?php get_header(); ?>

<main class="main-content">
    <!-- Герой секция -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">СТРОИТЕЛЬНАЯ КОМПАНИЯ "SEVERCON"</h1>
                <p class="hero-subtitle">Профессиональные решения в области промышленного и гражданского строительства</p>
                <div class="hero-buttons">
                    <a href="#services" class="btn btn-primary">Наши услуги</a>
                    <a href="#contact" class="btn btn-secondary">Связаться с нами</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция услуг -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="section-title">НАШИ УСЛУГИ</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🏭</div>
                    <h3>Промышленное строительство</h3>
                    <p>Строительство промышленных объектов, заводов, фабрик и складских комплексов</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏢</div>
                    <h3>Гражданское строительство</h3>
                    <p>Возведение жилых домов, офисных зданий и социальных объектов</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔧</div>
                    <h3>Реконструкция</h3>
                    <p>Реконструкция и модернизация существующих зданий и сооружений</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📐</div>
                    <h3>Проектирование</h3>
                    <p>Разработка проектной документации и архитектурных решений</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция о компании -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">О КОМПАНИИ</h2>
                    <p>Строительная компания "SEVERCON" - это надежный партнер в области строительства и реконструкции объектов различной сложности. Мы работаем на рынке строительных услуг более 15 лет.</p>
                    <div class="features-list">
                        <div class="feature">
                            <strong>15+ лет</strong>
                            <span>успешной работы</span>
                        </div>
                        <div class="feature">
                            <strong>200+</strong>
                            <span>реализованных проектов</span>
                        </div>
                        <div class="feature">
                            <strong>50+</strong>
                            <span>профессионалов в команде</span>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <!-- Здесь будет изображение -->
                    <div class="image-placeholder">Изображение компании</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Блог/Новости -->
    <section class="news-section">
        <div class="container">
            <h2 class="section-title">ПОСЛЕДНИЕ НОВОСТИ</h2>
            <div class="news-grid">
                <?php if (have_posts()): ?>
                    <?php while (have_posts()): the_post(); ?>
                        <article class="news-card">
                            <?php if (has_post_thumbnail()): ?>
                                <div class="news-image">
                                    <?php the_post_thumbnail(); ?>
                                </div>
                            <?php endif; ?>
                            <div class="news-content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="news-excerpt"><?php the_excerpt(); ?></div>
                                <div class="news-meta">
                                    <span class="date"><?php echo get_the_date(); ?></span>
                                    <a href="<?php the_permalink(); ?>" class="read-more">Подробнее</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Новости скоро будут добавлены.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>