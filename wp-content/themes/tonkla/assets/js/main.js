document.addEventListener('DOMContentLoaded', () => {
    initNavbarFeatures();
    initPDPA();
    initScrollReveal();
});

loadFontAwesomeWhenIdle();

/**
 * Load Font Awesome subset after first paint so webfonts do not contend with LCP.
 * Subset CSS is ~15KiB (vs ~100KiB full kit) — see bin/build-fa-subset.php.
 */
function loadFontAwesomeWhenIdle() {
    const href = (window.tonklaPerf && window.tonklaPerf.faCss) || '';
    if (!href || document.getElementById('fontawesome-css')) {
        return;
    }

    const inject = () => {
        if (document.getElementById('fontawesome-css')) {
            return;
        }
        const link = document.createElement('link');
        link.id = 'fontawesome-css';
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    };

    // After first paint: rAF twice, then idle (short timeout — subset is small).
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if ('requestIdleCallback' in window) {
                window.requestIdleCallback(inject, { timeout: 1200 });
                return;
            }
            setTimeout(inject, 100);
        });
    });
}

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function setMobileMenuOpen(menu, open) {
    if (!menu) {
        return;
    }

    menu.classList.toggle('is-open', open);
    menu.setAttribute('aria-hidden', open ? 'false' : 'true');

    const btn = document.getElementById('mobile-menu-btn');
    if (btn) {
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
}

function initNavbarFeatures() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    if (menuBtn) {
        menuBtn.setAttribute('aria-expanded', 'false');
        menuBtn.setAttribute('aria-controls', 'mobile-menu');
    }

    document.addEventListener('click', (e) => {
        const menu = document.getElementById('mobile-menu');

        if (e.target.closest('#mobile-menu-btn')) {
            setMobileMenuOpen(menu, !(menu && menu.classList.contains('is-open')));
            return;
        }

        const dropdownToggle = e.target.closest('.mobile-dropdown-toggle');
        if (dropdownToggle) {
            const dropdownContent = dropdownToggle.nextElementSibling;
            const icon = dropdownToggle.querySelector('.fa-chevron-down');

            if (dropdownContent) {
                dropdownContent.classList.toggle('hidden');
            }
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }

        if (e.target.closest('#mobile-menu a')) {
            setMobileMenuOpen(menu, false);
        }

        if (menu && menu.classList.contains('is-open')) {
            const isInsideMenu = e.target.closest('#mobile-menu');
            const isMenuBtn = e.target.closest('#mobile-menu-btn');

            if (!isInsideMenu && !isMenuBtn) {
                setMobileMenuOpen(menu, false);
            }
        }

        const langBtn = e.target.closest('#lang-switch-btn');
        const mobileLangBtn = e.target.closest('#mobile-lang-btn');

        if (langBtn || mobileLangBtn) {
            const modal = document.getElementById('language-modal');
            const backdrop = document.getElementById('lang-modal-backdrop');
            const panel = document.getElementById('lang-modal-panel');

            if (modal) {
                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0');
                    }
                    if (panel) {
                        panel.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
                    }
                });
            }
        }

        const modal = document.getElementById('language-modal');
        if (modal && !modal.classList.contains('hidden')) {
            const closeLangBtn = e.target.closest('#close-lang-modal');
            const isInsidePanel = e.target.closest('#lang-modal-panel');
            const isOpeningBtn = e.target.closest('#lang-switch-btn') || e.target.closest('#mobile-lang-btn');

            if (closeLangBtn || (!isInsidePanel && !isOpeningBtn)) {
                const backdrop = document.getElementById('lang-modal-backdrop');
                const panel = document.getElementById('lang-modal-panel');

                if (backdrop) {
                    backdrop.classList.add('opacity-0');
                }
                if (panel) {
                    panel.classList.add('opacity-0', 'translate-y-4', 'scale-95');
                }

                setTimeout(() => {
                    modal.classList.add('hidden');
                }, prefersReducedMotion() ? 0 : 300);
            }
        }
    });

    const navbar = document.getElementById('navbar');
    const backToTopBtn = document.getElementById('back-to-top');
    let ticking = false;

    window.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }
        ticking = true;
        requestAnimationFrame(() => {
            const scrollY = window.scrollY;

            if (navbar) {
                navbar.classList.toggle('shadow-md', scrollY > 10);
            }

            if (backToTopBtn) {
                const show = scrollY > 300;
                backToTopBtn.classList.toggle('opacity-0', !show);
                backToTopBtn.classList.toggle('invisible', !show);
                backToTopBtn.classList.toggle('translate-y-4', !show);
            }

            ticking = false;
        });
    }, { passive: true });

    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion() ? 'auto' : 'smooth'
            });
        });
    }
}

function initScrollReveal() {
    const targets = document.querySelectorAll(
        '#content > section, main > section, .site-content > section, [data-reveal]'
    );

    if (!targets.length) {
        return;
    }

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.12
    });

    let delayIndex = 0;

    targets.forEach((el) => {
        // Transform breaks position:sticky — skip sticky sections (class only; avoid forced reflow).
        if (el.classList.contains('sticky') || el.closest('.sticky')) {
            return;
        }

        const rect = el.getBoundingClientRect();
        const inView = rect.top < window.innerHeight * 0.88 && rect.bottom > 40;

        // Keep above-the-fold content visible — no flash of empty hero.
        if (inView) {
            el.classList.add('is-visible');
            return;
        }

        el.classList.add('tk-reveal');
        delayIndex += 1;
        if (delayIndex < 4) {
            el.classList.add('tk-reveal-delay-' + delayIndex);
        }
        observer.observe(el);
    });
}

function openPDPABanner(banner, backdrop) {
    if (!banner) {
        return;
    }

    const position = banner.getAttribute('data-position');

    if (position === 'center') {
        banner.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
        if (backdrop) {
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.setAttribute('aria-hidden', 'false');
        }
        document.documentElement.classList.add('overflow-hidden');
        const accept = document.getElementById('pdpa-accept-btn');
        if (accept) {
            accept.focus({ preventScroll: true });
        }
        return;
    }

    if (position === 'bottom-bar') {
        banner.classList.remove('translate-y-full');
        return;
    }

    banner.classList.remove('translate-y-[150%]');
}

function initPDPA() {
    const banner = document.getElementById('pdpa-banner');
    const acceptBtn = document.getElementById('pdpa-accept-btn');
    const rejectBtn = document.getElementById('pdpa-reject-btn');
    const backdrop = document.getElementById('pdpa-backdrop');

    const isAccepted = localStorage.getItem('pdpa_accepted');
    const isRejected = localStorage.getItem('pdpa_rejected');

    if (isAccepted === 'true') {
        loadPDPAScripts();
    } else if (isRejected !== 'true' && banner) {
        setTimeout(() => {
            openPDPABanner(banner, backdrop);
        }, prefersReducedMotion() ? 0 : 800);
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            localStorage.setItem('pdpa_accepted', 'true');
            localStorage.removeItem('pdpa_rejected');

            closePDPABanner(banner, backdrop);
            loadPDPAScripts();
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', () => {
            localStorage.setItem('pdpa_rejected', 'true');
            localStorage.removeItem('pdpa_accepted');

            closePDPABanner(banner, backdrop);
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            // Backdrop click = soft dismiss (same as reject) — no tracking scripts.
            localStorage.setItem('pdpa_rejected', 'true');
            localStorage.removeItem('pdpa_accepted');
            closePDPABanner(banner, backdrop);
        });
    }
}

function closePDPABanner(banner, backdrop) {
    if (!banner) {
        return;
    }

    const position = banner.getAttribute('data-position');
    if (position === 'center') {
        banner.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        document.documentElement.classList.remove('overflow-hidden');
    } else if (position === 'bottom-bar') {
        banner.classList.add('translate-y-full');
    } else {
        banner.classList.add('translate-y-[150%]');
    }

    if (backdrop) {
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.setAttribute('aria-hidden', 'true');
    }
}

function loadPDPAScripts() {
    if (window.pdpaScriptsLoaded) {
        return;
    }
    window.pdpaScriptsLoaded = true;

    const scriptTemplate = document.getElementById('pdpa-scripts-template');
    if (!scriptTemplate) {
        return;
    }

    const rawScripts = scriptTemplate.textContent.trim();
    if (!rawScripts) {
        return;
    }

    const div = document.createElement('div');
    div.innerHTML = rawScripts;

    const scripts = div.querySelectorAll('script');

    scripts.forEach((oldScript) => {
        const newScript = document.createElement('script');

        Array.from(oldScript.attributes).forEach((attr) => {
            newScript.setAttribute(attr.name, attr.value);
        });

        newScript.textContent = oldScript.textContent;
        document.body.appendChild(newScript);
    });
}
