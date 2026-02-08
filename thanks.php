<?php
// Language detection
$current_lang = 'en';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es'])) {
    $current_lang = $_GET['lang'];
} elseif (isset($_COOKIE['uwf_lang']) && in_array($_COOKIE['uwf_lang'], ['en', 'es'])) {
    $current_lang = $_COOKIE['uwf_lang'];
}

// Load language file
require_once __DIR__ . '/lang/' . $current_lang . '.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['thanks_title']; ?> | UltraWebForge</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/uwf-main.css">
    <link rel="stylesheet" href="assets/css/animated.css">
    
    <style>
        body {
            background: linear-gradient(180deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }
        .thanks-container {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 40px 20px;
            position: relative;
            z-index: 2;
        }
        .success-icon {
            font-size: 80px;
            color: #4cd964;
            margin-bottom: 30px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .thanks-title {
            font-size: 48px;
            font-weight: 900;
            color: #fff;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .thanks-message {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn-back {
            display: inline-block;
            background: linear-gradient(105deg, #2b9fe6, #1e7bb8);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(43, 159, 230, 0.4);
        }
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(43, 159, 230, 0.6);
            color: white;
        }
        /* Animated background particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 8s infinite ease-in-out;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="thanks-container">
        <div class="success-icon">
            <i class="fa fa-check-circle"></i>
        </div>
        <h1 class="thanks-title"><?php echo $lang['thanks_title']; ?></h1>
        <p class="thanks-message">
            <?php echo $lang['thanks_message']; ?>
        </p>
        <a href="index.php?lang=<?php echo $current_lang; ?>" class="btn-back">
            <i class="fa fa-arrow-left"></i> <?php echo $lang['thanks_button']; ?>
        </a>
    </div>

    <script>
        // Create animated particles
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 8 + 's';
            particle.style.animationDuration = (8 + Math.random() * 4) + 's';
            particlesContainer.appendChild(particle);
        }
    </script>
</body>
</html>
