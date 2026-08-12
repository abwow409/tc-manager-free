<?php
session_start();

// ==================== 配置 ====================
$soapConfig = [
    'host' => '127.0.0.1',
    'port' => 7878,
    'user' => '2#1',
    'password' => 'YourStrongSOAPPassword'
];

$securityConfig = [
    'min_password_length' => 8,
    'token_expiry_minutes' => 60
];

// ==================== 数据库连接 ====================
$db = new mysqli('127.0.0.1', 'trinity', 'trinity', 'auth', 3306);
if ($db->connect_error) {
    die('数据库连接失败：' . $db->connect_error);
}
$db->set_charset('utf8mb4');

// ==================== 验证令牌 ====================
$token = $_GET['token'] ?? '';
$validToken = false;
$username = '';
$email = '';
$message = '';
$messageType = '';

if (empty($token)) {
    $message = '❌ 无效的重置链接。';
    $messageType = 'error';
} else {
    // 查找令牌
    $stmt = $db->prepare("SELECT id, username, email, expires_at, used FROM password_reset_tokens WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $tokenData = $result->fetch_assoc();
    $stmt->close();
    
    if (!$tokenData) {
        $message = '❌ 无效或已使用的重置链接。';
        $messageType = 'error';
    } elseif ($tokenData['used'] == 1) {
        $message = '❌ 此重置链接已被使用。请重新发起密码重置请求。';
        $messageType = 'error';
    } elseif (new DateTime() > new DateTime($tokenData['expires_at'])) {
        $message = '❌ 重置链接已过期（' . $securityConfig['token_expiry_minutes'] . '分钟有效期）。请重新发起请求。';
        $messageType = 'error';
        // 标记为已使用
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
        $stmt->bind_param('i', $tokenData['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $validToken = true;
        $username = $tokenData['username'];
        $email = $tokenData['email'];
        $_SESSION['reset_token_id'] = $tokenData['id'];
        $_SESSION['reset_username'] = $username;
    }
}

// ==================== 处理密码重置 ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $tokenId = $_SESSION['reset_token_id'] ?? 0;
    $username = $_SESSION['reset_username'] ?? '';
    
    if (!$tokenId || !$username) {
        $message = '❌ 会话已过期，请重新发起密码重置请求。';
        $messageType = 'error';
        goto show_result;
    }
    
    // 验证密码
    if (strlen($newPassword) < $securityConfig['min_password_length']) {
        $message = '❌ 密码至少 ' . $securityConfig['min_password_length'] . ' 位字符。';
        $messageType = 'error';
        goto show_result;
    }
    
    if (!preg_match('/[a-z]/', $newPassword) || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $message = '❌ 密码必须包含大小写字母和数字。';
        $messageType = 'error';
        goto show_result;
    }
    
    if ($newPassword !== $confirmPassword) {
        $message = '❌ 两次输入的密码不一致。';
        $messageType = 'error';
        goto show_result;
    }
    
    // 通过SOAP重置密码
    try {
        $soapClient = new SoapClient(null, [
            'location' => "http://{$soapConfig['host']}:{$soapConfig['port']}/",
            'uri' => 'urn:TC',
            'style' => SOAP_RPC,
            'login' => $soapConfig['user'],
            'password' => $soapConfig['password'],
            'trace' => true,
            'exceptions' => true
        ]);
        
        $command = "account set password $username $newPassword";
        $result = $soapClient->__soapCall('executeCommand', [$command]);
        
        if (strpos($result, 'password updated') !== false) {
            // 标记令牌为已使用
            $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
            $stmt->bind_param('i', $tokenId);
            $stmt->execute();
            $stmt->close();
            
            $message = '✅ 密码重置成功！您可以使用新密码登录游戏了。';
            $messageType = 'success';
            // 清除会话
            unset($_SESSION['reset_token_id']);
            unset($_SESSION['reset_username']);
        } else {
            $message = '❌ 密码重置失败：' . htmlspecialchars($result);
            $messageType = 'error';
        }
        
    } catch (SoapFault $e) {
        $message = '❌ SOAP通信错误：' . $e->getMessage();
        $messageType = 'error';
    } catch (Exception $e) {
        $message = '❌ 发生未知错误：' . $e->getMessage();
        $messageType = 'error';
    }
}

show_result:
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重置密码 - TrinityCore 安全系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #16213e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #5cffa0, #4a9eff, #5cffa0);
            background-size: 400% 400%;
            z-index: -1;
            animation: gradient 3s ease infinite;
            border-radius: 12px;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .inner {
            background: #16213e;
            padding: 30px;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }
        h2 {
            color: #e0e0e0;
            text-align: center;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #8892b0;
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }
        label {
            display: block;
            color: #a8b2d1;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
        }
        label .required {
            color: #ff6b6b;
            margin-left: 3px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #0f3460;
            border-radius: 6px;
            background: #0a0a1a;
            color: #ccd6f6;
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }
        input:focus {
            border-color: #5cffa0;
            box-shadow: 0 0 0 3px rgba(92, 255, 160, 0.1);
        }
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #0a0a1a;
            border-radius: 2px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        .password-strength-text {
            display: block;
            font-size: 12px;
            margin-top: 5px;
            color: #8892b0;
        }
        input[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #5cffa0, #00d4ff);
            color: #1a1a2e;
            border: none;
            border-radius: 6px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 1px;
        }
        input[type="submit"]:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(92, 255, 160, 0.4);
        }
        input[type="submit"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .message {
            margin-top: 20px;
            padding: 14px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success {
            background: rgba(92, 255, 160, 0.1);
            color: #5cffa0;
            border: 1px solid rgba(92, 255, 160, 0.2);
        }
        .error {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.2);
        }
        .info-box {
            background: rgba(92, 255, 160, 0.05);
            border: 1px solid rgba(92, 255, 160, 0.1);
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #a8b2d1;
            font-size: 13px;
            text-align: center;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #8892b0;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #4a9eff;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            color: #495670;
            font-size: 12px;
            border-top: 1px solid #0f3460;
            padding-top: 20px;
        }
        @media (max-width: 480px) {
            .container { padding: 15px; }
            .inner { padding: 20px; }
            h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="inner">
            <?php if ($validToken && empty($message) || $messageType === 'error'): ?>
            
            <h2>🔑 设置新密码</h2>
            <p class="subtitle">为账号 <strong><?php echo htmlspecialchars($username); ?></strong> 设置新密码</p>
            
            <div class="info-box">
                🔒 密码要求：至少8位，包含大小写字母和数字
            </div>
            
            <form method="post" action="" id="resetPasswordForm">
                <div class="form-group">
                    <label>新密码 <span class="required">*</span></label>
                    <input type="password" 
                           name="new_password" 
                           id="newPassword" 
                           placeholder="至少8位，含大小写字母和数字"
                           required>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <span class="password-strength-text" id="strengthText">请输入新密码</span>
                </div>
                
                <div class="form-group">
                    <label>确认密码 <span class="required">*</span></label>
                    <input type="password" 
                           name="confirm_password" 
                           id="confirmPassword" 
                           placeholder="再次输入新密码"
                           required>
                </div>
                
                <input type="submit" name="reset_password" value="重置密码" id="submitBtn">
            </form>
            
            <?php endif; ?>
            
            <!-- ===== 消息显示 ===== -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                    <?php if ($messageType === 'success'): ?>
                        <br><br>
                        <a href="login.php" style="color: #4a9eff; text-decoration: none; font-weight: 600;">前往登录 →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <a href="forgot_password.php" class="back-link">← 重新发起找回密码</a>
            
            <div class="footer">
                TrinityCore 12.0 · 安全密码重置
            </div>
        </div>
    </div>
    
    <script>
    // ===== 密码强度检测（与注册页面相同） =====
    const passwordInput = document.getElementById('newPassword');
    const confirmInput = document.getElementById('confirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');
    
    function checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        if (password.length >= 8) score++;
        else feedback.push('至少8位');
        
        if (/[a-z]/.test(password)) score++;
        else feedback.push('小写字母');
        
        if (/[A-Z]/.test(password)) score++;
        else feedback.push('大写字母');
        
        if (/[0-9]/.test(password)) score++;
        else feedback.push('数字');
        
        if (/[^a-zA-Z0-9]/.test(password)) score++;
        else feedback.push('特殊字符');
        
        const commonPasswords = ['123456', 'password', '12345678', 'qwerty', 'abc123'];
        if (commonPasswords.includes(password.toLowerCase())) {
            score = 1;
            feedback = ['密码太常见'];
        }
        
        return { score, feedback };
    }
    
    function updatePasswordStrength(password) {
        const result = checkPasswordStrength(password);
        const percentage = (result.score / 5) * 100;
        
        strengthBar.style.width = percentage + '%';
        
        let color, text;
        if (password.length === 0) {
            color = '#0a0a1a';
            text = '请输入新密码';
        } else if (percentage <= 20) {
            color = '#ff6b6b';
            text = '💪 弱 - ' + result.feedback.join('、');
        } else if (percentage <= 40) {
            color = '#ffd93d';
            text = '💪 一般 - ' + result.feedback.join('、');
        } else if (percentage <= 60) {
            color = '#6bcbff';
            text = '💪 良好 - ' + result.feedback.join('、');
        } else if (percentage <= 80) {
            color = '#5cffa0';
            text = '💪 强 - ' + result.feedback.join('、');
        } else {
            color = '#00ff87';
            text = '💪 很强！密码很安全';
        }
        
        strengthBar.style.background = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
    }
    
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    function checkPasswordMatch() {
        const pass = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            confirmInput.style.borderColor = '#0f3460';
            return;
        }
        
        if (pass === confirm) {
            confirmInput.style.borderColor = '#5cffa0';
        } else {
            confirmInput.style.borderColor = '#ff6b6b';
        }
    }
    
    confirmInput.addEventListener('input', checkPasswordMatch);
    
    // 表单提交验证
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (password.length < 8) {
            e.preventDefault();
            alert('密码至少8位字符！');
            passwordInput.focus();
            return false;
        }
        
        const strength = checkPasswordStrength(password);
        if (strength.score < 3) {
            if (!confirm('密码强度较弱，确定要继续吗？\n建议使用大小写字母+数字+特殊字符的组合。')) {
                e.preventDefault();
                return false;
            }
        }
        
        if (password !== confirm) {
            e.preventDefault();
            alert('两次输入的密码不一致！');
            confirmInput.focus();
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.value = '重置中...';
    });
    </script>
</body>
</html>