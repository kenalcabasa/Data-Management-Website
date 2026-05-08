<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();

// Get some statistics
$conn = getDBConnection();
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$conn->close();

include 'includes/header.php';
?>

<div class="container">
    <div class="dashboard-card">
        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>! 👋</h2>
        
        <div class="user-info">
            <div class="info-item">
                <span class="info-label">👤 Username:</span>
                <span><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">📧 Email:</span>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">📅 Member Since:</span>
                <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="profile-actions">
            <button class="btn btn-primary" onclick="alert('Edit profile feature coming soon!')">Edit Profile</button>
            <button class="btn btn-secondary" onclick="alert('Settings feature coming soon!')">Settings</button>
        </div>
    </div>
    
    <div class="grid">
        <div class="stat-card">
            <h3>📊 Statistics</h3>
            <div class="stat-number"><?php echo $total_users; ?></div>
            <p>Total Registered Users</p>
        </div>
        
        <div class="stat-card">
            <h3>🔐 Security</h3>
            <p>Your account is protected with encryption</p>
            <p style="color: #27ae60; margin-top: 0.5rem;">✓ Password hashed</p>
            <p style="color: #27ae60;">✓ Secure session</p>
        </div>
        
        <div class="stat-card">
            <h3>🕒 Recent Activity</h3>
            <p>Last login: Just now</p>
            <p style="color: #7f8c8d; margin-top: 0.5rem;">No recent activity to show</p>
        </div>
    </div>
    
    <div class="text-center mt-2">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>