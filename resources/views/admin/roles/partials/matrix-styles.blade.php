<style>
    .perm-module-card {
        border: 1px solid #e9ecef; border-radius: .625rem;
        overflow: hidden; margin-bottom: .875rem;
    }
    .perm-module-header {
        padding: .625rem .875rem; background: #f8f9fa;
        border-bottom: 1px solid #e9ecef; cursor: default; user-select: none;
    }
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: .5rem; padding: .875rem;
    }
    .perm-chip {
        display: flex; align-items: center; gap: .45rem;
        padding: .4rem .65rem;
        border: 1.5px solid #dee2e6; border-radius: .4rem;
        cursor: pointer; transition: all .12s;
        background: #fff; font-size: .81rem;
    }
    .perm-chip:hover { border-color: var(--role-color); background: var(--role-bg); }
    .perm-chip.active {
        border-color: var(--role-color); background: var(--role-bg);
        color: var(--role-color); font-weight: 600;
    }
    .perm-chip input[type="checkbox"] {
        accent-color: var(--role-color); width: 15px; height: 15px; flex-shrink: 0;
    }

    /* Panel Access radio options */
    .panel-option {
        display: inline-flex; align-items: center; gap: .55rem;
        padding: .55rem 1.1rem;
        border: 2px solid #dee2e6; border-radius: .5rem;
        cursor: pointer; transition: border-color .12s, background .12s, color .12s;
        background: #fff; font-size: .875rem; user-select: none; min-width: 155px;
    }
    .panel-option:hover {
        border-color: var(--role-color);
        background: var(--role-bg);
    }
    .panel-option.active {
        border-color: var(--role-color);
        background: var(--role-bg);
        color: var(--role-color);
        font-weight: 600;
    }
    .panel-opt-icon {
        width: 30px; height: 30px; border-radius: .4rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem;
    }

    .preset-color { transition: transform .1s; }
    .preset-color:hover { transform: scale(1.25); }
</style>
