<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">IMMO</h1>
            <p class="login-subtitle"><?= trans('welcome_back') ?></p>
        </div>

        <form method="POST" action="<?= route('login') ?>" class="login-form">
            <?= csrf_input() ?>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label"><?= trans('email') ?></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input <?= isset($errors['email']) ? 'has-error' : '' ?>"
                    value="<?= sanitize($_SESSION['old_input']['email'] ?? '') ?>"
                    autocomplete="email"
                    required
                    autofocus
                >
                <?php if (isset($errors['email'])): ?>
                    <div class="form-error">
                        <?php foreach ($errors['email'] as $error): ?>
                            <span><?= sanitize($error) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label"><?= trans('password') ?></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input <?= isset($errors['password']) ? 'has-error' : '' ?>"
                    autocomplete="current-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <div class="form-error">
                        <?php foreach ($errors['password'] as $error): ?>
                            <span><?= sanitize($error) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Remember Me -->
            <div class="form-group form-check">
                <label class="form-check-label">
                    <input type="checkbox" name="remember" value="1" class="form-check-input">
                    <span class="form-check-text"><?= trans('remember_me') ?></span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary btn-block">
                <?= trans('login') ?>
            </button>
        </form>

        <div class="login-footer">
            <p><?= trans('new_here') ?> <a href="#"><?= trans('contact_admin') ?></a></p>
        </div>
    </div>
</div>

<?php unset($_SESSION['old_input'], $_SESSION['errors']); ?>
