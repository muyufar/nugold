<?php

/**
 * Layout Template Utama
 * Digunakan oleh semua halaman
 */
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
$userName = $_SESSION['user_name'] ?? 'User';
$userPhoto = $_SESSION['user_photo'] ?? '';
$isDarkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo $isDarkMode ? 'dark' : 'light'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Tabungan Emas Digital</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --gold-color: #ffc700;
            --gold-dark: #e6b300;
            --gold-light: #ffd633;
        }

        body {
            background-color: var(--bs-body-bg);
            transition: background-color 0.3s ease;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 0;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            cursor: pointer;
            pointer-events: none;
            -webkit-tap-highlight-color: transparent;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            touch-action: none;
        }

        @media (min-width: 769px) {
            .sidebar-overlay {
                display: none;
            }
        }

        [data-bs-theme="light"] .sidebar {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 2px solid var(--gold-color);
            background: rgba(255, 199, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .sidebar-brand .d-flex {
            width: 100%;
            align-items: center;
            position: relative;
        }

        .sidebar-brand img {
            flex: 1;
            max-width: calc(100% - 60px);
        }

        #sidebarClose {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #fff !important;
            opacity: 0.9;
            z-index: 1001;
            cursor: pointer;
            border: none;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        #sidebarClose:hover,
        #sidebarClose:focus,
        #sidebarClose:active {
            opacity: 1;
            outline: none;
            background: rgba(0, 0, 0, 0.4);
            transform: translateY(-50%) scale(1.1);
        }

        [data-bs-theme="light"] #sidebarClose {
            color: #333 !important;
            background: rgba(255, 255, 255, 0.3);
        }

        [data-bs-theme="light"] #sidebarClose:hover,
        [data-bs-theme="light"] #sidebarClose:focus,
        [data-bs-theme="light"] #sidebarClose:active {
            background: rgba(255, 255, 255, 0.5);
        }

        #sidebarClose i {
            pointer-events: none;
            font-size: 1.5rem;
        }

        #sidebarClose.btn-link {
            text-decoration: none;
            padding: 0;
        }

        .sidebar-brand img {
            max-width: 100%;
            height: auto;
            max-height: 120px;
            width: auto;
        }

        .sidebar-brand h4 {
            color: var(--gold-color);
            margin: 0;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="light"] .sidebar-menu li {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        [data-bs-theme="light"] .sidebar-menu a {
            color: #333;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--gold-color);
            color: #000;
            padding-left: 2rem;
        }

        .sidebar-menu a i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .main-content.sidebar-hidden {
            margin-left: 0;
        }

        .topbar {
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            background-color: var(--bs-body-bg);
        }

        #sidebarToggle {
            z-index: 101;
            position: relative;
        }

        [data-bs-theme="dark"] .topbar {
            background-color: rgba(26, 26, 26, 0.9);
        }

        [data-bs-theme="light"] .topbar {
            background-color: rgba(255, 255, 255, 0.9);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--gold-color);
        }

        .gold-card {
            background: linear-gradient(135deg, var(--gold-color) 0%, var(--gold-dark) 100%);
            color: #000;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(255, 199, 0, 0.3);
        }

        .gold-card h5 {
            font-size: 1.15rem;
            font-weight: 600;
        }

        .gold-card h2 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .gold-card small {
            font-size: 1rem;
        }

        .stat-card {
            background: var(--bs-card-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--gold-color);
        }

        .stat-card h6 {
            font-size: 1rem;
            font-weight: 600;
        }

        .stat-card h4 {
            font-size: 1.75rem;
            font-weight: bold;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        .alert-box {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .btn-gold {
            background: var(--gold-color);
            color: #000;
            border: none;
            font-weight: bold;
            min-height: 44px;
            padding: 0.5rem 1.5rem;
            touch-action: manipulation;
        }

        .btn-gold:hover {
            background: var(--gold-dark);
            color: #000;
        }

        /* Touch-friendly buttons */
        button,
        .btn,
        a.btn {
            min-height: 44px;
            min-width: 44px;
            touch-action: manipulation;
        }

        /* Responsive tables */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .mobile-card-view {
            display: none;
        }

        .mobile-card {
            background: var(--bs-card-bg);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--gold-color);
        }

        .mobile-card .card-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .mobile-card .card-row:last-child {
            border-bottom: none;
        }

        .mobile-card .card-label {
            font-weight: 600;
            color: var(--bs-secondary);
            font-size: 0.95rem;
        }

        .mobile-card .card-value {
            text-align: right;
            font-weight: 500;
            font-size: 2rem;
        }

        .mobile-card .card-actions {
            margin-top: 0.75rem;
            display: flex;
            gap: 0.5rem;
        }

        .mobile-card .card-actions .btn {
            flex: 1;
        }

        /* Bottom Navigation for Mobile */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bs-body-bg);
            border-top: 1px solid var(--bs-border-color);
            z-index: 1050;
            padding: 0.5rem 0;
            padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.25rem;
            text-decoration: none;
            color: var(--bs-secondary);
            transition: all 0.3s ease;
            min-height: 60px;
            max-width: 20%;
            box-sizing: border-box;
        }

        .bottom-nav-item i {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .bottom-nav-item span {
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            text-align: center;
            font-weight: 500;
        }

        .bottom-nav-item.active,
        .bottom-nav-item:hover {
            color: var(--gold-color);
        }

        .bottom-nav-item.active {
            background: rgba(255, 199, 0, 0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none !important;
            }

            .sidebar-overlay {
                display: none !important;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-bottom: 80px;
                /* Space for bottom nav */
            }

            .main-content.sidebar-hidden {
                margin-left: 0;
            }

            .topbar {
                padding: 0.75rem 1rem;
                margin: -1rem -1rem 1rem -1rem;
            }

            #sidebarToggle {
                display: none !important;
            }

            .bottom-nav {
                display: flex;
            }

            .user-info {
                gap: 0.5rem;
            }

            .user-info span {
                display: none;
            }

            .gold-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }

            .gold-card h5 {
                font-size: 1.1rem;
            }

            .gold-card h2 {
                font-size: 2rem;
            }

            .gold-card small {
                font-size: 0.95rem;
            }

            .stat-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
            }

            .stat-card h6 {
                font-size: 0.95rem;
            }

            .stat-card h4 {
                font-size: 1.5rem;
            }

            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            /* Hide desktop table, show mobile cards */
            .table-responsive {
                display: none;
            }

            .mobile-card-view {
                display: block;
            }

            /* Form improvements */
            .form-control,
            .form-select {
                font-size: 16px;
                /* Prevents zoom on iOS */
                padding: 0.75rem;
                min-height: 44px;
            }

            /* Better spacing */
            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }

            .row>* {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            /* Chart container */
            .chart-container {
                height: 280px !important;
                position: relative;
                width: 100%;
                overflow: hidden;
            }

            canvas {
                max-width: 100% !important;
                height: auto !important;
            }

            /* Increase base font size for mobile */
            body {
                font-size: 16px;
            }

            p {
                font-size: 1rem;
            }

            .text-muted {
                font-size: 0.95rem;
            }

            /* Stat cards - stack vertically on small screens */
            .row .col-md-4 {
                margin-bottom: 1rem;
            }

            /* Ensure bottom nav is always visible */
            .bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1050;
            }

            /* Prevent content from being hidden behind bottom nav */
            body {
                padding-bottom: 0;
            }

            /* Card improvements */
            .card-header {
                padding: 1rem;
                font-size: 1rem;
            }

            .card-header h5 {
                font-size: 1.15rem;
                margin: 0;
                font-weight: 600;
            }

            .card-body {
                font-size: 1rem;
            }

            .mobile-card {
                font-size: 1rem;
            }

            .mobile-card .card-label {
                font-size: 1rem;
            }

            .mobile-card .card-value {
                font-size: 1.05rem;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 0.75rem;
                padding-bottom: 80px;
                /* Space for bottom nav */
            }

            .topbar {
                padding: 0.5rem 0.75rem;
                margin: -0.75rem -0.75rem 0.75rem -0.75rem;
            }

            .gold-card h2 {
                font-size: 3rem;
            }

            .gold-card h5 {
                font-size: 1.1rem;
            }

            .gold-card small {
                font-size: 2rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .stat-card h4 {
                font-size: 1.35rem;
            }

            .stat-card h6 {
                font-size: 0.95rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            /* Bottom nav adjustments for very small screens */
            .bottom-nav-item {
                padding: 0.5rem 0.25rem;
                min-height: 65px;
            }

            .bottom-nav-item i {
                font-size: 1.4rem;
            }

            .bottom-nav-item span {
                font-size: 0.75rem;
                font-weight: 500;
            }

            /* Chart container for very small screens */
            .chart-container {
                height: 250px !important;
            }

            /* Larger fonts for very small screens */
            body {
                font-size: 16px;
            }

            .card-body p {
                font-size: 1rem;
            }

            /* Ensure no horizontal scroll */
            body {
                overflow-x: hidden;
            }

            .row {
                margin-left: 0;
                margin-right: 0;
            }

            .row>* {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebarOnClick()" ontouchstart="closeSidebarOnTouch(event)"></div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex justify-content-between align-items-center">
                <img src="<?php echo BASE_URL; ?>image/NUGold.png" alt="NUGold">
                <button type="button" class="btn btn-link text-white d-md-none" id="sidebarClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?php echo BASE_URL; ?>index.php?action=dashboard" class="<?php echo $action === 'dashboard' ? 'active' : ''; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=tambah_emas" class="<?php echo $action === 'tambah_emas' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> Tambah Emas</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=simulasi_gadai" class="<?php echo $action === 'simulasi_gadai' ? 'active' : ''; ?>"><i class="bi bi-calculator"></i> Simulasi Gadai</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=riwayat_harga" class="<?php echo $action === 'riwayat_harga' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Riwayat Harga</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=pengaturan_alert" class="<?php echo $action === 'pengaturan_alert' ? 'active' : ''; ?>"><i class="bi bi-bell"></i> Pengaturan Alert</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=profil" class="<?php echo $action === 'profil' ? 'active' : ''; ?>"><i class="bi bi-person"></i> Profil</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="user-info">
                <button class="btn btn-sm btn-outline-secondary" id="darkModeToggle" title="Toggle Dark Mode">
                    <i class="bi bi-<?php echo $isDarkMode ? 'sun' : 'moon'; ?>"></i>
                </button>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($userName); ?></span>
                <?php if ($userPhoto): ?>
                    <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="User" class="user-avatar">
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>index.php?action=logout" class="btn btn-sm btn-outline-danger d-md-none" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="bottom-nav">
        <a href="<?php echo BASE_URL; ?>index.php?action=dashboard" class="bottom-nav-item <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=tambah_emas" class="bottom-nav-item <?php echo $action === 'tambah_emas' ? 'active' : ''; ?>">
            <i class="bi bi-plus-circle"></i>
            <span>Tambah</span>
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=simulasi_gadai" class="bottom-nav-item <?php echo $action === 'simulasi_gadai' ? 'active' : ''; ?>">
            <i class="bi bi-calculator"></i>
            <span>Gadai</span>
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=riwayat_harga" class="bottom-nav-item <?php echo $action === 'riwayat_harga' ? 'active' : ''; ?>">
            <i class="bi bi-graph-up"></i>
            <span>Riwayat</span>
        </a>
        <a href="<?php echo BASE_URL; ?>index.php?action=profil" class="bottom-nav-item <?php echo $action === 'profil' ? 'active' : ''; ?>">
            <i class="bi bi-person"></i>
            <span>Profil</span>
        </a>
    </nav>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Dark Mode Toggle
        document.getElementById('darkModeToggle')?.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', newTheme);
            document.cookie = 'dark_mode=' + (newTheme === 'dark' ? '1' : '0') + '; path=/; max-age=31536000';

            const icon = this.querySelector('i');
            icon.className = 'bi bi-' + (newTheme === 'dark' ? 'sun' : 'moon');
        });

        // Sidebar Toggle - Initialize after DOM is ready
        (function() {
            let sidebar, mainContent, sidebarToggle, sidebarOverlay, sidebarClose;

            function initSidebar() {
                sidebar = document.querySelector('.sidebar');
                mainContent = document.querySelector('.main-content');
                sidebarToggle = document.getElementById('sidebarToggle');
                sidebarOverlay = document.getElementById('sidebarOverlay');
                sidebarClose = document.getElementById('sidebarClose');

                if (!sidebar) {
                    console.error('Sidebar element not found');
                    return;
                }

                console.log('Sidebar initialized', {
                    sidebar: !!sidebar,
                    overlay: !!sidebarOverlay,
                    toggle: !!sidebarToggle,
                    close: !!sidebarClose
                });

                setupSidebarEvents();
            }

            function isMobile() {
                return window.innerWidth <= 768;
            }

            // Make functions globally accessible for onclick
            window.closeSidebarOnClick = function() {
                if (sidebar) {
                    sidebar.classList.remove('show');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('show');
                }
            };

            window.closeSidebarOnTouch = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (sidebar) {
                    sidebar.classList.remove('show');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('show');
                }
                return false;
            };

            function toggleSidebarMobile() {
                if (!sidebar) return;

                const isShowing = sidebar.classList.toggle('show');
                if (sidebarOverlay) {
                    if (isShowing) {
                        sidebarOverlay.classList.add('show');
                        // Add event listener when sidebar opens
                        attachOverlayListeners();
                    } else {
                        sidebarOverlay.classList.remove('show');
                        // Remove event listener when sidebar closes
                        detachOverlayListeners();
                    }
                }
            }

            function closeSidebarMobile() {
                if (sidebar) {
                    sidebar.classList.remove('show');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('show');
                }
                detachOverlayListeners();
            }

            let overlayClickHandler = null;
            let overlayTouchHandler = null;

            function attachOverlayListeners() {
                if (!sidebarOverlay) return;

                // Remove existing listeners first
                detachOverlayListeners();

                // Add new listeners
                overlayClickHandler = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeSidebarMobile();
                };

                overlayTouchHandler = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeSidebarMobile();
                };

                sidebarOverlay.addEventListener('click', overlayClickHandler, true);
                sidebarOverlay.addEventListener('touchend', overlayTouchHandler, {
                    passive: false,
                    capture: true
                });
            }

            function detachOverlayListeners() {
                if (!sidebarOverlay) return;
                if (overlayClickHandler) {
                    sidebarOverlay.removeEventListener('click', overlayClickHandler, true);
                    overlayClickHandler = null;
                }
                if (overlayTouchHandler) {
                    sidebarOverlay.removeEventListener('touchend', overlayTouchHandler, true);
                    overlayTouchHandler = null;
                }
            }

            function updateToggleIcon(hidden) {
                if (sidebarToggle) {
                    const icon = sidebarToggle.querySelector('i');
                    if (icon) {
                        if (isMobile()) {
                            icon.className = 'bi bi-list';
                        } else {
                            icon.className = hidden ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
                        }
                    }
                }
            }

            function setupSidebarEvents() {
                // SIMPLE AND RELIABLE: Use body click detection
                // This is the most reliable method for mobile
                let bodyClickHandler = function(e) {
                    if (!isMobile()) return;
                    if (!sidebar || !sidebar.classList.contains('show')) return;

                    // Get the actual clicked element
                    const target = e.target;

                    // Don't close if clicking inside sidebar
                    if (sidebar.contains(target)) {
                        // But close if clicking on menu links
                        if (target.closest('.sidebar-menu a')) {
                            setTimeout(closeSidebarMobile, 100);
                        }
                        return;
                    }

                    // Don't close if clicking on toggle button
                    if (sidebarToggle && (sidebarToggle.contains(target) || target === sidebarToggle)) {
                        return;
                    }

                    // Close for any other click
                    closeSidebarMobile();
                };

                // Add to body with capture phase
                document.body.addEventListener('click', bodyClickHandler, true);
                document.body.addEventListener('touchend', bodyClickHandler, true);

                // Store handler for potential cleanup
                window._sidebarBodyHandler = bodyClickHandler;

                // Close sidebar button
                if (sidebarClose) {
                    sidebarClose.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Close button clicked');
                        closeSidebarMobile();
                    });
                } else {
                    console.warn('Sidebar close button not found');
                }

                // Sidebar toggle button - SINGLE EVENT LISTENER
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Toggle button clicked, isMobile:', isMobile());

                        if (isMobile()) {
                            toggleSidebarMobile();
                        } else {
                            const isHidden = sidebar.classList.toggle('hidden');
                            if (mainContent) {
                                mainContent.classList.toggle('sidebar-hidden', isHidden);
                            }
                            localStorage.setItem('sidebarHidden', isHidden);
                            updateToggleIcon(isHidden);
                        }
                    });
                } else {
                    console.error('Sidebar toggle button not found');
                }

                // Load sidebar state from localStorage (only for desktop)
                if (!isMobile()) {
                    const sidebarHidden = localStorage.getItem('sidebarHidden') === 'true';
                    if (sidebarHidden && sidebar) {
                        sidebar.classList.add('hidden');
                        if (mainContent) {
                            mainContent.classList.add('sidebar-hidden');
                        }
                        updateToggleIcon(true);
                    }
                }

                // Close sidebar when clicking menu item (mobile)
                if (isMobile()) {
                    document.querySelectorAll('.sidebar-menu a').forEach(link => {
                        link.addEventListener('click', function() {
                            setTimeout(closeSidebarMobile, 100);
                        });
                    });
                }
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSidebar);
            } else {
                initSidebar();
            }
        })();

        // Swipe gesture for sidebar (mobile) - moved inside IIFE
        (function() {
            let touchStartX = 0;
            let touchEndX = 0;
            let sidebarRef = null;

            function isMobile() {
                return window.innerWidth <= 768;
            }

            function handleSwipe() {
                if (!sidebarRef) return;

                const swipeThreshold = 50;
                const swipeDistance = touchEndX - touchStartX;

                // Swipe right to open (from left edge)
                if (touchStartX < 20 && swipeDistance > swipeThreshold && !sidebarRef.classList.contains('show')) {
                    sidebarRef.classList.add('show');
                    const overlay = document.getElementById('sidebarOverlay');
                    if (overlay) overlay.classList.add('show');
                }
                // Swipe left to close
                else if (swipeDistance < -swipeThreshold && sidebarRef.classList.contains('show')) {
                    sidebarRef.classList.remove('show');
                    const overlay = document.getElementById('sidebarOverlay');
                    if (overlay) overlay.classList.remove('show');
                }
            }

            // Wait for sidebar to be initialized
            setTimeout(function() {
                sidebarRef = document.querySelector('.sidebar');
            }, 100);

            document.addEventListener('touchstart', function(e) {
                if (isMobile()) {
                    touchStartX = e.changedTouches[0].screenX;
                }
            }, {
                passive: true
            });

            document.addEventListener('touchend', function(e) {
                if (isMobile()) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                }
            }, {
                passive: true
            });
        })();
    </script>
</body>

</html>