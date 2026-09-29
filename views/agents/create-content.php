<div class="agent-form-page">
    <form method="POST" action="<?= route('agents.store') ?>" class="agent-form">
        <?= csrf_input() ?>
        
        <!-- Header -->
        <div class="form-header">
            <h2><?= trans('create_agent') ?></h2>
            <p><?= trans('create_agent_help') ?></p>
        </div>

        <!-- Agent Details -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('agent_details') ?></h3>
            
            <div class="form-grid">
                <!-- Office -->
                <div class="form-group">
                    <label for="office_id" class="form-label">
                        <?= trans('office') ?> *
                    </label>
                    <select 
                        name="office_id" 
                        id="office_id" 
                        class="form-select <?= isset($errors['office_id']) ? 'has-error' : '' ?>"
                        required
                        <?= !$isAdmin ? 'disabled' : '' ?>
                    >
                        <option value=""><?= trans('select_office') ?></option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" 
                                <?= ($_SESSION['old_input']['office_id'] ?? '') == $office['id'] ? 'selected' : '' ?>
                            >
                                <?= sanitize($office['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['office_id'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['office_id'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- First Name -->
                <div class="form-group">
                    <label for="first_name" class="form-label">
                        <?= trans('first_name') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        class="form-input <?= isset($errors['first_name']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['first_name'] ?? '') ?>"
                        required
                        autofocus
                    >
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['first_name'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="last_name" class="form-label">
                        <?= trans('last_name') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        class="form-input <?= isset($errors['last_name']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['last_name'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['last_name'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <?= trans('password') ?> *
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input <?= isset($errors['password']) ? 'has-error' : '' ?>"
                        value=""
                        required
                        minlength="8"
                    >
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['password'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <small class="form-hint"><?= trans('password_min_8') ?></small>
                </div>

                <!-- Role (Admin only) -->
                <?php if ($isAdmin): ?>
                    <div class="form-group">
                        <label for="role" class="form-label">
                            <?= trans('role') ?>
                        </label>
                        <select name="role" id="role" class="form-select">
                            <option value="agent" <?= ($_SESSION['old_input']['role'] ?? '') === 'agent' ? 'selected' : '' ?>>
                                <?= trans('agent') ?>
                            </option>
                            <option value="admin" <?= ($_SESSION['old_input']['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                <?= trans('admin') ?>
                            </option>
                        </select>
                    </div>
                <?php endif; ?>

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
            <a href="<?= route('agents.index') ?>" class="btn btn-secondary">
                <?= trans('cancel') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?= trans('save_agent') ?>
            </button>
        </div>
    </form>
</div>

<?php unset($_SESSION['old_input'], $_SESSION['errors']); ?>
