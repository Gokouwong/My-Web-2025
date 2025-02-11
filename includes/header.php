<?php
echo "<!-- Loading header.php -->\n";
// Get current page name without .php extension
$current_page = basename($_SERVER['PHP_SELF']);
$current_page = str_replace('.php', '', $current_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-NS7MD78J0V"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-NS7MD78J0V');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Header styles */
        .header-nav {
            background: #333;
            height: 60px; /* Fixed height */
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .nav-container {
            margin: 0;
            padding: 0 30px 0 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo img {
            height: 50px;
            width: auto;
            margin-right: 1rem;
            margin-top:25px;
        }

        .logo-text {
            color: #fff;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .burger-menu {
            display: none;
            cursor: pointer;
        }

        .burger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        .nav-menu-container {
            display: flex;
            align-items: center;
            margin-left: auto;
            gap: 2rem;
        }

        .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav-links a:hover {
            background-color: #555;
        }

        .nav-links a.active {
            background-color: #007bff;
        }

        /* Global container styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        /* Section styles */
        section {
            padding: 3rem 0;
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        p {
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Image styles */
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem;
        }

        /* Introduction section */
        .introduction {
            background-color: #f8f9fa;
            padding: 4rem 0;
        }

        .intro-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .intro-image {
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Products grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .product-card {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Forms */
        form {
            max-width: 600px;
            margin: 0 auto;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background: #0000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        button:hover {
            opacity: 0.9;
        }

        /* Keep desktop styles unchanged */
        @media (min-width: 501px) {
            .nav-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nav-links {
                display: flex;
                gap: 20px;
            }

            .burger-menu {
                display: none;
            }
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .burger-menu {
                display: block;
                margin-left: auto; /* Keep burger menu on the right */
            }

            .nav-links {
                display: none;
                width: 100%;
                position: absolute;
                top: 100%;
                left: 0;
                background: #333;
                flex-direction: column;
                padding: 20px 0;
                margin-left: 0; /* Reset margin for mobile menu */
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                margin: 10px 0;
            }

            .nav-container {
                padding: 0 20px;
            }
            
            .nav-links {
                margin-right: 0;
            }

            .nav-menu-container {
                gap: 1rem;
            }
            
            .language-switcher {
                display: none;
            }

            .burger-menu {
                display: block;
                margin-left: auto;
            }
        }

        :root {
            --primary-color: #2c3e50;
        }

        .nav-links li a.active {
            background-color: var(--secondary-color);
        }

        .language-switcher {
            position: relative;
            display: inline-block;
        }

        .language-switcher .current-lang {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            color: white;
            cursor: pointer;
            background: none;
            border: none;
        }

        .language-switcher .current-lang:after {
            content: '▼';
            font-size: 0.8em;
        }

        .language-switcher .lang-options {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #333;
            border-radius: 4px;
            padding: 0.5rem;
            z-index: 1000;
        }

        .language-switcher:hover .lang-options {
            display: block;
        }

        .language-switcher .lang-btn {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            text-align: left;
            background: #0000;
            border: none;
            color: white;
            cursor: pointer;
            white-space: nowrap;
        }

        .language-switcher .lang-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .language-switcher .lang-btn.active {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Hide mobile language switcher by default */
        .language-switcher-mobile {
            display: none;
        }

        @media (max-width: 768px) {
            .language-switcher {
                display: none; /* Hide desktop language switcher */
            }

            .burger-menu {
                display: block;
                margin-left: auto;
            }

            .nav-links {
                display: none;
                width: 100%;
                position: absolute;
                top: 100%;
                left: 0;
                background: #333;
                flex-direction: column;
                padding: 20px 0;
                margin-left: 0;
            }

            /* Mobile language switcher */
            .language-switcher-mobile {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        /* Mobile language switcher styles */
        .language-switcher-mobile .lang-btn {
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }

        .language-switcher-mobile .lang-btn.active {
            background: #fff;
            color: #333;
            border-color: #fff;
        }

        @media (max-width: 768px) {
            .nav-container {
                padding: 0 20px;
            }
            
            .language-switcher {
                display: none;
            }

            .language-switcher-mobile {
                display: flex;
                gap: 0.5rem;
                margin: 1rem 0;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: color #333; ;
                padding: 1rem;
                box-shadow: var(--shadow);
            }
        }
    </style>
</head>
<body>
    <nav class="header-nav">
        <div class="nav-container">
            <a href="index" class="logo">
                <img src="uploads/user_images/RAKKO.jpg" alt="RAKKO Logo">
                <span class="logo-text">TEST WEB 2025</span>
            </a>
            <div class="nav-menu-container">
                <ul class="nav-links">
                    <li><a href="index" <?php echo $current_page === 'index' ? 'class="active"' : ''; ?>><?php echo t('home'); ?></a></li>
                    <li><a href="about" <?php echo $current_page === 'about' ? 'class="active"' : ''; ?>><?php echo t('about'); ?></a></li>
                    <li><a href="products-list" <?php echo $current_page === 'products-list' ? 'class="active"' : ''; ?>><?php echo t('products'); ?></a></li>
                    <li><a href="past-events" <?php echo $current_page === 'past-events' ? 'class="active"' : ''; ?>><?php echo t('past_events'); ?></a></li>
                    <li><a href="contact" <?php echo $current_page === 'contact' ? 'class="active"' : ''; ?>><?php echo t('contact'); ?></a></li>
                    <li><a href="upload" <?php echo $current_page === 'upload' ? 'class="active"' : ''; ?>><?php echo t('share_image'); ?></a></li>
                    <li class="language-switcher-mobile">
                        <button onclick="changeLanguage('en')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'en' ? 'active' : ''; ?>">EN</button>
                        <button onclick="changeLanguage('zh-cn')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'zh-cn' ? 'active' : ''; ?>">简体</button>
                        <button onclick="changeLanguage('zh-hk')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'zh-hk' ? 'active' : ''; ?>">繁體</button>
                    </li>
                </ul>
                <div class="language-switcher">
                    <button class="current-lang">
                        <?php 
                            $currentLang = $_SESSION['lang'] ?? 'zh-hk';
                            echo $currentLang === 'en' ? 'EN' : ($currentLang === 'zh-cn' ? '简体' : '繁體');
                        ?>
                    </button>
                    <div class="lang-options">
                        <button onclick="changeLanguage('en')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'en' ? 'active' : ''; ?>">English</button>
                        <button onclick="changeLanguage('zh-cn')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'zh-cn' ? 'active' : ''; ?>">简体中文</button>
                        <button onclick="changeLanguage('zh-hk')" class="lang-btn <?php echo ($_SESSION['lang'] ?? 'zh-hk') === 'zh-hk' ? 'active' : ''; ?>">繁體中文</button>
                    </div>
                </div>
                <div class="burger-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Language switching function
        function changeLanguage(lang) {
            document.cookie = 'lang=' + lang + ';path=/';
            location.reload();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const burgerMenu = document.querySelector('.burger-menu');
            const navLinks = document.querySelector('.nav-links');

            burgerMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                burgerMenu.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-container')) {
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
</body>
</html> 

<meta name="description" content="First Web 2025 RAKKOOOOOOOOOOO ROCKS!">
<meta name="keywords" content="Chiikawa, RAKKO, Gokou's Web, Web 2025">
<meta name="robots" content="index, follow">
