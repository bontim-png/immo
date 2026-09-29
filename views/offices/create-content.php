<div class="office-form-page">
    <form method="POST" action="<?= route('offices.store') ?>" class="office-form">
        <?= csrf_input() ?>
        
        <!-- Header -->
        <div class="form-header">
            <h2><?= trans('create_office') ?></h2>
            <p><?= trans('create_office_help') ?></p>
        </div>

        <!-- Office Details -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('office_details') ?></h3>
            
            <div class="form-grid">
                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        <?= trans('name') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input <?= isset($errors['name']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['name'] ?? '') ?>"
                        required
                        autofocus
                    >
                    <?php if (isset($errors['name'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['name'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Legal Name -->
                <div class="form-group">
                    <label for="legal_name" class="form-label">
                        <?= trans('legal_name') ?>
                    </label>
                    <input 
                        type="text" 
                        id="legal_name" 
                        name="legal_name" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['legal_name'] ?? '') ?>"
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <?= trans('email') ?> *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input <?= isset($errors['email']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['email'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['email'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <?= trans('phone') ?>
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['phone'] ?? '') ?>"
                    >
                </div>

                <!-- Website -->
                <div class="form-group">
                    <label for="website" class="form-label">
                        <?= trans('website') ?>
                    </label>
                    <input 
                        type="url" 
                        id="website" 
                        name="website" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['website'] ?? '') ?>"
                        placeholder="https://"
                    >
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address_line1" class="form-label">
                        <?= trans('address') ?>
                    </label>
                    <input 
                        type="text" 
                        id="address_line1" 
                        name="address_line1" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['address_line1'] ?? '') ?>"
                    >
                </div>

                <!-- Postal Code -->
                <div class="form-group">
                    <label for="postal_code" class="form-label">
                        <?= trans('postal_code') ?>
                    </label>
                    <input 
                        type="text" 
                        id="postal_code" 
                        name="postal_code" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['postal_code'] ?? '') ?>"
                    >
                </div>

                <!-- City -->
                <div class="form-group">
                    <label for="city" class="form-label">
                        <?= trans('city') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        class="form-input <?= isset($errors['city']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['city'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['city'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['city'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label for="country_code" class="form-label">
                        <?= trans('country') ?>
                    </label>
                    <select name="country_code" id="country_code" class="form-select">
                        <option value="FR" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                        <option value="BE" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'BE' ? 'selected' : '' ?>>Belgium</option>
                        <option value="NL" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'NL' ? 'selected' : '' ?>>Netherlands</option>
                        <option value="LU" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'LU' ? 'selected' : '' ?>>Luxembourg</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status" class="form-label">
                        <?= trans('status') ?>
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= ($_SESSION['old_input']['status'] ?? '') === 'active' ? 'selected' : '' ?>>
                            <?= trans('active') ?>
                        </option>
                        <option value="inactive" <?= ($_SESSION['old_input']['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>
                            <?= trans('inactive') ?>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="form-actions">
            <a href="<?= route('offices.index') ?>" class="btn btn-secondary">
                <?= trans('cancel') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?= trans('save_office') ?>
            </button>
        </div>
    </form>
</div>

<?php unset($_SESSION['old_input'], $_SESSION['errors']); ?>
