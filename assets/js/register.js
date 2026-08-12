// assets/js/register.js - 注册页面交互

document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const usernameStatus = document.getElementById('username_status');
    const usernameAvailability = document.getElementById('username_availability');
    const strengthBar = document.getElementById('strength_bar');
    const strengthText = document.getElementById('strength_text');
    const passwordMatch = document.getElementById('password_match');
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    
    let usernameTimeout = null;
    
    // 用户名实时检查
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameTimeout);
        const username = this.value.trim();
        
        if (username.length < 3) {
            usernameStatus.textContent = '❌';
            usernameStatus.style.color = '#e17055';
            usernameAvailability.textContent = '用户名至少3个字符';
            usernameAvailability.style.color = '#e17055';
            return;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            usernameStatus.textContent = '❌';
            usernameStatus.style.color = '#e17055';
            usernameAvailability.textContent = '用户名只能包含字母、数字和下划线';
            usernameAvailability.style.color = '#e17055';
            return;
        }
        
        usernameStatus.textContent = '⏳';
        usernameStatus.style.color = '#fdcb6e';
        usernameAvailability.textContent = '正在检查...';
        usernameAvailability.style.color = '#fdcb6e';
        
        usernameTimeout = setTimeout(function() {
            fetch('ajax_check_username.php?username=' + encodeURIComponent(username))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        usernameStatus.textContent = '✅';
                        usernameStatus.style.color = '#00b894';
                        usernameAvailability.textContent = '用户名可用 ✓';
                        usernameAvailability.style.color = '#00b894';
                    } else {
                        usernameStatus.textContent = '❌';
                        usernameStatus.style.color = '#e17055';
                        usernameAvailability.textContent = '用户名已被使用';
                        usernameAvailability.style.color = '#e17055';
                    }
                })
                .catch(error => {
                    usernameStatus.textContent = '⚠️';
                    usernameStatus.style.color = '#fdcb6e';
                    usernameAvailability.textContent = '检查失败，请重试';
                    usernameAvailability.style.color = '#fdcb6e';
                });
        }, 500);
    });
    
    // 密码强度检测
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        const percentage = (strength.score / strength.max_score) * 100;
        strengthBar.style.width = percentage + '%';
        strengthBar.className = 'strength-bar-fill strength-' + strength.strength_level;
        
        let strengthTextContent = strength.strength_level + ' (' + strength.score + '/' + strength.max_score + ')';
        if (strength.messages.length > 0) {
            strengthTextContent += ' - ' + strength.messages.join(', ');
        }
        strengthText.textContent = strengthTextContent;
        
        // 显示密码要求
        const requirements = document.getElementById('password_requirements');
        if (password.length > 0) {
            const reqList = [
                { fulfilled: password.length >= 8, text: '至少8位' },
                { fulfilled: /[a-z]/.test(password) && /[A-Z]/.test(password), text: '包含大小写字母' },
                { fulfilled: /[0-9]/.test(password), text: '包含数字' },
                { fulfilled: /[^a-zA-Z0-9]/.test(password), text: '包含特殊字符' }
            ];
            
            requirements.innerHTML = reqList.map(req => 
                '<div class="req-item ' + (req.fulfilled ? 'fulfilled' : 'unfulfilled') + '">' +
                (req.fulfilled ? '✅' : '❌') + ' ' + req.text +
                '</div>'
            ).join('');
        } else {
            requirements.innerHTML = '';
        }
        
        // 检查密码是否常见
        const weakPasswords = ['password', '123456', 'qwerty', 'admin', 'letmein', 'welcome', 'trinity'];
        if (weakPasswords.includes(password.toLowerCase())) {
            strengthBar.style.width = '0%';
            strengthText.textContent = '❌ 密码太常见，请使用更复杂的密码';
        }
        
        // 检查密码匹配
        checkPasswordMatch();
    });
    
    // 密码匹配检查
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmPasswordInput.value;
        
        if (confirm.length === 0) {
            passwordMatch.textContent = '';
            passwordMatch.style.color = '';
            return;
        }
        
        if (password === confirm) {
            passwordMatch.textContent = '✅ 密码匹配';
            passwordMatch.style.color = '#00b894';
        } else {
            passwordMatch.textContent = '❌ 密码不匹配';
            passwordMatch.style.color = '#e17055';
        }
    }
    
    // 密码强度检测函数
    function checkPasswordStrength(password) {
        let score = 0;
        const messages = [];
        
        if (password.length >= <?php echo $config['security']['min_password_length']; ?>) {
            score++;
        } else {
            messages.push('长度至少<?php echo $config['security']['min_password_length']; ?>位');
        }
        
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
            score++;
        } else {
            messages.push('包含大小写字母');
        }
        
        if (/[0-9]/.test(password)) {
            score++;
        } else {
            messages.push('包含数字');
        }
        
        if (/[^a-zA-Z0-9]/.test(password)) {
            score++;
        } else {
            messages.push('包含特殊字符');
        }
        
        const levels = ['非常弱', '弱', '一般', '强', '非常强'];
        
        return {
            score: score,
            max_score: 4,
            strength_level: levels[score] || '非常弱',
            messages: messages
        };
    }
    
    // 表单提交前的验证
    registerForm.addEventListener('submit', function(e) {
        // 验证用户名
        const username = usernameInput.value.trim();
        if (username.length < 3 || username.length > 32) {
            e.preventDefault();
            alert('用户名长度必须在3-32个字符之间。');
            return false;
        }
        
        // 验证密码强度
        const password = passwordInput.value;
        const strength = checkPasswordStrength(password);
        if (strength.score < 2) {
            e.preventDefault();
            alert('密码强度不足，请使用更复杂的密码。');
            return false;
        }
        
        // 验证密码匹配
        if (password !== confirmPasswordInput.value) {
            e.preventDefault();
            alert('两次输入的密码不一致。');
            return false;
        }
        
        // 禁用按钮防止重复提交
        registerBtn.disabled = true;
        registerBtn.textContent = '注册中...';
    });
    
    // 显示密码切换
    window.togglePassword = function(id) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    };
    
    // reCAPTCHA回调
    window.recaptchaCallback = function() {
        document.getElementById('registerBtn').disabled = false;
    };
});