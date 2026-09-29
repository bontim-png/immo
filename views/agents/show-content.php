<div class="agent-show-page">
    <!-- Agent Header -->
    <div class="show-header">
        <div class="show-title">
            <h2><?= sanitize($agent['first_name'] . ' ' . $agent['last_name']) ?></h2>
            <p class="show-subtitle">
                <span class="role-badge role-<?= $agent['role'] ?? 'agent' ?>">
                    <?= trans($agent['role'] ?? 'agent') ?>
                </span>
            </p>
        </div>
        <div class="show-actions">
            <a href="<?= route('agents.edit', ['id' => $agent['id']]) ?>" class="btn btn-secondary">
                <?= trans('edit') ?>
            </a>
        </div>
    </div>

    <!-- Agent Info Card -->
    <div class="show-section">
        <h3 class="section-title"><?= trans('agent_information') ?></h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><?= trans('office') ?></span>
                <span class="info-value">
                    <a href="<?= route('offices.show', ['id' => $agent['office']['id']]) ?>">
                        <?= sanitize($agent['office']['name'] ?? '') ?>
                    </a>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('email') ?></span>
                <span class="info-value"><?= sanitize($agent['email'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('phone') ?></span>
                <span class="info-value"><?= sanitize($agent['phone'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('status') ?></span>
                <span class="info-value">
                    <span class="status-badge status-<?= $agent['status'] ?? 'active' ?>">
                        <?= trans($agent['status'] ?? 'active') ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('created_at') ?></span>
                <span class="info-value"><?= format_date($agent['created_at'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('last_login_at') ?></span>
                <span class="info-value"><?= $agent['last_login_at'] ? format_date($agent['last_login_at']) : trans('never') ?></span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="show-section">
        <h3 class="section-title"><?= trans('statistics') ?></h3>
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?= format_number($agent['property_count'] ?? 0) ?></div>
                <div class="stat-label"><?= trans('properties') ?></div>
            </div>
        </div>
    </div>

    <!-- Properties Section -->
    <?php if (!empty($agent['properties'])): ?>
        <div class="show-section">
            <h3 class="section-title"><?= trans('properties') ?> (<= format_number(count($agent['properties'])) ?>)</h3>
            <div class="properties-list">
                <?php foreach ($agent['properties'] as $property): ?>
                    <div class="property-card compact">
                        <div class="property-image small">
                            <?php
                            $propertyModel = new \App\Models\PropertyPhoto();
                            $hero = $propertyModel->getHeroByProperty($property['id']);
                            if ($hero):
                                echo '<img src="' . asset('uploads/properties/thumbnails/' . basename($hero['file_path'])) . '" alt="' . sanitize($property['title']) . '">';
                            else:
                                echo '<div class="property-placeholder">&#127968;</div>';
                            ?>
                        </div>
                        <div class="property-info">
                            <h4>
                                <a href="<?= route('properties.show', ['id' => $property['id']]) ?>">
                                    <?= sanitize($property['title']) ?>
                                </a>
                            </h4>
                            <p class="property-ref"><?= sanitize($property['reference_code']) ?></p>
                            <p class="property-price"><?= format_price($property['price'] ?? 0, $property['currency'] ?? 'EUR') ?></p>
                        </div>
                        <div class="property-status">
                            <span class="status-badge status-<?= $property['listing_status'] ?? 'draft' ?>">
                                <?= trans($property['listing_status'] ?? 'draft') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
