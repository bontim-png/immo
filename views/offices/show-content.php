<div class="office-show-page">
    <!-- Office Header -->
    <div class="show-header">
        <div class="show-title">
            <h2><?= sanitize($office['name']) ?></h2>
            <?php if ($office['legal_name'] && $office['legal_name'] !== $office['name']): ?>
                <p class="show-subtitle"><?= sanitize($office['legal_name']) ?></p>
            <?php endif; ?>
        </div>
        <div class="show-actions">
            <a href="<?= route('offices.edit', ['id' => $office['id']]) ?>" class="btn btn-secondary">
                <?= trans('edit') ?>
            </a>
        </div>
    </div>

    <!-- Office Info Card -->
    <div class="show-section">
        <h3 class="section-title"><?= trans('office_information') ?></h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><?= trans('email') ?></span>
                <span class="info-value"><?= sanitize($office['email'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('phone') ?></span>
                <span class="info-value"><?= sanitize($office['phone'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('website') ?></span>
                <span class="info-value">
                    <?php if ($office['website'] ?? null): ?>
                        <a href="<?= sanitize($office['website']) ?>" target="_blank">
                            <?= sanitize($office['website']) ?>
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('address') ?></span>
                <span class="info-value"><?= sanitize($office['address_line1'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('postal_code') ?></span>
                <span class="info-value"><?= sanitize($office['postal_code'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('city') ?></span>
                <span class="info-value"><?= sanitize($office['city'] ?? '') ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('country') ?></span>
                <span class="info-value">
                    <?php
                    $countries = ['FR' => 'France', 'BE' => 'Belgium', 'NL' => 'Netherlands', 'LU' => 'Luxembourg'];
                    echo sanitize($countries[$office['country_code'] ?? 'FR'] ?? $office['country_code']);
                    ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= trans('status') ?></span>
                <span class="info-value">
                    <span class="status-badge status-<?= $office['status'] ?? 'active' ?>">
                        <?= trans($office['status'] ?? 'active') ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="show-section">
        <h3 class="section-title"><?= trans('statistics') ?></h3>
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?= format_number($office['agent_count'] ?? 0) ?></div>
                <div class="stat-label"><?= trans('agents') ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= format_number($office['property_count'] ?? 0) ?></div>
                <div class="stat-label"><?= trans('properties') ?></div>
            </div>
        </div>
    </div>

    <!-- Agents Section -->
    <?php if (!empty($office['agents'])): ?>
        <div class="show-section">
            <h3 class="section-title"><?= trans('agents') ?> (<= format_number(count($office['agents'])) ?>)</h3>
            <div class="agents-list">
                <?php foreach ($office['agents'] as $agent): ?>
                    <div class="agent-card">
                        <div class="agent-avatar">
                            <?php if ($agent['photo_path'] ?? null): ?>
                                <img src="<?= asset($agent['photo_path']) ?>" alt="<?= sanitize($agent['first_name']) ?>">
                            <?php else: ?>
                                <span class="avatar-initials"><?= substr($agent['first_name'], 0, 1) . substr($agent['last_name'], 0, 1) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="agent-info">
                            <h4><?= sanitize($agent['first_name'] . ' ' . $agent['last_name']) ?></h4>
                            <p><?= sanitize($agent['email'] ?? '') ?></p>
                            <p><?= sanitize($agent['phone'] ?? '') ?></p>
                        </div>
                        <div class="agent-status">
                            <span class="status-badge status-<?= $agent['status'] ?? 'active' ?>">
                                <?= trans($agent['status'] ?? 'active') ?>
                            </span>
                            <span class="agent-role"><?= trans($agent['role'] ?? 'agent') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Properties Section -->
    <?php if (!empty($office['properties'])): ?>
        <div class="show-section">
            <h3 class="section-title"><?= trans('properties') ?> (<= format_number(count($office['properties'])) ?>)</h3>
            <div class="properties-list">
                <?php foreach ($office['properties'] as $property): ?>
                    <div class="property-card compact">
                        <div class="property-image small">
                            <?php
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

<?php
// Fix for propertyModel not defined
$propertyModel = new \App\Models\PropertyPhoto();
?>
