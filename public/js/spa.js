/**
 * Lightweight SPA-style page navigation.
 * Intercepts internal link clicks, fetches pages via AJAX, swaps head+body via DOM.
 * No full page reload = no white flash = no heartbeat between dashboards.
 */
(function () {

    // Only genuine auth/public pages need a hard browser reload.
    // Every authenticated dashboard / office / records / admin route is handled via SPA swap.
    var FULL_RELOAD_ROUTES = [
        '/login', '/register', '/activate', '/set-password', '/activation-status'
    ];

    function shouldFullReload(url) {
        try {
            var path = new URL(url, location.origin).pathname.replace(/\/+$/, '') || '/';
            for (var i = 0; i < FULL_RELOAD_ROUTES.length; i++) {
                var route = FULL_RELOAD_ROUTES[i];
                if (path === route || path.indexOf(route + '/') === 0) return true;
            }
        } catch (e) { return true; }
        return false;
    }

    /* ── Progress bar ── */
    var _barEl = document.createElement('div');
    _barEl.id = 'spa-bar';
    document.documentElement.appendChild(_barEl);

    var _barCss = document.createElement('style');
    _barCss.textContent =
        '#spa-bar{position:fixed;top:0;left:0;height:5px;z-index:99999;width:0;opacity:0;pointer-events:none;' +
        'background:linear-gradient(90deg,#fca311 0%,#ffde00 60%,#fca311 100%);' +
        'background-size:200% 100%;box-shadow:0 0 10px rgba(252,163,17,.9),0 0 4px rgba(255,222,0,.7);' +
        'transition:width .25s ease,opacity .35s ease}' +
        '#spa-bar.active{opacity:1;animation:spa-shimmer 1.2s linear infinite}' +
        '@keyframes spa-shimmer{0%{background-position:100% 0}100%{background-position:-100% 0}}' +
        /* Fade-transition classes injected into live <body> */
        'body{transition:opacity .12s ease}' +
        'body.spa-out{opacity:0!important;pointer-events:none}';
    document.head.appendChild(_barCss);

    function _barStart() {
        _barEl.style.width = '0';
        _barEl.classList.add('active');
        setTimeout(function () { _barEl.style.width = '75%'; }, 16);
    }
    function _barDone() {
        _barEl.style.width = '100%';
        setTimeout(function () { _barEl.classList.remove('active'); _barEl.style.width = '0'; }, 350);
    }

    /* ── Track already-loaded external scripts to avoid double-execution ── */
    var _loadedSrcs = {};
    document.querySelectorAll('script[src]').forEach(function (s) {
        if (s.src) _loadedSrcs[new URL(s.src, location.origin).href] = true;
    });

    function init() {

        function cssEscapeValue(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }
            return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
        }

        function captureFocusState(options) {
            if (!options || !options.preserveFocus) return null;

            var active = document.activeElement;
            if (!active || !active.tagName) return null;

            var tag = active.tagName.toLowerCase();
            if (tag !== 'input' && tag !== 'textarea' && tag !== 'select') return null;

            var selector = '';
            if (active.id) {
                selector = '#' + cssEscapeValue(active.id);
            } else if (active.name) {
                selector = tag + '[name="' + cssEscapeValue(active.name) + '"]';
            } else {
                return null;
            }

            return {
                selector: selector,
                value: typeof active.value === 'string' ? active.value : '',
                selectionStart: typeof active.selectionStart === 'number' ? active.selectionStart : null,
                selectionEnd: typeof active.selectionEnd === 'number' ? active.selectionEnd : null
            };
        }

        function restoreFocusState(snapshot) {
            if (!snapshot || !snapshot.selector) return;

            requestAnimationFrame(function () {
                var el = document.querySelector(snapshot.selector);
                if (!el || typeof el.focus !== 'function') return;

                el.focus({ preventScroll: true });

                if (typeof el.value === 'string' && snapshot.value && el.value !== snapshot.value) {
                    el.value = snapshot.value;
                }

                if (typeof el.setSelectionRange === 'function' && snapshot.selectionStart !== null) {
                    var max = (el.value || '').length;
                    var start = Math.min(snapshot.selectionStart, max);
                    var end = Math.min(snapshot.selectionEnd === null ? start : snapshot.selectionEnd, max);
                    try { el.setSelectionRange(start, end); } catch (e) {}
                }
            });
        }

        /* Perform the actual DOM swap (head sync + body replace + script re-exec) */
        function applySwap(html, url, historyMode, options) {
            options = options || {};
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var scrollX = window.scrollX || window.pageXOffset || 0;
            var scrollY = window.scrollY || window.pageYOffset || 0;
            var focusSnapshot = captureFocusState(options);

            /* -- Title -- */
            document.title = doc.title;

            /* -- Favicon: sync link[rel="icon"] from new page -- */
            var newFavicon = doc.querySelector('link[rel="icon"]');
            if (newFavicon) {
                var oldFavicon = document.querySelector('head > link[rel="icon"]');
                if (oldFavicon) {
                    oldFavicon.setAttribute('href', newFavicon.getAttribute('href'));
                    if (newFavicon.getAttribute('type')) oldFavicon.setAttribute('type', newFavicon.getAttribute('type'));
                } else {
                    document.head.appendChild(newFavicon.cloneNode(true));
                }
            }

            /* -- Meta tags (keep charset + viewport, replace the rest) -- */
            document.querySelectorAll('head > meta').forEach(function (m) {
                var n = m.getAttribute('name') || '';
                if (n !== 'viewport' && !m.getAttribute('charset')) m.remove();
            });
            doc.querySelectorAll('head > meta').forEach(function (m) {
                var n = m.getAttribute('name') || '';
                if (n !== 'viewport' && !m.getAttribute('charset'))
                    document.head.appendChild(m.cloneNode(true));
            });

            /* -- Inline <style> blocks --
               Only remove anonymous (no id) styles — keyed styles (e.g. clearable-input-style,
               loading-dots-global-style) are injected by form-utils.js which we skip from
               re-execution, so they must be preserved across swaps. */
            document.querySelectorAll('head > style:not([id])').forEach(function (s) { s.remove(); });
            doc.querySelectorAll('head > style').forEach(function (s) {
                // Skip inserting if an element with the same id already exists
                if (s.id && document.getElementById(s.id)) return;
                document.head.appendChild(s.cloneNode(true));
            });
            // Re-inject our persistent spa/fade CSS (it has no id so it was removed above)
            document.head.appendChild(_barCss);

            /* -- Stylesheets: remove stale, add new -- */
            var newHrefs = {};
            doc.querySelectorAll('link[rel="stylesheet"]').forEach(function (l) {
                var h = l.getAttribute('href');
                if (h) newHrefs[h] = true;
            });
            document.querySelectorAll('head > link[rel="stylesheet"]').forEach(function (l) {
                var h = l.getAttribute('href');
                if (!h) return;
                if (h.indexOf('cdnjs.cloudflare.com') !== -1) return; // keep CDN icons/fonts
                if (h.indexOf('fonts.googleapis.com') !== -1) return;
                if (!newHrefs[h]) l.remove();
            });
            var existHrefs = {};
            document.querySelectorAll('head > link[rel="stylesheet"]').forEach(function (l) {
                var h = l.getAttribute('href');
                if (h) existHrefs[h] = true;
            });
            doc.querySelectorAll('link[rel="stylesheet"]').forEach(function (l) {
                var h = l.getAttribute('href');
                if (h && !existHrefs[h]) {
                    var lnk = document.createElement('link');
                    lnk.rel = 'stylesheet';
                    lnk.href = h;
                    document.head.appendChild(lnk);
                }
            });

            /* -- Body -- */
            /* Save sidebar open state before replacing body */
            var _sbWasOpen = !!document.querySelector('.sidebar.open');

            /* Pre-apply sidebar open state to new content BEFORE inserting into DOM
               so the sidebar never visually disappears during SPA navigation. */
            if (_sbWasOpen) {
                var _newSb = doc.querySelector('.sidebar');
                var _newOv = doc.querySelector('.mob-overlay');
                var _newHb = doc.querySelector('.mob-hamburger');
                if (_newSb) { _newSb.classList.add('open'); _newSb.style.transition = 'none'; }
                if (_newOv) { _newOv.classList.add('open'); _newOv.classList.add('show'); }
                if (_newHb) { _newHb.classList.add('toggle'); }
            }

            document.body.innerHTML = doc.body.innerHTML;
            document.body.className  = doc.body.className;
            /* Reset any inline styles carried over from the previous page (e.g. overflow:hidden
               set by a drawer/modal) so the new page always starts with a clean slate. */
            document.body.removeAttribute('style');

            /* Keep body locked if sidebar was open */
            if (_sbWasOpen && document.querySelector('.sidebar')) {
                document.body.style.overflow = 'hidden';
                /* Re-enable sidebar transition after one frame so future toggles animate */
                requestAnimationFrame(function () {
                    var sb = document.querySelector('.sidebar');
                    if (sb) sb.style.transition = '';
                });
            }

            /* -- Re-execute body <script> tags -- */
            document.body.querySelectorAll('script').forEach(function (old) {
                // Never re-run spa.js or form-utils.js (they survive swaps via event delegation)
                if (old.src && old.src.indexOf('spa.js') !== -1)       return;
                if (old.src && old.src.indexOf('form-utils.js') !== -1) return;
                if (old.src && old.src.indexOf('request-utils.js') !== -1) return;

                var s = document.createElement('script');
                // Copy ALL attributes (id, type, data-*, etc.) so elements like
                // <script type="application/json" id="docsData"> keep their id after swap.
                Array.from(old.attributes).forEach(function (attr) {
                    s.setAttribute(attr.name, attr.value);
                });
                if (old.src) {
                    var abs = new URL(old.src, location.origin).href;
                    if (_loadedSrcs[abs]) {
                        // Already loaded — fire reinit event for any listeners
                        window.dispatchEvent(new CustomEvent('spa:reinit'));
                        return;
                    }
                    _loadedSrcs[abs] = true;
                    s.src = old.src;
                } else {
                    s.textContent = old.textContent;
                }
                old.parentNode.replaceChild(s, old);
            });

            if (historyMode === 'push') history.pushState({ spa: true }, '', url);
            else if (historyMode === 'replace') history.replaceState({ spa: true }, '', url);

            if (options.preserveScroll) {
                window.scrollTo(scrollX, scrollY);
            } else {
                window.scrollTo(0, 0);
            }

            restoreFocusState(focusSnapshot);
        }

        /* Fade out (120 ms) -> swap content -> fade in */
        function swap(html, url, historyMode, options) {
            options = options || {};
            // Detect layout mismatch: sidebar layout <-> navbar layout.
            // Crossing layouts causes visual glitches.
            // Force a real navigation instead so the user sees a clean context switch.
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var currentHasSidebar = !!document.querySelector('.sidebar');
            var newHasSidebar     = !!doc.querySelector('.sidebar');
            if (currentHasSidebar !== newHasSidebar) {
                _barDone();
                window.location.href = url;
                return;
            }

            window.dispatchEvent(new CustomEvent('spa:before-swap', {
                detail: { url: url }
            }));

            if (options.silent) {
                applySwap(html, url, historyMode, options);
                return;
            }

            document.body.classList.add('spa-out');
            setTimeout(function () {
                applySwap(html, url, historyMode, options);
                // Double rAF: first commit new DOM, second ensures browser painted before fade-in
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        document.body.classList.remove('spa-out');
                    });
                });
            }, 120);
        }

        /* ── Intercept internal link clicks ── */
        var _navSeq = 0;

        function fetchAndSwap(url, historyMode, fallbackUrl, options) {
            options = options || {};
            var reqId = ++_navSeq;
            if (!options.silent) _barStart();

            return fetch(url, {
                credentials: 'same-origin',
                signal: options.signal
            })
                .then(function (r) {
                    if (shouldFullReload(r.url)) {
                        if (reqId !== _navSeq) return null;
                        if (!options.silent) _barDone();
                        window.location.href = r.url;
                        return null;
                    }

                    return r.text().then(function (h) {
                        return { html: h, url: r.url };
                    });
                })
                .then(function (r) {
                    if (!r || reqId !== _navSeq) return false;
                    swap(r.html, r.url, historyMode || 'push', options);
                    if (!options.silent) _barDone();
                    return true;
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return false;
                    if (reqId !== _navSeq) return false;
                    if (!options.silent) _barDone();
                    window.location.href = fallbackUrl || url;
                    return false;
                });
        }

        window.spaNavigate = function (url, opts) {
            opts = opts || {};

            if (!url) return Promise.resolve(false);

            if (shouldFullReload(url) || shouldFullReload(location.href)) {
                window.location.href = url;
                return Promise.resolve(false);
            }

            return fetchAndSwap(url, opts.historyMode || 'push', opts.fallbackUrl || url, opts);
        };

        document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a || !a.href || a.target) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            if (a.hostname !== location.hostname) return;
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.charAt(0) === '#') return;
            if (a.href === location.href || a.href === location.href + '#') return;

            // Full reload for auth pages (or if we're currently on one)
            if (shouldFullReload(a.href) || shouldFullReload(location.href)) {
                _barStart();
                return; // Browser navigates normally
            }

            e.preventDefault();
            window.spaNavigate(a.href, { historyMode: 'push', fallbackUrl: a.href });
        });

        /* ── Browser back / forward ── */
        window.addEventListener('popstate', function () {
            if (shouldFullReload(location.href)) { location.reload(); return; }
            fetchAndSwap(location.href, 'none', location.href, { preserveScroll: true })
                .catch(function () { location.reload(); });
        });

        history.replaceState({ spa: true }, '', location.href);
    }

    if (!window._spaReady) {
        window._spaReady = true;
        init();
    }

})();
