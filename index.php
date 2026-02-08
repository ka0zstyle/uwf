<?php
// Language detection and loading
$current_lang = 'en'; // default language

// Check for language parameter in URL
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es'])) {
    $current_lang = $_GET['lang'];
    setcookie('uwf_lang', $current_lang, time() + (86400 * 30), "/"); // 30 days
}
// Check for language cookie
elseif (isset($_COOKIE['uwf_lang']) && in_array($_COOKIE['uwf_lang'], ['en', 'es'])) {
    $current_lang = $_COOKIE['uwf_lang'];
}

// Load language file
require_once __DIR__ . '/lang/' . $current_lang . '.php';

// Load skills loader
require_once __DIR__ . '/skills_loader.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

  <head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo htmlspecialchars($lang['meta_description']); ?>">
    <meta name="author" content="UltraWebForge">
    <meta name="theme-color" content="#03a4ed">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="assets/css/uwf-main.min.css" as="style">
    <link rel="preload" href="assets/images/banner-right-image.webp" as="image">
    <link rel="preload" href="vendor/jquery/jquery.min.js" as="script">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <title><?php echo htmlspecialchars($lang['meta_title']); ?></title>

    <!-- Bootstrap core CSS - deferred to prevent render blocking -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="print" onload="this.media='all'; this.onload=null;">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css" media="print" onload="this.media='all'; this.onload=null;">
    <link rel="stylesheet" href="assets/css/uwf-main.min.css">
    <link rel="stylesheet" href="assets/css/animated.min.css" media="print" onload="this.media='all'; this.onload=null;">
    <link rel="stylesheet" href="assets/css/chat.min.css" media="print" onload="this.media='all'; this.onload=null;">
    <link rel="stylesheet" href="assets/css/language.min.css" media="print" onload="this.media='all'; this.onload=null;">
    <link rel="stylesheet" href="assets/css/skills-manager.css" media="print" onload="this.media='all'; this.onload=null;">
    
    <!-- Noscript fallback for deferred CSS -->
    <noscript>
      <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
      <link rel="stylesheet" href="assets/css/fontawesome.css">
      <link rel="stylesheet" href="assets/css/animated.min.css">
      <link rel="stylesheet" href="assets/css/chat.min.css">
      <link rel="stylesheet" href="assets/css/language.min.css">
      <link rel="stylesheet" href="assets/css/skills-manager.css">
    </noscript>
    
    <style>
      /* Logo Image Styling */
      .logo-img {
        max-height: 60px;
        width: auto;
      }
    </style>
  </head>

<body>

  <!-- Language Splash Overlay -->
  <div id="language-splash">
    <div class="splash-content">
      <img src="assets/images/ultrawebforge.webp" alt="UltraWebForge" class="splash-logo" width="600" height="111">
      <h2 class="splash-slogan"><?php echo $lang['splash_slogan']; ?></h2>
      <h3 class="splash-title"><?php echo htmlspecialchars($lang['splash_title']); ?></h3>
      <p class="splash-subtitle"><?php echo htmlspecialchars($lang['splash_subtitle']); ?></p>
      <div class="language-options">
        <a href="?lang=en" class="language-option" onclick="selectLanguage('en'); return false;">
          <img src="assets/images/flag-us.webp" alt="English" width="100" height="100">
          <span><?php echo htmlspecialchars($lang['splash_english']); ?></span>
        </a>
        <a href="?lang=es" class="language-option" onclick="selectLanguage('es'); return false;">
          <img src="assets/images/flag-es.webp" alt="Español" width="100" height="100">
          <span><?php echo htmlspecialchars($lang['splash_spanish']); ?></span>
        </a>
      </div>
    </div>
  </div>
  
  <script>
    function selectLanguage(lang) {
      localStorage.setItem('uwf_lang_selected', 'true');
      // Set cookie and redirect immediately
      document.cookie = 'uwf_lang=' + encodeURIComponent(lang) + '; path=/; max-age=' + (86400 * 30);
      window.location.href = '?lang=' + encodeURIComponent(lang);
    }
    
    // Hide splash if user has already selected language
    if (localStorage.getItem('uwf_lang_selected')) {
      document.getElementById('language-splash').classList.add('hidden');
    }
  </script>

  <!-- ***** Preloader Start ***** -->
  <div id="js-preloader" class="js-preloader">
    <div class="preloader-inner">
      <span class="dot"></span>
      <div class="dots">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>
  <!-- ***** Preloader End ***** -->

  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky wow slideInDown" data-wow-duration="0.75s" data-wow-delay="0s">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <nav class="main-nav">
            <!-- ***** Logo Start ***** -->
            <a href="index.php" class="logo">
              <img src="assets/images/ultrawebforge.webp" alt="UltraWebForge" class="logo-img" width="600" height="111">
            </a>
            <!-- ***** Logo End ***** -->
            <!-- ***** Menu Start ***** -->
            <ul class="nav">
              <li class="scroll-to-section"><a href="#top" class="active"><?php echo htmlspecialchars($lang['nav_home']); ?></a></li>
              <li class="scroll-to-section"><a href="#about"><?php echo htmlspecialchars($lang['nav_about']); ?></a></li>
              <li class="scroll-to-section"><a href="#services"><?php echo htmlspecialchars($lang['nav_services']); ?></a></li>
              <li class="scroll-to-section"><a href="#portfolio"><?php echo htmlspecialchars($lang['nav_portfolio']); ?></a></li>
              <li class="scroll-to-section"><a href="#pricing"><?php echo htmlspecialchars($lang['nav_pricing']); ?></a></li>
              <li class="scroll-to-section"><a href="#blog"><?php echo htmlspecialchars($lang['nav_blog']); ?></a></li> 
              <li class="scroll-to-section"><a href="#contact"><?php echo htmlspecialchars($lang['nav_contact']); ?></a></li>
              <li class="scroll-to-section language-switcher">
                <span class="language-switcher-label"><?php echo htmlspecialchars($lang['language_label']); ?>:</span>
                <a href="?lang=en" class="<?php echo $current_lang === 'en' ? 'active' : ''; ?>">
                  <img src="assets/images/flag-us.webp" alt="English" width="100" height="100">
                </a>
                <a href="?lang=es" class="<?php echo $current_lang === 'es' ? 'active' : ''; ?>">
                  <img src="assets/images/flag-es.webp" alt="Español" width="100" height="100">
                </a>
              </li>
            </ul>        
            <a class='menu-trigger'>
                <span></span>
            </a>
            <!-- ***** Menu End ***** -->
          </nav>
        </div>
      </div>
    </div>
  </header>
  <!-- ***** Header Area End ***** -->

  <div class="main-banner wow fadeIn" id="top" data-wow-duration="1s" data-wow-delay="0.5s">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-lg-6 align-self-center">
              <div class="left-content header-text wow fadeInLeft" data-wow-duration="1s" data-wow-delay="1s">
                <h6><?php echo htmlspecialchars($lang['hero_welcome']); ?></h6>
                <h2><?php echo $lang['hero_title']; ?></h2>
                <p><?php echo htmlspecialchars($lang['hero_description']); ?></p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="right-image wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.5s">
                <img src="assets/images/banner-right-image.webp" alt="team meeting" width="622" height="591">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="about" class="about-us section">
    <div class="container">
      <div class="row">
        <div class="col-lg-4">
          <div class="left-image wow fadeIn" data-wow-duration="1s" data-wow-delay="0.2s">
            <img loading="lazy" src="assets/images/about-left-image.webp" alt="person graphic" width="331" height="383">
          </div>
        </div>
        <div class="col-lg-8 align-self-center">
          <div class="services">
            <div class="row">
              <div class="col-lg-6">
                <div class="item wow fadeIn" data-wow-duration="1s" data-wow-delay="0.5s">
                  <div class="icon">
                    <img src="assets/images/service-icon-01.webp" alt="reporting" width="70" height="70">
                  </div>
                  <div class="right-text">
                    <h4><?php echo htmlspecialchars($lang['service_1_title']); ?></h4>
                    <p><?php echo htmlspecialchars($lang['service_1_description']); ?></p>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="item wow fadeIn" data-wow-duration="1s" data-wow-delay="0.7s">
                  <div class="icon">
                    <img src="assets/images/service-icon-02.webp" alt="" width="70" height="70">
                  </div>
                  <div class="right-text">
                    <h4><?php echo htmlspecialchars($lang['service_2_title']); ?></h4>
                    <p><?php echo htmlspecialchars($lang['service_2_description']); ?></p>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="item wow fadeIn" data-wow-duration="1s" data-wow-delay="0.9s">
                  <div class="icon">
                    <img src="assets/images/service-icon-03.webp" alt="" width="70" height="70">
                  </div>
                  <div class="right-text">
                    <h4><?php echo htmlspecialchars($lang['service_3_title']); ?></h4>
                    <p><?php echo htmlspecialchars($lang['service_3_description']); ?></p>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="item wow fadeIn" data-wow-duration="1s" data-wow-delay="1.1s">
                  <div class="icon">
                    <img src="assets/images/service-icon-04.webp" alt="" width="70" height="70">
                  </div>
                  <div class="right-text">
                    <h4><?php echo htmlspecialchars($lang['service_4_title']); ?></h4>
                    <p><?php echo htmlspecialchars($lang['service_4_description']); ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="services" class="our-services section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 align-self-center  wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.2s">
          <div class="left-image">
            <img loading="lazy" src="assets/images/services-left-image.webp" alt="" width="570" height="399">
          </div>
        </div>
        <div class="col-lg-6 wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.2s">
          <div class="section-heading">
            <h2><?php echo $lang['services_detail_title']; ?></h2>
            <p><?php echo htmlspecialchars($lang['services_detail_description']); ?></p>
          </div>
          <div class="row" id="skills-bars-container">
            <?php 
            $skills = loadSkills();
            foreach ($skills as $index => $skill): 
              $skillName = $current_lang === 'es' ? $skill['name_es'] : $skill['name_en'];
              $skillClass = getSkillClass($index);
            ?>
            <div class="col-lg-12">
              <div class="<?php echo $skillClass; ?> progress-skill-bar">
                <h4><?php echo htmlspecialchars($skillName); ?></h4>
                <span><?php echo intval($skill['percentage']); ?>%</span>
                <div class="filled-bar"></div>
                <div class="full-bar"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="portfolio" class="our-portfolio section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 offset-lg-3">
          <div class="section-heading  wow bounceIn" data-wow-duration="1s" data-wow-delay="0.2s">
            <h2><?php echo $lang['portfolio_title']; ?></h2>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3 col-sm-6">
          <a href="#">
            <div class="item wow bounceInUp" data-wow-duration="1s" data-wow-delay="0.3s">
              <div class="hidden-content">
                <h4><?php echo htmlspecialchars($lang['portfolio_item_1_title']); ?></h4>
                <p><?php echo htmlspecialchars($lang['portfolio_item_1_desc']); ?></p>
              </div>
              <div class="showed-content">
                <img loading="lazy" src="assets/images/portfolio-image.webp" alt="" width="100" height="113">
              </div>
            </div>
          </a>
        </div>
        <div class="col-lg-3 col-sm-6">
          <a href="#">
            <div class="item wow bounceInUp" data-wow-duration="1s" data-wow-delay="0.4s">
              <div class="hidden-content">
                <h4><?php echo htmlspecialchars($lang['portfolio_item_2_title']); ?></h4>
                <p><?php echo htmlspecialchars($lang['portfolio_item_2_desc']); ?></p>
              </div>
              <div class="showed-content">
                <img loading="lazy" src="assets/images/portfolio-image.webp" alt="" width="100" height="113">
              </div>
            </div>
          </a>
        </div>
        <div class="col-lg-3 col-sm-6">
          <a href="#">
            <div class="item wow bounceInUp" data-wow-duration="1s" data-wow-delay="0.5s">
              <div class="hidden-content">
                <h4><?php echo htmlspecialchars($lang['portfolio_item_3_title']); ?></h4>
                <p><?php echo htmlspecialchars($lang['portfolio_item_3_desc']); ?></p>
              </div>
              <div class="showed-content">
                <img loading="lazy" src="assets/images/portfolio-image.webp" alt="" width="100" height="113">
              </div>
            </div>
          </a>
        </div>
        <div class="col-lg-3 col-sm-6">
          <a href="#">
            <div class="item wow bounceInUp" data-wow-duration="1s" data-wow-delay="0.6s">
              <div class="hidden-content">
                <h4><?php echo htmlspecialchars($lang['portfolio_item_4_title']); ?></h4>
                <p><?php echo htmlspecialchars($lang['portfolio_item_4_desc']); ?></p>
              </div>
              <div class="showed-content">
                <img loading="lazy" src="assets/images/portfolio-image.webp" alt="" width="100" height="113">
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Pricing Section -->
  <div id="pricing" class="section">
    <div class="pricing-container">
      <div class="section-heading pricing-heading wow fadeInDown" data-wow-duration="1s" data-wow-delay="0.2s">
        <h2><?php echo $lang['pricing_title']; ?></h2>
        <p><?php echo htmlspecialchars($lang['pricing_subtitle']); ?></p>
      </div>
      
      <div class="pricing-cards">
        <!-- Starter Plan -->
        <div class="pricing-card wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.3s">
          <div class="pricing-header">
            <div class="pricing-name"><?php echo htmlspecialchars($lang['pricing_plan_1_name']); ?></div>
            <div class="pricing-price"><?php echo htmlspecialchars($lang['pricing_plan_1_price']); ?></div>
            <div class="pricing-period"><?php echo htmlspecialchars($lang['pricing_plan_1_period']); ?></div>
          </div>
          <ul class="pricing-features">
            <li><?php echo htmlspecialchars($lang['pricing_plan_1_feature_1']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_1_feature_2']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_1_feature_3']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_1_feature_4']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_1_feature_5']); ?></li>
          </ul>
          <a href="#contact" class="pricing-button"><?php echo htmlspecialchars($lang['pricing_button']); ?></a>
        </div>
        
        <!-- Professional Plan (Featured) -->
        <div class="pricing-card featured wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.4s">
          <div class="pricing-badge"><?php echo htmlspecialchars($lang['pricing_plan_2_badge']); ?></div>
          <div class="pricing-header">
            <div class="pricing-name"><?php echo htmlspecialchars($lang['pricing_plan_2_name']); ?></div>
            <div class="pricing-price"><?php echo htmlspecialchars($lang['pricing_plan_2_price']); ?></div>
            <div class="pricing-period"><?php echo htmlspecialchars($lang['pricing_plan_2_period']); ?></div>
          </div>
          <ul class="pricing-features">
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_1']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_2']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_3']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_4']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_5']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_2_feature_6']); ?></li>
          </ul>
          <a href="#contact" class="pricing-button"><?php echo htmlspecialchars($lang['pricing_button']); ?></a>
        </div>
        
        <!-- Enterprise Plan -->
        <div class="pricing-card wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.5s">
          <div class="pricing-header">
            <div class="pricing-name"><?php echo htmlspecialchars($lang['pricing_plan_3_name']); ?></div>
            <div class="pricing-price"><?php echo htmlspecialchars($lang['pricing_plan_3_price']); ?></div>
            <div class="pricing-period"><?php echo htmlspecialchars($lang['pricing_plan_3_period']); ?></div>
          </div>
          <ul class="pricing-features">
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_1']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_2']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_3']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_4']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_5']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_6']); ?></li>
            <li><?php echo htmlspecialchars($lang['pricing_plan_3_feature_7']); ?></li>
          </ul>
          <a href="#contact" class="pricing-button"><?php echo htmlspecialchars($lang['pricing_button']); ?></a>
        </div>
      </div>
    </div>
  </div>

  <div id="blog" class="our-blog section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 wow fadeInDown" data-wow-duration="1s" data-wow-delay="0.25s">
          <div class="section-heading">
            <h2><?php echo $lang['blog_title']; ?></h2>
          </div>
        </div>
        <div class="col-lg-6 wow fadeInDown" data-wow-duration="1s" data-wow-delay="0.25s">
          <div class="top-dec">
            <img loading="lazy" src="assets/images/blog-dec.webp" alt="" width="204" height="213">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-6 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.25s">
          <div class="left-image">
            <a href="#"><img loading="lazy" src="assets/images/big-blog-thumb.webp" alt="Workspace Desktop" width="570" height="501"></a>
            <div class="info">
              <div class="inner-content">
                <ul>
                  <li><i class="fa fa-calendar"></i> 24 Mar 2024</li>
                  <li><i class="fa fa-users"></i> UltraWebForge</li>
                  <li><i class="fa fa-folder"></i> Branding</li>
                </ul>
                <a href="#"><h4><?php echo htmlspecialchars($lang['service_4_title']); ?></h4></a>
                <p><?php echo htmlspecialchars($lang['service_4_description']); ?></p>
                <div class="main-blue-button">
                  <a href="#"><?php echo htmlspecialchars($lang['pricing_button']); ?></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.25s">
          <div class="right-list">
            <ul>
              <li>
                <div class="left-content align-self-center">
                  <span><i class="fa fa-calendar"></i> 18 Mar 2024</span>
                  <a href="#"><h4><?php echo htmlspecialchars($lang['service_1_title']); ?></h4></a>
                  <p><?php echo htmlspecialchars($lang['service_1_description']); ?></p>
                </div>
                <div class="right-image">
                  <a href="#"><img loading="lazy" src="assets/images/blog-thumb-01.webp" alt="" width="252" height="219"></a>
                </div>
              </li>
              <li>
                <div class="left-content align-self-center">
                  <span><i class="fa fa-calendar"></i> 14 Mar 2024</span>
                  <a href="#"><h4><?php echo htmlspecialchars($lang['service_3_title']); ?></h4></a>
                  <p><?php echo htmlspecialchars($lang['service_3_description']); ?></p>
                </div>
                <div class="right-image">
                  <a href="#"><img loading="lazy" src="assets/images/blog-thumb-01.webp" alt="" width="252" height="219"></a>
                </div>
              </li>
              <li>
                <div class="left-content align-self-center">
                  <span><i class="fa fa-calendar"></i> 06 Mar 2024</span>
                  <a href="#"><h4><?php echo htmlspecialchars($lang['service_2_title']); ?></h4></a>
                  <p><?php echo htmlspecialchars($lang['service_2_description']); ?></p>
                </div>
                <div class="right-image">
                  <a href="#"><img loading="lazy" src="assets/images/blog-thumb-01.webp" alt="" width="252" height="219"></a>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="contact" class="contact-us section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 align-self-center wow fadeInLeft" data-wow-duration="0.5s" data-wow-delay="0.25s">
          <div class="section-heading">
            <h2><?php echo htmlspecialchars($lang['contact_title']); ?></h2>
            <p><?php echo htmlspecialchars($lang['contact_description']); ?></p>
            <div class="phone-info">
              <h4><?php echo htmlspecialchars($lang['contact_phone_label']); ?> <span><i class="fa fa-phone"></i> <a href="tel:<?php echo htmlspecialchars($lang['contact_phone']); ?>"><?php echo htmlspecialchars($lang['contact_phone']); ?></a></span></h4>
            </div>
          </div>
        </div>
        <div class="col-lg-6 wow fadeInRight" data-wow-duration="0.5s" data-wow-delay="0.25s">
          <form id="contact" action="send_mail.php" method="post">
            <input type="hidden" name="lang" value="<?php echo $current_lang; ?>">
            <div class="row">
              <div class="col-lg-12">
                <fieldset>
                  <input type="text" name="name" id="name" placeholder="<?php echo htmlspecialchars($lang['form_name']); ?>" autocomplete="on" required>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <input type="email" name="email" id="email" placeholder="<?php echo htmlspecialchars($lang['form_email']); ?>" required>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <select name="country" id="country" class="form-control" aria-label="<?php echo htmlspecialchars($lang['form_country']); ?>" required>
                    <option value=""><?php echo htmlspecialchars($lang['country_select']); ?></option>
                    <option value="US"><?php echo htmlspecialchars($lang['country_us']); ?></option>
                    <option value="UK"><?php echo htmlspecialchars($lang['country_uk']); ?></option>
                    <option value="CA"><?php echo htmlspecialchars($lang['country_ca']); ?></option>
                    <option value="AU"><?php echo htmlspecialchars($lang['country_au']); ?></option>
                    <option value="DE"><?php echo htmlspecialchars($lang['country_de']); ?></option>
                    <option value="FR"><?php echo htmlspecialchars($lang['country_fr']); ?></option>
                    <option value="IT"><?php echo htmlspecialchars($lang['country_it']); ?></option>
                    <option value="BR"><?php echo htmlspecialchars($lang['country_br']); ?></option>
                    <option value="ES"><?php echo htmlspecialchars($lang['country_es']); ?></option>
                    <option value="MX"><?php echo htmlspecialchars($lang['country_mx']); ?></option>
                    <option value="AR"><?php echo htmlspecialchars($lang['country_ar']); ?></option>
                    <option value="CO"><?php echo htmlspecialchars($lang['country_co']); ?></option>
                    <option value="CL"><?php echo htmlspecialchars($lang['country_cl']); ?></option>
                    <option value="PE"><?php echo htmlspecialchars($lang['country_pe']); ?></option>
                    <option value="VE"><?php echo htmlspecialchars($lang['country_ve']); ?></option>
                    <option value="EC"><?php echo htmlspecialchars($lang['country_ec']); ?></option>
                    <option value="UY"><?php echo htmlspecialchars($lang['country_uy']); ?></option>
                    <option value="PA"><?php echo htmlspecialchars($lang['country_pa']); ?></option>
                    <option value="CR"><?php echo htmlspecialchars($lang['country_cr']); ?></option>
                    <option value="OTHER"><?php echo htmlspecialchars($lang['country_other']); ?></option>
                  </select>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <input type="tel" name="whatsapp" id="whatsapp" placeholder="<?php echo htmlspecialchars($lang['form_whatsapp']); ?>" autocomplete="tel" required>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <textarea name="message" class="form-control" id="message" placeholder="<?php echo htmlspecialchars($lang['form_message']); ?>" required></textarea>  
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <button type="submit" id="form-submit" class="main-button "><?php echo htmlspecialchars($lang['form_submit']); ?></button>
                </fieldset>
              </div>
            </div>
            <div class="contact-dec">
              <img loading="lazy" src="assets/images/contact-decoration.webp" alt="" width="161" height="163">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll to Top Button -->
  <div id="scroll-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.scrollTo({top: 0, behavior: 'smooth'});}" aria-label="Scroll to top" role="button" tabindex="0" style="display: none;">
    <i class="fa fa-angle-up" style="font-size: 28px; color: white;"></i>
  </div>

  <!-- Live Chat Integration -->
  <!-- Chat Trigger Button -->
  <div id="chat-trigger" onclick="window.toggleChat && window.toggleChat()" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.toggleChat && window.toggleChat();}" aria-label="Open chat" role="button" tabindex="0">
    <i class="fa fa-comments" style="font-size: 28px; color: white;"></i>
    <span id="chat-notif-badge" class="notif-badge" style="display: none;">0</span>
  </div>

  <!-- Support Online Notification Bubble -->
  <div id="chat-support-bubble" class="support-online-bubble">
    <span class="support-bubble-dot"></span>
    <span class="support-bubble-text"><?php echo htmlspecialchars($lang['chat_support_online']); ?></span>
  </div>

  <!-- Chat Container -->
  <div id="uwf-chat-container" class="chat-closed" role="dialog" aria-labelledby="chat-header-title" aria-modal="false">
    <!-- Chat Header -->
    <div class="chat-header" id="chat-header">
      <span id="chat-header-title" role="button" tabindex="0"
            onclick="(window.toggleChat || function(){})();"
            onkeypress="if (event.key==='Enter'||event.key===' ') (window.toggleChat||function(){})();">
        <?php echo htmlspecialchars($lang['chat_header_title']); ?>
      </span>
      <div class="chat-controls">
        <button id="toggle-sound" type="button" aria-pressed="false" title="<?php echo htmlspecialchars($lang['chat_mute_label']); ?>"
                onclick="event.stopPropagation();">
          <i class="fa fa-bell" aria-hidden="true"></i>
        </button>
        <button id="toggle-settings" type="button" aria-label="<?php echo htmlspecialchars($lang['chat_settings_label']); ?>" title="<?php echo htmlspecialchars($lang['chat_settings_label']); ?>"
                onclick="event.stopPropagation(); (window.openChatSettings || function(){})();">
          <i class="fa fa-cog" aria-hidden="true"></i>
        </button>
        <button id="toggle-fullscreen" type="button" aria-pressed="false" aria-label="<?php echo htmlspecialchars($lang['chat_expand_label']); ?>" title="<?php echo htmlspecialchars($lang['chat_expand_label']); ?>"
                onclick="event.stopPropagation(); (window.toggleFullScreen || function(){})();">
          <i class="fa fa-expand" aria-hidden="true"></i>
        </button>
        <button id="toggle-min" type="button" aria-label="<?php echo htmlspecialchars($lang['chat_minimize_label']); ?>"
                onclick="event.stopPropagation(); (window.toggleChat || function(){})();">
          <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <!-- Pre-Chat Form -->
    <form id="pre-chat-form" onsubmit="return window.startChatSession(event)" style="display: flex;">
      <div class="chat-field">
        <label for="chat-name"><?php echo htmlspecialchars($lang['chat_name_label']); ?></label>
        <input type="text" id="chat-name" name="name" placeholder="<?php echo htmlspecialchars($lang['chat_name_placeholder']); ?>" required minlength="2">
      </div>
      <div class="chat-field">
        <label for="u-email"><?php echo htmlspecialchars($lang['chat_email_label']); ?></label>
        <input type="email" id="u-email" name="email" placeholder="<?php echo htmlspecialchars($lang['chat_email_placeholder']); ?>" required>
      </div>
      <div class="chat-field">
        <label for="chat-phone"><?php echo htmlspecialchars($lang['chat_phone_label']); ?></label>
        <input type="tel" id="chat-phone" name="phone" placeholder="<?php echo htmlspecialchars($lang['chat_phone_placeholder']); ?>">
      </div>
      <button type="submit" class="btn-start-chat"><?php echo htmlspecialchars($lang['chat_start_button']); ?></button>
    </form>

    <!-- Main Chat Area -->
    <div id="chat-main-area" style="display: none;">
      <!-- Admin Status -->
      <div class="admin-status">
        <span class="online-dot" aria-hidden="true"></span>
        <span><?php echo htmlspecialchars($lang['chat_online_status']); ?></span>
      </div>
      
      <!-- Chat History -->
      <div id="chat-history" role="log" aria-live="polite" aria-label="Chat messages">
        <!-- Messages will be loaded here -->
      </div>

      <!-- Typing Indicator -->
      <div id="chat-status-indicator" aria-live="polite" aria-atomic="true"></div>

      <!-- Chat Input -->
      <div class="chat-input-bar">
        <input type="text" id="u-msg" placeholder="<?php echo htmlspecialchars($lang['chat_message_placeholder']); ?>" aria-label="<?php echo htmlspecialchars($lang['chat_message_placeholder']); ?>" autocomplete="off">
        <button type="button" class="btn-send-chat" aria-label="<?php echo htmlspecialchars($lang['chat_send_label']); ?>">
          <i class="fa fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Chat Toast Notification -->
  <div id="chat-toast" role="alert" aria-live="polite"></div>

  <footer>
    <div class="container">
      <div class="row">
        <div class="col-lg-12 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.25s">
          <p><?php echo htmlspecialchars($lang['footer_text']); ?></p>
        </div>
      </div>
    </div>
  </footer>
  <!-- Scripts -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
  <script src="assets/js/animation.js" defer></script>
  <script src="assets/js/imagesloaded.js" defer></script>
  <script src="assets/js/uwf-custom.js" defer></script>
  <script src="assets/js/chat.js" defer></script>
  <script src="assets/js/skills-manager.js" defer></script>

</body>
</html>