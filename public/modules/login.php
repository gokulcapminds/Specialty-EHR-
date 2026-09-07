<!-- public/modules/login.php --><link rel="stylesheet" href="css/modules/login.css?v=<?= time() ?>">

<div class="login-wrapper">
    <div class="card login-card">
        <h1 class="login-title">Welcome</h1>
        <p class="login-subtitle">Sign In to Specialty EHR</p>
        
        <form id="login-form" method="POST" novalidate>
            <div class="form-group">
                <label class="form-label" for="login-username">User Name / Email</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-envelope input-icon-left"></i>
                    <input class="form-control input-with-icon-left" type="text" id="login-username" name="username" placeholder="Enter Username / Email" required autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="login-password">Password</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-lock input-icon-left"></i>
                    <input class="form-control input-with-icon-left input-with-icon-right" type="password" id="login-password" name="password" placeholder="Enter Password" required autocomplete="current-password">
                    <i class="fas fa-eye input-icon-right mod-login-style-1" id="toggle-login-password" title="Toggle Password Visibility"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="login-specialty">Clinical Specialty / Department</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-stethoscope input-icon-left"></i>
                    <select class="form-control input-with-icon-left" id="login-specialty" name="specialty" required style="height: 44px; font-size: 0.92rem;">
                        <option value="Cardiology">Cardiology EHR</option>
                        <option value="Orthopedics">Orthopedic EHR</option>
                        <option value="Dermatology">Dermatology EHR</option>
                        <option value="Neurology">Neurology EHR</option>
                        <option value="Oncology">Oncology EHR</option>
                        <option value="Ophthalmology">Ophthalmology EHR</option>
                        <option value="Physical Therapy">Physical Therapy EHR</option>
                    </select>
                </div>
            </div>

            <div class="form-group login-options-row">
                <label class="checkbox-container">
                    <input type="checkbox" id="login-remember" name="remember_me" aria-label="Remember me">
                    <span class="checkmark"></span>
                    Remember me
                </label>
                <a href="#forgot-password" class="forgot-password-link" id="forgot-password-btn">Forgot password ?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login-submit" id="login-submit-btn">
                Sign In <i class="fas fa-arrow-right mod-login-style-2"></i>
            </button>
        </form>
    </div>
</div>
