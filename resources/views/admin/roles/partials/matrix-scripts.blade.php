<script>
(function () {
    var totalPermissions = {{ $totalPermissions }};
    var panelNames = { 'none': 'No Access', 'panel': 'Panel' };

    // ── Select / deselect all chips in a module ──────────────────────────────
    window.selectAllInModule = function (module, checked) {
        document.querySelectorAll('[data-module="' + module + '"]').forEach(function (chip) {
            var cb = chip.querySelector('input[type="checkbox"]');
            cb.checked = checked;
            chip.classList.toggle('active', checked);
        });
        updateCounts();
    };

    // ── Per-chip change handler ──────────────────────────────────────────────
    window.onPermChange = function (checkbox) {
        checkbox.closest('.perm-chip').classList.toggle('active', checkbox.checked);
        updateCounts();
    };

    // ── Panel Access radio selector ──────────────────────────────────────────
    window.selectPanel = function (value) {
        // Sync hidden permission checkboxes
        var adminCb  = document.getElementById('hiddenPanelAdmin');
        var authorCb = document.getElementById('hiddenPanelAuthor');
        if (adminCb)  adminCb.checked  = false;                    // never granted via UI
        if (authorCb) authorCb.checked = (value === 'panel');

        // Update visual state of option cards
        document.querySelectorAll('.panel-option').forEach(function (el) {
            el.classList.toggle('active', el.dataset.value === value);
        });

        // Update sidebar panel badge and header label
        var panelSel = (value !== 'none') ? 1 : 0;
        var cntEl    = document.querySelector('.mod-count-panel');
        var badgeEl  = document.querySelector('.mod-badge-panel');
        var labelEl  = document.getElementById('panelAccessLabel');
        if (cntEl)   cntEl.textContent  = panelSel;
        if (badgeEl) badgeEl.textContent = panelSel + '/1';
        if (labelEl) labelEl.textContent = panelNames[value] || 'No Access';

        updateCounts();
    };

    // ── Recompute total + per-module counters ────────────────────────────────
    function updateCounts() {
        // Total includes panel hidden checkboxes automatically
        var total = document.querySelectorAll('input[name="permissions[]"]:checked').length;
        var el    = document.getElementById('totalCount');
        var bar   = document.getElementById('totalBar');
        if (el)  el.textContent  = total;
        if (bar) bar.style.width = (totalPermissions > 0 ? Math.round((total / totalPermissions) * 100) : 0) + '%';

        // Per-module counts (chip-based modules only — panel is handled above)
        var modules = {};
        document.querySelectorAll('[data-module]').forEach(function (chip) {
            var mod = chip.dataset.module;
            if (!modules[mod]) modules[mod] = { total: 0, checked: 0 };
            modules[mod].total++;
            if (chip.querySelector('input').checked) modules[mod].checked++;
        });
        Object.keys(modules).forEach(function (mod) {
            var badge = document.querySelector('.mod-badge-' + mod);
            var count = document.querySelector('.mod-count-' + mod);
            if (badge) badge.textContent = modules[mod].checked + '/' + modules[mod].total;
            if (count) count.textContent = modules[mod].checked;
        });
    }

    // Expose so external callers (e.g. updateColor) can trigger a recount
    window.updateCounts = updateCounts;
}());
</script>
