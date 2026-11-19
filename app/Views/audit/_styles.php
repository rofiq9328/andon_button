<style>
    /* ===== Audit Andon: gaya seragam ===== */
    .audit-card {
        border-radius: 14px;
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .audit-card > .card-header {
        background: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        padding: 0.85rem 1.1rem;
    }

    .audit-toolbar {
        position: sticky;
        top: 57px;
        z-index: 1020;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
    }

    .progress-thin {
        height: 8px;
        border-radius: 999px;
        background-color: var(--bs-secondary-bg);
    }

    .progress-thin .progress-bar {
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .progress-stack {
        height: 8px;
        border-radius: 999px;
        background-color: var(--bs-secondary-bg);
        overflow: hidden;
        display: flex;
    }

    .progress-stack .progress-bar {
        height: 100%;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: background-color 0.2s, color 0.2s;
    }

    .status-pill.is-idle {
        background: var(--bs-secondary-bg);
        color: var(--bs-secondary-color);
    }

    .status-pill.is-saving {
        background: var(--bs-info-bg-subtle);
        color: var(--bs-info-text-emphasis);
    }

    .status-pill.is-saved {
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success-text-emphasis);
    }

    .status-pill.is-error {
        background: var(--bs-danger-bg-subtle);
        color: var(--bs-danger-text-emphasis);
    }

    .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        color: var(--bs-secondary-color);
    }

    .legend-empty {
        background-image: repeating-linear-gradient(45deg, transparent 0 6px, rgba(128, 128, 128, 0.25) 6px 12px);
    }

    .led-dot {
        display: inline-block;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background-color: currentColor;
        flex-shrink: 0;
        transition: box-shadow 0.3s;
    }

    .led-dot.is-active {
        box-shadow: 0 0 9px currentColor;
    }

    /* ===== Tabel input per unit ===== */
    .audit-wrap {
        max-height: 70vh;
    }

    .audit-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--bs-secondary-color);
        background-color: var(--bs-tertiary-bg);
        white-space: nowrap;
        vertical-align: middle;
    }

    .audit-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .audit-table th.grp-start,
    .audit-table td.grp-start {
        border-left: 2px solid var(--bs-secondary-color) !important;
    }

    .audit-table th.grp-alt,
    .audit-table td.grp-alt {
        background-color: var(--bs-secondary-bg);
    }

    .audit-table td.cell-ok {
        background-color: rgba(25, 135, 84, 0.12);
    }

    .audit-table td.cell-bad {
        background-color: rgba(220, 53, 69, 0.14);
    }

    .audit-table td.cell-empty {
        background-image: repeating-linear-gradient(45deg, transparent 0 6px, rgba(128, 128, 128, 0.2) 6px 12px);
    }

    .audit-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 1;
    }

    .audit-table tr[data-state="ok"] .sticky-col      { box-shadow: inset 4px 0 0 var(--bs-success); }
    .audit-table tr[data-state="bad"] .sticky-col     { box-shadow: inset 4px 0 0 var(--bs-danger); }
    .audit-table tr[data-state="partial"] .sticky-col { box-shadow: inset 4px 0 0 var(--bs-warning); }
    .audit-table tr[data-state="empty"] .sticky-col   { box-shadow: inset 4px 0 0 var(--bs-secondary); }

    .audit-table .btn-group .btn {
        min-width: 60px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .unit-no {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .unit-count {
        font-size: 0.7rem;
        color: var(--bs-secondary-color);
        font-variant-numeric: tabular-nums;
    }

    .note-input {
        min-width: 180px;
    }

    /* ===== Kartu statistik ===== */
    .stat-tile {
        border-radius: 14px;
        padding: 0.9rem 1.1rem;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-left-width: 4px;
    }

    .stat-tile .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--bs-secondary-color);
    }

    .stat-tile .stat-num {
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .stat-tile.stat-neutral { border-left-color: var(--bs-secondary); }
    .stat-tile.stat-ok      { border-left-color: var(--bs-success); }
    .stat-tile.stat-ok .stat-num      { color: var(--bs-success-text-emphasis); }
    .stat-tile.stat-bad     { border-left-color: var(--bs-danger); }
    .stat-tile.stat-bad .stat-num     { color: var(--bs-danger-text-emphasis); }
    .stat-tile.stat-partial { border-left-color: var(--bs-warning); }
    .stat-tile.stat-partial .stat-num { color: var(--bs-warning-text-emphasis); }

    /* ===== Kartu line ===== */
    .line-tile {
        border-radius: 14px;
        border: 1px solid var(--bs-border-color);
        border-left-width: 4px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .line-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .line-tile.tone-bad     { border-left-color: var(--bs-danger); }
    .line-tile.tone-ok      { border-left-color: var(--bs-success); }
    .line-tile.tone-partial { border-left-color: var(--bs-warning); }
    .line-tile.tone-empty   { border-left-color: var(--bs-secondary); }

    .line-title {
        font-weight: 800;
        letter-spacing: 0.02em;
        color: var(--bs-body-color);
    }
</style>
