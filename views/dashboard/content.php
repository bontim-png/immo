<div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#127968;</div>
            <div class="stat-info">
                <span class="stat-value"><?= format_number($totalProperties) ?></span>
                <span class="stat-label"><?= trans('total_properties') ?></span>
            </div>
        </div>
        
        <?php if ($isAdmin): ?>
            <div class="stat-card">
                <div class="stat-icon">&#127970;</div>
                <div class="stat-info">
                    <span class="stat-value"><?= format_number($totalOffices) ?></span>
                    <span class="stat-label"><?= trans('total_offices') ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="stat-card">
            <div class="stat-icon">&#128101;</div>
            <div class="stat-info">
                <span class="stat-value"><?= format_number($totalAgents) ?></span>
                <span class="stat-label"><?= trans('total_agents') ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Properties -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><?= trans('recent_properties') ?></h2>
            <a href="<?= route('properties.create') ?>" class="btn btn-primary">
                <?= trans('new_property') ?>
            </a>
        </div>
        
        <?php if (empty($recentProperties)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128193;</div>
                <h3><?= trans('no_properties_yet') ?></h3>
                <p><?= trans('create_first_property') ?></p>
                <a href="<?= route('properties.create') ?>" class="btn btn-primary">
                    <?= trans('create_property') ?>
                </a>
            </div>
        <?php else: ?>
            <div class="properties-list">
                <?php foreach ($recentProperties as $property): ?>
                    <div class="property-card">
                        <div class="property-image">
                            <?php if ($property['hero_photo'] ?? null): ?>
                                <img src="<?= asset($property['hero_photo']['file_path']) ?>" alt="<?= sanitize($property['title']) ?>">
                            <?php else: ?>
                                <div class="property-placeholder">&#127968;</div>
                            <?php endif; ?>
                        </div>
                        <div class="property-info">
                            <h3 class="property-title"><?= sanitize($property['title']) ?></h3>
                            <p class="property-reference"><?= sanitize($property['reference_code']) ?></p>
                            <p class="property-price">
                                <?= format_price($property['price'] ?? 0, $property['currency'] ?? 'EUR') ?>
                            </p>
                            <p class="property-location">
                                <?= sanitize($property['city'] ?? '') ?>
                            </p>
                            <span class="property-status status-<?= $property['listing_status'] ?? 'draft' ?>">
                                <?= trans($property['listing_status'] ?? 'draft') ?>
                            </span>
                        </div>
                        <div class="property-actions">
                            <a href="<?= route('properties.edit', ['id' => $property['id']]) ?>" class="btn-icon" title="<?= trans('edit') ?>">
                                &#9998;
                            </a>
                            <a href="<?= route('properties.show', ['id' => $property['id']]) ?>" class="btn-icon" title="<?= trans('view') ?>">
                                &#128065;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
