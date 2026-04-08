/**
 * Authentication Guard
 * Prevents cached page viewing after logout
 */
(function() {
    'use strict';

    // Check if CSRF token exists (indicates valid session)
    var csrf = document.querySelector('meta[name="csrf-token"]');
    if (!csrf || !csrf.content) {
        // No CSRF token = not authenticated
        window.location.replace('/login');
        return;
    }

    var _logoutInFlight = false;
    var LOGOUT_REDIRECT_DELAY_MS = 120;

    function broadcastLogoutEvent() {
        try {
            localStorage.setItem('logout-event', String(Date.now()));
        } catch (e) {}
    }

    function navigateToLogin() {
        window.location.replace('/login');
    }

    // Fast logout path used on guarded pages.
    window.performLogout = function(event) {
        if (event) event.preventDefault();
        if (_logoutInFlight) return false;
        _logoutInFlight = true;

        var csrfToken = csrf.content;
        var redirected = false;

        try { sessionStorage.removeItem('logout-event'); } catch (e) {}
        broadcastLogoutEvent();

        var logoutRequest = fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            keepalive: true
        }).catch(function() {
            // Best effort only. We'll still redirect immediately.
        });

        setTimeout(function() {
            if (redirected) return;
            redirected = true;
            navigateToLogin();
        }, LOGOUT_REDIRECT_DELAY_MS);

        logoutRequest.finally(function() {
            if (redirected) return;
            redirected = true;
            navigateToLogin();
        });

        return false;
    };

    // Override default logout function if it exists
    if (window.logout) {
        window.logout = window.performLogout;
    }

    // Periodically check authentication status
    var authCheckInterval = setInterval(function() {
        var token = document.querySelector('meta[name="csrf-token"]');
        if (!token || !token.content) {
            clearInterval(authCheckInterval);
            window.location.replace('/login');
        }
    }, 30000); // Check every 30 seconds

    // Listen for storage events (logout in another tab)
    window.addEventListener('storage', function(e) {
        if (e.key === 'logout-event') {
            window.location.replace('/login');
        }
    });

    // Clear logout event on page load
    try {
        localStorage.removeItem('logout-event');
    } catch (e) {}

    // Prevent back button after logout via pageshow event
    window.addEventListener('pageshow', function(event) {
        // If page was loaded from cache (back/forward button)
        if (event.persisted) {
            // Check if still authenticated
            var token = document.querySelector('meta[name="csrf-token"]');
            if (!token || !token.content) {
                window.location.replace('/login');
            }
        }
    });

    // Idle Auto-Logout (30 minutes)
    if (!window.__docTraxIdleGuardInitialized) {
        var IDLE_TIMEOUT = 30 * 60 * 1000; // 30 minutes in ms
        var _idleTimer = null;

        window.__docTraxIdleGuardInitialized = true;
        window.__docTraxIdleLogoutMs = IDLE_TIMEOUT;

        function resetIdleTimer() {
            if (_idleTimer) clearTimeout(_idleTimer);
            _idleTimer = setTimeout(function() {
                if (window.performLogout) {
                    window.performLogout();
                } else {
                    window.location.replace('/login');
                }
            }, IDLE_TIMEOUT);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click', 'input'].forEach(function(evt) {
            document.addEventListener(evt, resetIdleTimer, { passive: true });
        });

        window.addEventListener('focus', resetIdleTimer);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) resetIdleTimer();
        });

        resetIdleTimer();
    }
})();
