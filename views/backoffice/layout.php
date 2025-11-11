<?php
/**
 * Layout Template Backoffice
 */
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
$username = $_SESSION['backoffice_username'] ?? 'Admin';
$isDarkMode = isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1';
$currentAction = $_GET['action'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="<?php echo $isDarkMode ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - NUGold Backoffice</title>
    
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
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        [data-bs-theme="light"] .sidebar {
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 2px solid var(--gold-color);
            background: rgba(255, 199, 0, 0.1);
        }
        
        .sidebar-brand h4 {
            color: var(--gold-color);
            margin: 0.5rem 0 0 0;
            font-weight: bold;
        }
        
        .sidebar-brand small {
            color: var(--bs-secondary);
            font-size: 0.85rem;
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
            min-height: 100vh;
        }
        
        .topbar {
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--bs-body-bg);
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
            font-size: 0.9rem;
            color: var(--bs-secondary);
            margin-bottom: 0.5rem;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            color: var(--gold-color);
            opacity: 0.7;
        }
        
        .btn-gold {
            background: var(--gold-color);
            color: #000;
            border: none;
            font-weight: bold;
        }
        
        .btn-gold:hover {
            background: var(--gold-dark);
            color: #000;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shield-lock" style="font-size: 2rem; color: var(--gold-color);"></i>
            <h4>Backoffice</h4>
            <small>NUGold Admin</small>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo BASE_URL; ?>backoffice.php?action=dashboard" 
                   class="<?php echo $currentAction === 'dashboard' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>backoffice.php?action=users" 
                   class="<?php echo $currentAction === 'users' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Manajemen User
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>backoffice.php?action=harga_emas" 
                   class="<?php echo $currentAction === 'harga_emas' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i> Harga Emas
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>backoffice.php?action=statistik" 
                   class="<?php echo $currentAction === 'statistik' ? 'active' : ''; ?>">
                    <i class="bi bi-bar-chart"></i> Statistik
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Lihat Aplikasi
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>backoffice.php?action=logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h4 class="mb-0 d-none d-md-inline"><?php echo htmlspecialchars($pageTitle); ?></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary" id="darkModeToggle" title="Toggle Dark Mode">
                    <i class="bi bi-<?php echo $isDarkMode ? 'sun' : 'moon'; ?>"></i>
                </button>
                <span class="d-none d-md-inline">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($username); ?>
                </span>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </div>
    
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
        
        // Sidebar Toggle (Mobile)
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>

