<script>
(function() {
    var wrap = document.getElementById('submissionNotification');
    var toggle = document.getElementById('submissionNotificationToggle');
    var panel = document.getElementById('submissionNotificationPanel');

    if (!wrap || !toggle) return;

    if (panel && panel.parentElement !== document.body) {
        document.body.appendChild(panel);
    }

    function viewportSize() {
        var vv = window.visualViewport;
        return {
            width: vv ? vv.width : window.innerWidth,
            height: vv ? vv.height : window.innerHeight,
            offsetTop: vv ? vv.offsetTop : 0,
            offsetLeft: vv ? vv.offsetLeft : 0
        };
    }

    function positionSubmissionNotifications() {
        if (!panel || !wrap.classList.contains('open')) return;

        var rect = toggle.getBoundingClientRect();
        var viewport = viewportSize();
        var isMobile = viewport.width <= 768;
        var margin = isMobile ? 12 : 16;
        var panelWidth = Math.max(0, Math.min(390, viewport.width - (margin * 2)));
        var minLeft = viewport.offsetLeft + margin;
        var maxLeft = Math.max(minLeft, viewport.offsetLeft + viewport.width - panelWidth - margin);
        var left = isMobile
            ? minLeft
            : Math.min(Math.max(minLeft, viewport.offsetLeft + rect.right - panelWidth), maxLeft);
        var top = Math.max(viewport.offsetTop + margin, viewport.offsetTop + rect.bottom + (isMobile ? 8 : 10));
        var maxHeight = viewport.offsetTop + viewport.height - top - margin;

        if (maxHeight < 240) {
            top = viewport.offsetTop + margin;
            maxHeight = viewport.height - (margin * 2);
        }

        panel.style.width = panelWidth + 'px';
        panel.style.left = left + 'px';
        panel.style.right = 'auto';
        panel.style.top = top + 'px';
        panel.style.maxHeight = Math.max(220, maxHeight) + 'px';
    }

    function closeSubmissionNotifications() {
        wrap.classList.remove('open');
        if (panel) panel.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    window.closeSubmissionNotifications = closeSubmissionNotifications;

    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var isOpen = wrap.classList.toggle('open');
        if (panel) panel.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (isOpen) positionSubmissionNotifications();
    });

    if (panel) {
        panel.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('click', closeSubmissionNotifications);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSubmissionNotifications();
    });
    window.addEventListener('resize', positionSubmissionNotifications);
    window.addEventListener('scroll', positionSubmissionNotifications, true);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', positionSubmissionNotifications);
        window.visualViewport.addEventListener('scroll', positionSubmissionNotifications);
    }
})();
</script>
