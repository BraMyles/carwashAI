<?php
require_once 'includes/init.php';

// Redirect if already logged in
if ($auth->isLoggedIn()) {
	header("Location: " . BASE_PATH . "dashboard.php");
    exit();
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($auth->login($username, $password)) {
            $current_user = $auth->getCurrentUser();
            // Check if user type matches (basic validation)
            if ($user_type === 'admin' && $current_user && $current_user['role'] === 'admin') {
                header("Location: " . BASE_PATH . "dashboard.php");
                exit();
            } elseif ($user_type === 'staff' && $current_user && $current_user['role'] === 'staff') {
                header("Location: " . BASE_PATH . "dashboard.php");
                exit();
            } else {
                $error = 'Invalid user type for this account.';
                $auth->logout(); // Logout the user if type doesn't match
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Car Wash Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern-style.css" rel="stylesheet">
    <style>
        :root {
            --neon-blue: #00d4ff;
            --neon-purple: #9d4edd;
            --neon-pink: #ff6b9d;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-color: rgba(0, 212, 255, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #533483 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 2rem 0;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.1); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .welcome-container {
            text-align: center;
            width: 100%;
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(45deg, #00d4ff, #9d4edd, #ff6b9d, #00d4ff);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease-in-out infinite;
            text-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
            margin-bottom: 1rem;
            letter-spacing: 2px;
            position: relative;
        }

        .welcome-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 4px;
            background: linear-gradient(90deg, transparent, #00d4ff, #9d4edd, transparent);
            border-radius: 2px;
            animation: glowPulse 2s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes glowPulse {
            0%, 100% { opacity: 0.7; box-shadow: 0 0 20px rgba(0, 212, 255, 0.5); }
            50% { opacity: 1; box-shadow: 0 0 40px rgba(0, 212, 255, 0.8); }
        }

        .welcome-subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-weight: 300;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            letter-spacing: 1px;
        }

        .ai-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .ai-feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .ai-feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .ai-feature-card:hover::before {
            left: 100%;
        }

        .ai-feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.3);
            border-color: var(--neon-blue);
        }

        .ai-feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 0 20px rgba(0, 212, 255, 0.5));
        }

        .ai-feature-card h3 {
            color: white;
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .ai-feature-card p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            font-size: 1rem;
        }

        .login-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(25px);
            border-radius: 30px;
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .login-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-section h3 {
            color: white;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 1.5rem;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            position: relative;
            z-index: 1;
        }

        .login-tabs {
            border: none;
            margin-bottom: 1rem;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .login-tabs .nav-link {
            border: none;
            border-radius: 15px;
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 120px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-tabs .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .login-tabs .nav-link.active {
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 212, 255, 0.4);
            border-color: var(--neon-blue);
        }

        .login-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.3);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
            outline: none;
        }

        .btn-login {
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            border: none;
            border-radius: 15px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            width: 100%;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 50px rgba(0, 212, 255, 0.4);
        }

        .credentials-info {
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            z-index: 1;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .floating-element {
            position: absolute;
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            border-radius: 50%;
            opacity: 0.1;
            animation: float 15s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 5s;
        }

        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); opacity: 0.1; }
            50% { transform: translateY(-30px) rotate(180deg) scale(1.1); opacity: 0.2; }
        }

        .neon-border {
            position: relative;
        }

        .neon-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple), var(--neon-pink), var(--neon-blue));
            border-radius: inherit;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        @media (max-width: 1200px) {
            .welcome-title {
                font-size: 3.5rem;
            }
            .ai-features-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 992px) {
            .hero-section {
                min-height: 100vh;
                padding: 1.5rem 0;
            }
            .welcome-title {
                font-size: 3rem;
            }
            .ai-features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin: 1rem 0;
            }
            .ai-feature-card {
                padding: 1.25rem;
            }
            .login-section {
                margin-top: 0.75rem;
                padding: 1.25rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 1rem 0;
            }
            .welcome-title {
                font-size: 2.5rem;
            }
            .welcome-subtitle {
                font-size: 1.2rem;
            }
            .welcome-container {
                margin-bottom: 1.5rem;
            }
            .ai-features-grid {
                gap: 0.75rem;
                margin: 0.75rem 0;
            }
            .ai-feature-card {
                padding: 1rem;
            }
            .login-section {
                margin-top: 0.5rem;
                padding: 1rem;
            }
            .login-section h3 {
                font-size: 1.3rem;
                margin-bottom: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 0.75rem 0;
            }
            .welcome-title {
                font-size: 1.8rem;
            }
            .welcome-subtitle {
                font-size: 1rem;
            }
            .welcome-container {
                margin-bottom: 1rem;
            }
            .ai-features-grid {
                gap: 0.5rem;
                margin: 0.5rem 0;
            }
            .ai-feature-card {
                padding: 0.75rem;
            }
            .ai-feature-card h3 {
                font-size: 1.2rem;
                margin-bottom: 0.5rem;
            }
            .ai-feature-card p {
                font-size: 0.85rem;
            }
            .login-section {
                padding: 0.75rem;
                margin-top: 0.25rem;
            }
            .login-section h3 {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }
            .login-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                min-width: 80px;
            }
            .login-form {
                padding: 0.75rem;
            }
            .form-control {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                margin-bottom: 0.5rem;
            }
            .btn-login {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="container hero-content">
            <div class="row align-items-center h-100">
                <div class="col-12">
                    <div class="welcome-container">
                        <h1 class="welcome-title">
                            🚗 Welcome to CarWash
                        </h1>
                        <p class="welcome-subtitle">
                            Professional Car Wash Management System
                        </p>
                    </div>
                    
                    <div class="ai-features-grid">
                        <div class="ai-feature-card neon-border">
                            <div class="ai-feature-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <h3>Smart Booking System</h3>
                            <p>Advanced scheduling system with real-time availability tracking and automated appointment management for seamless car wash services.</p>
                        </div>
                        
                        <div class="ai-feature-card neon-border">
                            <div class="ai-feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Revenue Analytics</h3>
                            <p>Comprehensive financial tracking with detailed reports on daily earnings, popular services, and customer payment trends.</p>
                        </div>
                        
                        <div class="ai-feature-card neon-border">
                            <div class="ai-feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Customer Management</h3>
                            <p>Complete customer database with service history, preferences tracking, and automated follow-up for repeat business.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mx-auto">
                    <div class="login-section neon-border">
                        <h3>Access Your Account</h3>
                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <ul class="nav nav-pills login-tabs" id="loginTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-login" type="button" role="tab">
                                    <i class="fas fa-user-shield me-2"></i>Admin
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="user-tab" data-bs-toggle="pill" data-bs-target="#user-login" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>Staff
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="loginTabContent">
                            <!-- Admin Login Tab -->
                            <div class="tab-pane fade show active" id="admin-login" role="tabpanel">
                                <div class="login-form">
                                    <form method="POST" action="">
                                        <input type="hidden" name="user_type" value="admin">
                                        <div class="mb-3">
                                            <label for="admin-username" class="form-label fw-bold text-white">Admin Username</label>
                                            <input type="text" class="form-control" id="admin-username" name="username" placeholder="Enter admin username" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin-password" class="form-label fw-bold text-white">Password</label>
                                            <input type="password" class="form-control" id="admin-password" name="password" placeholder="Enter password" required>
                                        </div>
                                        <button type="submit" class="btn btn-login">
                                            <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                                        </button>
                                    </form>
                                    <div class="credentials-info">
                                        <small class="fw-bold">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Default: admin / admin123
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Login Tab -->
                            <div class="tab-pane fade" id="user-login" role="tabpanel">
                                <div class="login-form">
                                    <form method="POST" action="">
                                        				<input type="hidden" name="user_type" value="staff">
                                        <div class="mb-3">
                                            <label for="user-username" class="form-label fw-bold text-white">Username</label>
                                            <input type="text" class="form-control" id="user-username" name="username" placeholder="Enter username" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="user-password" class="form-label fw-bold text-white">Password</label>
                                            <input type="password" class="form-control" id="user-password" name="password" placeholder="Enter password" required>
                                        </div>
                                        <button type="submit" class="btn btn-login">
                                            <i class="fas fa-sign-in-alt me-2"></i>Staff Login
                                        </button>
                                    </form>
                                    <div class="credentials-info">
                                        <small class="fw-bold">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Contact administrator for staff credentials
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced AI-inspired interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Parallax effect for floating elements
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelectorAll('.floating-element');
                parallax.forEach(element => {
                    const speed = 0.5;
                    element.style.transform = `translateY(${scrolled * speed}px)`;
                });
            });

            // Enhanced hover effects for AI feature cards
            const aiCards = document.querySelectorAll('.ai-feature-card');
            aiCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-15px) scale(1.05)';
                    this.style.boxShadow = '0 40px 80px rgba(0, 212, 255, 0.4)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '0 25px 50px rgba(0, 0, 0, 0.2)';
                });
            });
            
            // Smooth tab switching with enhanced animations
            const tabButtons = document.querySelectorAll('.nav-link');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Add ripple effect
                    const ripple = document.createElement('div');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = '50%';
                    ripple.style.top = '50%';
                    ripple.style.width = '100px';
                    ripple.style.height = '100px';
                    ripple.style.marginLeft = '-50px';
                    ripple.style.marginTop = '-50px';
                    
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            // Typing effect for welcome title
            const welcomeTitle = document.querySelector('.welcome-title');
            const originalText = welcomeTitle.textContent;
            welcomeTitle.textContent = '';
            
            let i = 0;
            const typeWriter = () => {
                if (i < originalText.length) {
                    welcomeTitle.textContent += originalText.charAt(i);
                    i++;
                    setTimeout(typeWriter, 100);
                }
            };
            
            // Start typing effect after a delay
            setTimeout(typeWriter, 1000);
        });
    </script>
</body>
</html>

