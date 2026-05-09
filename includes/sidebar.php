<?php
// Set $activePage before including this file
// e.g., $activePage = 'dashboard';
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?? 'NetServe Admin'; ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Root Variables ── */
        :root {
            --sidebar-width: 260px;
            --brand-red: #e60000;
            --brand-dark-red: #8b0000;
            --sidebar-bg: #1a1a2e;
            --sidebar-hover: rgba(230, 0, 0, 0.15);
            --sidebar-active: linear-gradient(90deg, rgba(230,0,0,0.25) 0%, rgba(230,0,0,0.05) 100%);
            --topbar-height: 64px;
            --body-bg: #f0f2f5;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            color: #333;
            margin: 0;
        }

        /* ── Sidebar ── */
        .ns-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1050;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .ns-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 22px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            text-decoration: none;
        }

        .ns-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--brand-red), var(--brand-dark-red));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .ns-brand-text {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .ns-brand-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.45);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Nav section */
        .ns-nav-section {
            padding: 20px 0 10px;
            flex: 1;
        }

        .ns-nav-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: rgba(255,255,255,0.3);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 0 24px 8px;
        }

        .ns-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            text-decoration: none;
            color: rgba(255,255,255,0.65);
            font-size: 0.92rem;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.2s ease;
            position: relative;
            margin: 2px 0;
        }

        .ns-nav-link i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }

        .ns-nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .ns-nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
        }

        .ns-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--brand-red);
            border-radius: 0 2px 2px 0;
        }

        /* Sidebar bottom */
        .ns-sidebar-bottom {
            padding: 16px 0;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .ns-logout-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            text-decoration: none;
            color: rgba(255,255,255,0.5);
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .ns-logout-link:hover {
            background: rgba(231,76,60,0.15);
            color: #ff6b6b;
        }

        /* ── Main Content Area ── */
        .ns-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        .ns-topbar {
            background: #ffffff;
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid #ebebeb;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .ns-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ns-page-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .ns-topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ns-admin-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f5f6f8;
            border-radius: 24px;
            padding: 6px 14px 6px 6px;
        }

        .ns-admin-avatar {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, var(--brand-red), var(--brand-dark-red));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 700;
        }

        .ns-admin-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
        }

        /* ── Page Content Wrapper ── */
        .ns-content {
            padding: 28px;
            flex: 1;
        }

        /* ── Hamburger toggle (mobile) ── */
        .ns-hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: #333;
            cursor: pointer;
        }

        /* ── Sidebar overlay (mobile) ── */
        .ns-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1040;
        }

        /* ── Responsive ── */
        @media (max-width: 992px) {
            .ns-sidebar {
                transform: translateX(-100%);
            }
            .ns-sidebar.open {
                transform: translateX(0);
            }
            .ns-main {
                margin-left: 0;
            }
            .ns-hamburger {
                display: block;
            }
            .ns-overlay.show {
                display: block;
            }
        }

        /* ── Common Card Styles ── */
        .ns-stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
            overflow: hidden;
            border: none;
        }
        .ns-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .ns-stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
        }
        .sc-blue::before   { background: #4a90e2; }
        .sc-green::before  { background: #2ecc71; }
        .sc-orange::before { background: #f39c12; }
        .sc-amber::before  { background: #e67e22; }
        .sc-teal::before   { background: #27ae60; }
        .sc-red::before    { background: #e74c3c; }
        .sc-purple::before { background: #9b59b6; }

        .ns-stat-label {
            font-size: 0.78rem;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .ns-stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0;
        }
        .sc-blue .ns-stat-value   { color: #4a90e2; }
        .sc-green .ns-stat-value  { color: #2ecc71; }
        .sc-orange .ns-stat-value { color: #f39c12; }
        .sc-amber .ns-stat-value  { color: #e67e22; }
        .sc-teal .ns-stat-value   { color: #27ae60; }
        .sc-red .ns-stat-value    { color: #e74c3c; }
        .sc-purple .ns-stat-value { color: #9b59b6; }

        .ns-stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2.8rem;
            opacity: 0.08;
        }

        /* ── Table Styles ── */
        .ns-table-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
            border: none;
        }
        .ns-table-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .ns-table-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ns-table table thead th {
            background: #f8f9fa;
            color: #777;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 2px solid #f0f0f0;
            white-space: nowrap;
        }
        .ns-table table tbody td {
            padding: 14px 18px;
            vertical-align: middle;
            color: #444;
            border-bottom: 1px solid #f5f5f5;
            font-size: 0.9rem;
        }
        .ns-table table tbody tr:hover {
            background: #fafbfc;
        }
        .ns-table table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Badges ── */
        .ns-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .nb-paid     { background: #e8f5e9; color: #2e7d32; }
        .nb-pending  { background: #fff3e0; color: #e65100; }
        .nb-paylater { background: #fce4ec; color: #c2185b; }
        .nb-approved { background: #e8f5e9; color: #2e7d32; }
        .nb-rejected { background: #ffebee; color: #c62828; }
        .nb-plan     { background: #e3f2fd; color: #1565c0; }

        /* ── Action Buttons ── */
        .ns-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .nab-approve { background: #e8f5e9; color: #2e7d32; }
        .nab-approve:hover { background: #c8e6c9; color: #1b5e20; }
        .nab-reject  { background: #ffebee; color: #c62828; }
        .nab-reject:hover  { background: #ffcdd2; color: #b71c1c; }
        .nab-pay     { background: #e3f2fd; color: #1565c0; }
        .nab-pay:hover     { background: #bbdefb; color: #0d47a1; }
        .nab-delete  { background: #f5f5f5; color: #616161; }
        .nab-delete:hover  { background: #eeeeee; color: #424242; }

        /* ── Filter Panel ── */
        .ns-filter-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 24px;
        }
        .ns-filter-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #555;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Pagination ── */
        .ns-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid #f0f0f0;
        }
        .ns-pagination .page-info {
            font-size: 0.85rem;
            color: #888;
        }
        .ns-pagination .page-buttons {
            display: flex;
            gap: 6px;
        }
        .ns-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #e0e0e0;
            color: #555;
            background: #fff;
        }
        .ns-page-btn:hover {
            background: #f5f5f5;
            color: #333;
        }
        .ns-page-btn.active {
            background: var(--brand-red);
            color: white;
            border-color: var(--brand-red);
        }
        .ns-page-btn.disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* ── Empty State ── */
        .ns-empty {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
        }
        .ns-empty i {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
            color: #ddd;
        }
        .ns-empty h5 { color: #bbb; margin-bottom: 6px; }
        .ns-empty p  { font-size: 0.9rem; }
    </style>
</head>
<body>

<!-- ── Sidebar ────────────────────────────────────── -->
<aside class="ns-sidebar" id="nsSidebar">

    <!-- Brand -->
    <a href="view-booking.php" class="ns-sidebar-brand text-decoration-none">
        <div class="ns-brand-icon">
            <i class="bi bi-broadcast"></i>
        </div>
        <div>
            <div class="ns-brand-text">NetServe</div>
            <div class="ns-brand-sub">Admin Panel</div>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="ns-nav-section">
        <div class="ns-nav-label">Main</div>

        <a href="view-booking.php"
           class="ns-nav-link <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <a href="view-booking.php"
           class="ns-nav-link <?php echo $activePage === 'bookings' ? 'active' : ''; ?>">
            <i class="bi bi-journal-text"></i>
            Bookings
        </a>

        <div class="ns-nav-label mt-3">Tools</div>

        <a href="analytics.php"
           class="ns-nav-link <?php echo $activePage === 'analytics' ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart-line-fill"></i>
            Analytics
        </a>

        <a href="export-csv.php"
           class="ns-nav-link <?php echo $activePage === 'export' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-arrow-down"></i>
            Export CSV
        </a>

        <a href="manage-plans.php"
           class="ns-nav-link <?php echo $activePage === 'plans' ? 'active' : ''; ?>">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Manage Plans
        </a>
    </nav>

    <!-- Bottom -->
    <div class="ns-sidebar-bottom">
        <a href="logout.php" class="ns-logout-link">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</aside>

<!-- Overlay (mobile) -->
<div class="ns-overlay" id="nsOverlay" onclick="closeSidebar()"></div>

<!-- ── Main Wrapper ── -->
<div class="ns-main">

    <!-- Topbar -->
    <div class="ns-topbar">
        <div class="ns-topbar-left">
            <button class="ns-hamburger" onclick="toggleSidebar()" title="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>
            <span class="ns-page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
        </div>
        <div class="ns-topbar-right">
            <div class="ns-admin-badge">
                <div class="ns-admin-avatar">A</div>
                <span class="ns-admin-name">Admin</span>
            </div>
        </div>
    </div>

    <!-- Page Content starts here (closed by footer_end.php or page itself) -->
    <div class="ns-content">
