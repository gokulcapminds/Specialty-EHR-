<!-- public/modules/reset_password.php --><link rel="stylesheet" href="css/modules/login.css?v=<?= time() ?>">

<div class="login-wrapper">
    <div class="card login-card">
        <h1 class="login-title">Choose a new password</h1>
        <p class="login-subtitle">At least 10 characters, with a letter and a number.</p>

        <form id="reset-password-form" method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="rp-new-password">New Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-key input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="password" id="rp-new-password" name="new_password" placeholder="Enter a new password" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="rp-confirm-password">Confirm New Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-key input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="password" id="rp-confirm-password" name="confirm_password" placeholder="Re-enter your new password" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login-submit" id="rp-submit-btn">Change password</button>
        </form>

        <p class="login-back-row"><a href="#login" class="forgot-password-link">Back to sign in</a></p>
    </div>
</div>
