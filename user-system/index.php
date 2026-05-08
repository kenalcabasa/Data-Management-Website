<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="home-content">
    <h1>Welcome to UserSystem</h1>
    <p>A secure and modern user management platform</p>
    <?php if (!isLoggedIn()): ?>
        <div>
            <a href="register.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-secondary" style="margin-left: 1rem; background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">Login</a>
        </div>
    <?php else: ?>
        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    <?php endif; ?>
    
    <div class="features">
        <div class="feature-item">
            <div class="feature-icon">🔒</div>
            <h3>Secure</h3>
            <p>Advanced encryption and security measures</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">⚡</div>
            <h3>Fast</h3>
            <p>Optimized for performance</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📱</div>
            <h3>Responsive</h3>
            <p>Works on all devices</p>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🆓</div>
            <h3>Free Hosting</h3>
            <p>Running on InfinityFree</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>