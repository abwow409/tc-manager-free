<?php
/**
 * footer.php - Common Footer
 * Contains copyright information, performance stats, and analytics placeholder
 */

// Prevent direct access
if (!defined('ALLOWED_ACCESS') && !defined('TRINITYCORE_SECURITY')) {
    die('Direct access not permitted.');
}

// Calculate page execution time (from request start to now)
$pageLoadTime = microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

// Database query count (if tracked)
$queryCount = 0;
if (class_exists('Database')) {
    $db = Database::getInstance();
    if (method_exists($db, 'getQueryCount')) {
        $queryCount = $db->getQueryCount();
    }
}

// Get current year
$currentYear = date('Y');

// Site name (from config or default)
$siteName = isset($config['site']['name']) ? $config['site']['name'] : 'TrinityCore';

// Analytics code (can be configured in config.php)
$analyticsCode = isset($config['analytics_code']) ? $config['analytics_code'] : '';
?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-left">
            <p>&copy; <?php echo $currentYear; ?> <strong><?php echo htmlspecialchars($siteName); ?></strong>. All rights reserved.</p>
            <p class="footer-meta">
                Page load time: <span class="time"><?php echo number_format($pageLoadTime, 4); ?></span> seconds
                <?php if ($queryCount > 0): ?>
                    · Database queries: <span class="queries"><?php echo $queryCount; ?></span>
                <?php endif; ?>
            </p>
        </div>
        <div class="footer-right">
            <a href="https://www.trinitycore.org" target="_blank" rel="noopener noreferrer">TrinityCore</a>
            <span class="divider">|</span>
            <a href="privacy.php">Privacy Policy</a>
            <span class="divider">|</span>
            <a href="terms.php">Terms of Service</a>
        </div>
    </div>

    <?php if (!empty($analyticsCode)): ?>
        <!-- Analytics code (e.g., Google Analytics) -->
        <?php echo $analyticsCode; ?>
    <?php endif; ?>
</footer>

<style>
/* Footer styles */
.site-footer {
    margin-top: 40px;
    padding: 20px 0;
    border-top: 1px solid rgba(200, 168, 110, 0.12);
    font-size: 14px;
    color: #7a7a7a;
    background: linear-gradient(180deg, #0d0d0d 0%, #0a0a0a 100%);
}
.site-footer .footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
.site-footer .footer-left p {
    margin: 2px 0;
}
.site-footer .footer-left strong {
    color: #c8a86e;
}
.site-footer .footer-meta {
    font-size: 12px;
    color: #5a5a5a;
}
.site-footer .footer-meta .time,
.site-footer .footer-meta .queries {
    color: #8a8a8a;
}
.site-footer .footer-right {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.site-footer .footer-right a {
    color: #8a8a8a;
    text-decoration: none;
    transition: color 0.3s ease;
}
.site-footer .footer-right a:hover {
    color: #c8a86e;
}
.site-footer .footer-right .divider {
    color: #4a4a4a;
}
@media (max-width: 600px) {
    .site-footer .footer-container {
        flex-direction: column;
        text-align: center;
    }
    .site-footer .footer-right {
        justify-content: center;
    }
}
</style>