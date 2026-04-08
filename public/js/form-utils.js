/**
 * Auto-capitalize: first letter of every word uppercase, rest lowercase.
 * Applied via document-level event delegation — works for modals and
 * dynamically inserted inputs without any extra setup.
 *
 * Skipped automatically:
 *  - Non-text inputs (email, password, tel, number, …)
 *  - Any element with a data-no-capitalize attribute
 *  - Fields whose id/name/placeholder contains: email, password, mobile,
 *    phone, or track  (tracking numbers, phone numbers, etc.)
 */
(function () {
    'use strict';

    // Fallback used by tracking drawers when request-utils.js fails to load.
    if (typeof window.describeRequestError !== 'function') {
        window.describeRequestError = function (error, fallbackMessage) {
            if (error && typeof error.userMessage === 'string' && error.userMessage.trim() !== '') {
                return error.userMessage;
            }
            if (error && typeof error.message === 'string' && error.message.trim() !== '') {
                return error.message;
            }
            return fallbackMessage || 'Could not load tracking details. Please try again.';
        };
    }

    var EXCLUDE = /email|password|mobile|phone|track/i;

    function toTitleCase(str) {
        // Capitalize first char of every space- or hyphen-delimited word;
        // lowercase the rest so ALL-CAPS input is corrected too.
        return str.replace(/([^\s\-]+)/g, function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
    }

    function isCapitalizable(el) {
        if (!el) return false;
        var tag  = el.tagName;
        var type = (el.getAttribute('type') || 'text').toLowerCase();

        // Only plain text inputs and textareas
        if (tag === 'INPUT' && type !== 'text') return false;
        if (tag !== 'INPUT' && tag !== 'TEXTAREA') return false;

        // Opt-out attribute
        if (el.hasAttribute('data-no-capitalize')) return false;

        // Exclude by id / name / placeholder keywords
        var key = (el.id || '') + '|' + (el.name || '') + '|' + (el.getAttribute('placeholder') || '');
        return !EXCLUDE.test(key);
    }

    // Capture phase so this fires before other input listeners on the element.
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!isCapitalizable(el)) return;

        var val = el.value;
        if (!val) return;

        var newVal = toTitleCase(val);
        if (newVal === val) return;

        // Preserve the caret / selection position
        var ss = el.selectionStart;
        var se = el.selectionEnd;
        el.value = newVal;
        try { el.setSelectionRange(ss, se); } catch (ex) { /* read-only or detached */ }
    }, true);

})();

/**
 * Clearable text inputs
 *
 * Automatically adds an inline "×" clear button to EVERY text and search
 * input on the page.  Add `data-no-clearable` to opt out for a specific field.
 * A MutationObserver catches dynamically-inserted inputs (modals, SPA, etc.).
 */
(function () {
    'use strict';

    var STYLE_ID = 'clearable-input-style';
    var SELECTOR = 'input[type="text"]:not([data-no-clearable]):not(:disabled),' +
                   'input[type="search"]:not([data-no-clearable]):not(:disabled)';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) return;

        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent =
            '.clearable-wrap{position:relative;display:block;width:100%;min-width:0}' +
            '.clearable-wrap>.with-clear-btn{width:100%;padding-right:36px !important}' +
            '.clear-input-btn{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:20px;height:20px;border:none;border-radius:999px;background:#e2e8f0;color:#64748b;display:none;align-items:center;justify-content:center;cursor:pointer;font-family:inherit;font-size:14px;line-height:1;padding:0;z-index:3;transition:all .15s}' +
            '.clear-input-btn:hover{background:#cbd5e1;color:#334155}' +
            '.clear-input-btn:focus{outline:none;box-shadow:0 0 0 2px rgba(0,86,179,.2)}' +
            '.clear-input-btn.show{display:inline-flex}';
        document.head.appendChild(style);
    }

    function ensureWrap(input) {
        var parent = input.parentElement;
        if (parent && parent.classList.contains('clearable-wrap')) return parent;

        var wrap = document.createElement('span');
        wrap.className = 'clearable-wrap';

        // Preserve layout behavior when input is inside flex/grid rows.
        var cs = window.getComputedStyle(input);
        var flex = cs.flex || '';
        if (flex && flex !== '0 1 auto' && flex !== 'none') {
            wrap.style.flex = flex;
        }
        var minWidth = cs.minWidth || '';
        if (minWidth && minWidth !== '0px' && minWidth !== 'auto') {
            wrap.style.minWidth = minWidth;
        }

        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        return wrap;
    }

    function bindClearable(input) {
        if (!input || input.dataset.clearableBound === '1') return;
        if (input.disabled) return;

        var wrap = ensureWrap(input);
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'clear-input-btn';
        btn.setAttribute('aria-label', 'Clear input');
        btn.innerHTML = '&times;';
        wrap.appendChild(btn);

        input.classList.add('with-clear-btn');

        function syncButton() {
            btn.classList.toggle('show', !!(input.value && input.value.length));
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            input.focus();
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            syncButton();
        });

        input.addEventListener('input', syncButton);
        input.addEventListener('change', syncButton);
        syncButton();

        input.dataset.clearableBound = '1';
    }

    function initClearables() {
        injectStyles();
        document.querySelectorAll(SELECTOR).forEach(bindClearable);
    }

    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClearables);
    } else {
        initClearables();
    }

    // Watch for dynamically-inserted inputs (modals, SPA page swaps, etc.)
    var mo = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            m.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches(SELECTOR)) bindClearable(node);
                if (node.querySelectorAll) {
                    node.querySelectorAll(SELECTOR).forEach(bindClearable);
                }
            });
        });
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
})();

/**
 * Global autocomplete killer
 *
 * Disables browser autofill on every input, select, textarea, and form
 * on the page.  A MutationObserver catches dynamically-inserted elements.
 */
(function () {
    'use strict';

    var SELECTOR = 'input, select, textarea';
    var FORM_SELECTOR = 'form';

    function disableAutocomplete(el) {
        if (!el || el.dataset.acOff === '1') return;
        // Never override password managers' "current-password" / "new-password"
        // on password fields — browsers tend to ignore autocomplete=off for those.
        // Instead use one-time-code which signals it's not a login field.
        var type = (el.getAttribute('type') || '').toLowerCase();
        var fieldId = (el.getAttribute('id') || '').toLowerCase();
        var fieldName = (el.getAttribute('name') || '').toLowerCase();
        var isEmailField = type === 'email' || fieldId.indexOf('email') !== -1 || fieldName.indexOf('email') !== -1;
        if (type === 'hidden' || type === 'checkbox' || type === 'radio' || type === 'file') {
            el.dataset.acOff = '1';
            return;
        }

        if (isEmailField) {
            if (!el.getAttribute('autocomplete') || el.getAttribute('autocomplete') === 'off') {
                el.setAttribute('autocomplete', 'email');
            }
            el.setAttribute('inputmode', 'email');
            el.setAttribute('autocapitalize', 'none');
            el.setAttribute('autocorrect', 'off');
            el.setAttribute('spellcheck', 'false');
            el.dataset.acOff = '1';
            return;
        }

        el.setAttribute('autocomplete', 'off');
        el.dataset.acOff = '1';
    }

    function disableFormAutocomplete(form) {
        if (!form || form.dataset.acOff === '1') return;
        form.setAttribute('autocomplete', 'off');
        form.dataset.acOff = '1';
    }

    function init() {
        document.querySelectorAll(SELECTOR).forEach(disableAutocomplete);
        document.querySelectorAll(FORM_SELECTOR).forEach(disableFormAutocomplete);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Catch dynamically-inserted elements
    var mo2 = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            m.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.tagName === 'FORM') disableFormAutocomplete(node);
                if (node.matches && node.matches(SELECTOR)) disableAutocomplete(node);
                if (node.querySelectorAll) {
                    node.querySelectorAll(SELECTOR).forEach(disableAutocomplete);
                    node.querySelectorAll(FORM_SELECTOR).forEach(disableFormAutocomplete);
                }
            });
        });
    });
    mo2.observe(document.documentElement, { childList: true, subtree: true });
})();

/**
 * Global button loading dots
 *
 * Provides window.btnLoading(btn) and window.btnReset(btn) helpers so every
 * fetch-based button can show the same animated dots used on auth pages.
 *
 * Also auto-intercepts traditional <form> submit events to show loading dots
 * on the submit button (covers Search / Apply Filters forms, etc.).
 */
(function () {
    'use strict';

    /* ---- inject CSS ---- */
    var STYLE_ID = 'loading-dots-global-style';
    if (!document.getElementById(STYLE_ID)) {
        var s = document.createElement('style');
        s.id  = STYLE_ID;
        s.textContent =
            '.loading-dots{display:inline-flex;align-items:center;gap:5px}' +
            '.loading-dots::before,.loading-dots::after,.loading-dots span' +
            '{content:"";width:8px;height:8px;border-radius:50%;background:currentColor;animation:dotPulse 1.2s ease-in-out infinite}' +
            '.loading-dots::before{animation-delay:0s}' +
            '.loading-dots span{animation-delay:.2s}' +
            '.loading-dots::after{animation-delay:.4s}' +
            '@keyframes dotPulse{0%,80%,100%{opacity:.25;transform:scale(.8)}40%{opacity:1;transform:scale(1)}}';
        document.head.appendChild(s);
    }

    var DOTS = '<span class="loading-dots"><span></span></span>';

    /**
     * Show loading dots on a button.
     * Saves the current innerHTML so btnReset() can restore it later.
     */
    window.btnLoading = function (btn) {
        if (!btn) return;
        if (!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
        /* Freeze current dimensions so the button doesn't shrink/grow */
        var rect = btn.getBoundingClientRect();
        btn.style.minWidth  = rect.width  + 'px';
        btn.style.minHeight = rect.height + 'px';
        btn.disabled  = true;
        btn.innerHTML = DOTS;
    };

    /**
     * Restore a button to its original state after btnLoading().
     * Pass optional customHtml to override the saved original.
     */
    window.btnReset = function (btn, customHtml) {
        if (!btn) return;
        btn.disabled  = false;
        btn.innerHTML = customHtml || btn.dataset.origHtml || 'Submit';
        btn.style.minWidth  = '';
        btn.style.minHeight = '';
        delete btn.dataset.origHtml;
    };

    /* ---- auto-intercept traditional form submissions ---- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.hasAttribute('data-live-search')) return;

        // Find the submit trigger
        var btn = form.querySelector('button[type="submit"], button:not([type])');
        if (!btn) {
            var inp = form.querySelector('input[type="submit"]');
            if (inp) { inp.disabled = true; inp.value = '\u2026'; }
            return;
        }
        if (btn.hasAttribute('data-no-auto-loading')) return;
        window.btnLoading(btn);
    }, true);
})();
