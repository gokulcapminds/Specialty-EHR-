<!-- public/modules/forgot_password.php --><link rel="stylesheet" href="css/modules/login.css?v=<?= time() ?>">

<div class="login-wrapper">
    <div class="card login-card">
        <h1 class="login-title">Forgot password?</h1>
        <p class="login-subtitle">Enter your username or email. If an account matches, we will email you a link to choose a new password.</p>

        <form id="forgot-password-form" method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="fp-identifier">User Name / Email</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-envelope input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="text" id="fp-identifier" name="identifier" placeholder="Enter Username / Email" required autocomplete="username">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login-submit" id="fp-submit-btn">Send reset link</button>
        </form>

        <p id="fp-done" class="login-subtitle" style="display: none; margin-top: 16px;"></p>
        <p class="login-back-row"><a href="#login" class="forgot-password-link">Back to sign in</a></p>
    </div>
</div>
