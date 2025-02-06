<?php
// Ensure user is logged in for all admin pages except login.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_page = str_replace('.php', '', $current_page);

if ($current_page !== 'login') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin CMS</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --text-light: #ffffff;
            --text-dark: #2c3e50;
            --background-light: #f8f9fa;
            --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --container-padding: 10rem;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-light);
        }

        .cms-nav {
            background-color: var(--primary-color);
            padding: 1rem var(--container-padding);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav-links li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-links li a:hover {
            background-color: var(--secondary-color);
            color: var(--text-light);
        }

        .nav-links li a.active {
            background-color: var(--secondary-color);
            color: var(--text-light);
        }

        .burger-menu {
            display: none;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
            padding: 8px;
        }

        .burger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: var(--text-light);
            transition: all 0.3s ease;
        }

        /* Desktop styles remain unchanged */
        @media (min-width: 769px) {
            .cms-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 4rem;
            }

            .nav-brand {
                margin-bottom: 0;
            }

            .nav-links {
                display: flex;
                gap: 2rem;
            }
        }

        @media (max-width: 1200px) {
            :root {
                --container-padding: 2rem;
            }
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .burger-menu {
                display: flex;
            }

            .cms-nav {
                padding: 1rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--primary-color);
                padding: 1rem;
                box-shadow: var(--shadow);
            }

            .nav-links.active {
                display: flex;
                flex-direction: column;
            }

            .nav-links li {
                width: 100%;
            }

            .nav-links li a {
                display: block;
                padding: 0.75rem 1rem;
            }

            /* Burger menu animation classes */
            .burger-menu.active span:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }

            .burger-menu.active span:nth-child(2) {
                opacity: 0;
            }

            .burger-menu.active span:nth-child(3) {
                transform: rotate(-45deg) translate(5px, -5px);
            }
        }
    </style>
</head>
<body>
    <?php if ($current_page !== 'login'): ?>
    <nav class="cms-nav">
        <div class="nav-brand">Admin CMS</div>
        <div class="burger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard"<?php echo $current_page === 'dashboard' ? ' class="active"' : ''; ?>>Dashboard</a></li>
            <li><a href="manage-banners"<?php echo $current_page === 'manage-banners' ? ' class="active"' : ''; ?>>Banner</a></li>
            <li><a href="update-content"<?php echo $current_page === 'update-content' ? ' class="active"' : ''; ?>>Homepage Content</a></li>
            <li><a href="manage-products"<?php echo $current_page === 'manage-products' ? ' class="active"' : ''; ?>>Products</a></li>
            <li><a href="manage-content"<?php echo $current_page === 'manage-content' ? ' class="active"' : ''; ?>>Content</a></li>
            <li><a href="manage-uploads"<?php echo $current_page === 'manage-uploads' ? ' class="active"' : ''; ?>>Uploads</a></li>
            <li><a href="manage-events"<?php echo $current_page === 'manage-events' ? ' class="active"' : ''; ?>>Past Events</a></li>
            <li><a href="logout"<?php echo $current_page === 'logout' ? ' class="active"' : ''; ?>>Logout</a></li>
        </ul>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.querySelector('.burger-menu');
            const navLinks = document.querySelector('.nav-links');

            burgerMenu.addEventListener('click', function() {
                burgerMenu.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.cms-nav')) {
                    burgerMenu.classList.remove('active');
                    navLinks.classList.remove('active');
                }
            });

            // Close menu when clicking a link
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    burgerMenu.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html> 