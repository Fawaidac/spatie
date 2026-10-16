/**
 * ========================================================================
 * SPARK ADMIN - CORE GLOBAL JAVASCRIPT
 * Handles layout interactions: Fullscreen, Sidebar Toggle & Mobile Overlay
 * ========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // -----------------------------------------------------------------
    // 1. Fullscreen Toggle Interaction (Top Navbar)
    // -----------------------------------------------------------------
    const fullscreenBtn = document.querySelector('#btn-fullscreen');

    function isFullscreen() {
        return !!(
            document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement ||
            document.msFullscreenElement
        );
    }

    function updateFullscreenIcon(active) {
        if (!fullscreenBtn) return;
        const icon = fullscreenBtn.querySelector('i');
        if (icon) {
            if (active) {
                icon.className = 'bi bi-fullscreen-exit';
            } else {
                icon.className = 'bi bi-arrows-fullscreen';
            }
        }
    }

    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function (e) {
            e.preventDefault();

            if (!isFullscreen()) {
                const docElm = document.documentElement;
                if (docElm.requestFullscreen) {
                    docElm.requestFullscreen().then(() => updateFullscreenIcon(true)).catch(() => {});
                } else if (docElm.webkitRequestFullscreen) {
                    docElm.webkitRequestFullscreen();
                    updateFullscreenIcon(true);
                } else if (docElm.mozRequestFullScreen) {
                    docElm.mozRequestFullScreen();
                    updateFullscreenIcon(true);
                } else if (docElm.msRequestFullscreen) {
                    docElm.msRequestFullscreen();
                    updateFullscreenIcon(true);
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().then(() => updateFullscreenIcon(false)).catch(() => {});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                    updateFullscreenIcon(false);
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                    updateFullscreenIcon(false);
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                    updateFullscreenIcon(false);
                }
            }
        });

        // Listen for native escape or exit events
        const fullscreenChangeEvents = [
            'fullscreenchange',
            'webkitfullscreenchange',
            'mozfullscreenchange',
            'MSFullscreenChange'
        ];

        fullscreenChangeEvents.forEach(evt => {
            document.addEventListener(evt, function () {
                updateFullscreenIcon(isFullscreen());
            });
        });
    }

    // -----------------------------------------------------------------
    // 2. Desktop Sidebar Minimize Toggle
    // -----------------------------------------------------------------
    const desktopToggleBtn = document.querySelector('#desktop-sidebar-toggle');
    if (desktopToggleBtn) {
        // Restore saved preference if any
        if (localStorage.getItem('spark_sidebar_minimized') === 'true') {
            document.body.classList.add('sidebar-minimized');
            const icon = desktopToggleBtn.querySelector('i');
            if (icon) icon.className = 'bi bi-chevron-bar-right';
        }

        desktopToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-minimized');
            const isMinimized = document.body.classList.contains('sidebar-minimized');
            localStorage.setItem('spark_sidebar_minimized', isMinimized);

            const icon = desktopToggleBtn.querySelector('i');
            if (icon) {
                icon.className = isMinimized ? 'bi bi-chevron-bar-right' : 'bi bi-chevron-bar-left';
            }

            // Trigger window resize event for charts redraw
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);
        });
    }

    // -----------------------------------------------------------------
    // 3. Mobile Sidebar Toggle, Close Button & Backdrop Overlay
    // -----------------------------------------------------------------
    const sidebar = document.querySelector('.sidebar-wrapper');
    const mobileToggleBtn = document.querySelector('.sidebar-toggle-btn') || document.querySelector('#sidebar-toggle');
    const mobileCloseBtn = document.querySelector('#sidebar-close-btn') || document.querySelector('.sidebar-close-btn');

    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Lock background scroll on mobile
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = ''; // Unlock background scroll
    }

    if (mobileToggleBtn && sidebar) {
        mobileToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (sidebar.classList.contains('show')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });
    }

    if (mobileCloseBtn && sidebar) {
        mobileCloseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileSidebar();
        });
    }

    overlay.addEventListener('click', function () {
        closeMobileSidebar();
    });

    // Close mobile sidebar on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
            closeMobileSidebar();
        }
    });

    // Auto-close mobile sidebar when navigating through menu links on mobile
    document.addEventListener('click', function (e) {
        const menuLink = e.target.closest('.sidebar-menu-link');
        if (menuLink && window.innerWidth <= 1200) {
            closeMobileSidebar();
        }
    });

    // -----------------------------------------------------------------
    // 4. Live Search (Search As You Type with Debounce & Seamless Update)
    // -----------------------------------------------------------------
    let searchDebounceTimer = null;

    async function performLiveSearch(searchInput, immediate = false) {
        const form = searchInput.closest('form');
        if (!form) return;

        const tableWrapper = document.querySelector('.table-responsive');
        if (!tableWrapper) return;

        const searchWrapper = searchInput.closest('.table-search-wrapper');
        const searchIcon = searchWrapper ? searchWrapper.querySelector('.table-search-icon') : null;

        const execute = async () => {
            // Visual loading state on search icon
            if (searchIcon) {
                if (!searchIcon.dataset.originalClass) {
                    searchIcon.dataset.originalClass = searchIcon.className;
                }
                searchIcon.className = 'spinner-border spinner-border-sm table-search-icon text-primary';
                searchIcon.style.borderWidth = '2px';
            }

            try {
                const targetUrl = new URL(form.action || window.location.href, window.location.origin);
                const formData = new FormData(form);

                // Re-build query params cleanly
                targetUrl.search = '';
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== 'all') {
                        targetUrl.searchParams.set(key, value);
                    }
                }
                targetUrl.searchParams.set('page', '1');

                const response = await fetch(targetUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network response error');

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Replace Table Body / Content
                const newTable = doc.querySelector('.table-responsive');
                if (newTable && tableWrapper) {
                    tableWrapper.innerHTML = newTable.innerHTML;
                }

                // 2. Replace Pagination
                const currentPagination = document.querySelector('.table-pagination');
                const newPagination = doc.querySelector('.table-pagination');
                if (currentPagination && newPagination) {
                    currentPagination.outerHTML = newPagination.outerHTML;
                } else if (currentPagination && !newPagination) {
                    currentPagination.remove();
                } else if (!currentPagination && newPagination) {
                    const card = tableWrapper.closest('.card');
                    if (card) card.appendChild(newPagination);
                }

                // 3. Update Reset Button in Filter Form
                const currentResetBtn = form.querySelector('.btn-filter-reset');
                const newResetBtn = doc.querySelector('.btn-filter-reset');
                if (currentResetBtn && !newResetBtn) {
                    currentResetBtn.remove();
                } else if (!currentResetBtn && newResetBtn) {
                    form.appendChild(newResetBtn);
                }

                // 4. Update Filter Buttons active state
                const currentFilterBtns = form.querySelectorAll('.btn-filter-spark');
                const newFilterBtns = doc.querySelectorAll('.btn-filter-spark');
                currentFilterBtns.forEach((btn, idx) => {
                    if (newFilterBtns[idx]) {
                        btn.className = newFilterBtns[idx].className;
                        const span = btn.querySelector('span');
                        const newSpan = newFilterBtns[idx].querySelector('span');
                        if (span && newSpan) span.innerHTML = newSpan.innerHTML;
                    }
                });

                // 5. Update browser URL without reload (keeps fullscreen active!)
                window.history.replaceState({}, '', targetUrl.toString());

            } catch (err) {
                console.warn('Live search fetch fallback:', err);
            } finally {
                if (searchIcon && searchIcon.dataset.originalClass) {
                    searchIcon.className = searchIcon.dataset.originalClass;
                    searchIcon.style.borderWidth = '';
                }
            }
        };

        if (immediate) {
            clearTimeout(searchDebounceTimer);
            execute();
        } else {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(execute, 300); // 300ms responsive debounce
        }
    }

    // Attach search input listener
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('table-search-input')) {
            performLiveSearch(e.target);
        }
    });

    // Prevent enter key from causing a full page refresh
    document.addEventListener('keydown', function (e) {
        if (e.target && e.target.classList.contains('table-search-input') && e.key === 'Enter') {
            e.preventDefault();
            performLiveSearch(e.target, true);
        }
    });

    // -----------------------------------------------------------------
    // 5. AJAX Table Pagination (Smooth Page Navigation Without Reload)
    // -----------------------------------------------------------------
    document.addEventListener('click', async function (e) {
        const pageLink = e.target.closest('.table-pagination a.page-link');
        if (!pageLink) return;

        const targetUrl = pageLink.getAttribute('href');
        if (!targetUrl || targetUrl === '#' || targetUrl.startsWith('javascript:')) return;

        e.preventDefault();

        const tableWrapper = document.querySelector('.table-responsive');
        const paginationWrapper = document.querySelector('.table-pagination');
        if (!tableWrapper) {
            window.location.href = targetUrl;
            return;
        }

        // Add subtle loading opacity
        tableWrapper.style.transition = 'opacity 0.15s ease';
        tableWrapper.style.opacity = '0.4';

        try {
            const response = await fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Pagination fetch failed');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTable = doc.querySelector('.table-responsive');
            const newPagination = doc.querySelector('.table-pagination');

            if (newTable) {
                tableWrapper.innerHTML = newTable.innerHTML;
            }

            if (newPagination && paginationWrapper) {
                paginationWrapper.outerHTML = newPagination.outerHTML;
            } else if (!newPagination && paginationWrapper) {
                paginationWrapper.remove();
            }

            // Update URL without page reload
            window.history.pushState({}, '', targetUrl);

            // Scroll gently to top of card if table is long
            const card = tableWrapper.closest('.card');
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

        } catch (err) {
            console.error('AJAX pagination error, falling back to reload:', err);
            window.location.href = targetUrl;
        } finally {
            tableWrapper.style.opacity = '1';
        }
    });
});
