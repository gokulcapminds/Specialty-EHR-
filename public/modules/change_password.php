<!-- public/modules/change_password.php --><link rel="stylesheet" href="css/modules/login.css?v=<?= time() ?>">

<div class="login-wrapper">
    <div class="card login-card">
        <h1 class="login-title">Update Your Password</h1>
        <p class="login-subtitle">You're using a temporary password. Set a new one to continue.</p>

        <form id="change-password-form" method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="cp-current-password">Temporary Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="password" id="cp-current-password" name="current_password" placeholder="Enter the password from your email" required autocomplete="current-password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="cp-new-password">New Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-key input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="password" id="cp-new-password" name="new_password" placeholder="At least 10 characters, with a letter and a number" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="cp-confirm-password">Confirm New Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-key input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="password" id="cp-confirm-password" name="confirm_password" placeholder="Re-enter your new password" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login-submit" id="change-password-submit-btn">
                Update Password &amp; Continue <i class="fas fa-arrow-right mod-login-style-2"></i>
            </button>
        </form>
    </div>
</div>
