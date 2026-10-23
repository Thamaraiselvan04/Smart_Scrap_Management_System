/* SSMS panel: small plain-JavaScript helpers for the top bar and sidebar.
   1. Menu button opens / closes the sidebar on phones and tablets.
   2. Clicking the dark overlay or pressing Esc closes it.
   3. Only one top-bar dropdown (notifications / account) is open at a time,
      and a click anywhere else closes it. */
(function () {
    var body = document.body;
    var menuBtn = document.getElementById('ssMenuBtn');
    var overlay = document.getElementById('ssOverlay');
    var drops = document.querySelectorAll('.ss-drop');

    function closeSidebar() { body.classList.remove('ss-open'); }

    function closeDrops(except) {
        for (var i = 0; i < drops.length; i++) {
            if (drops[i] !== except) { drops[i].removeAttribute('open'); }
        }
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', function () { body.classList.toggle('ss-open'); });
    }
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    for (var i = 0; i < drops.length; i++) {
        drops[i].addEventListener('toggle', function () {
            if (this.open) { closeDrops(this); }
        });
        drops[i].querySelector('summary').addEventListener('click', function () {
            closeDrops(this.parentNode);
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.ss-drop')) { closeDrops(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeSidebar(); closeDrops(); }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) { closeSidebar(); }
    });
})();
