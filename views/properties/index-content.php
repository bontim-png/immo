<div class="properties-page">
    <!-- Filters -->
    <div class="filters">
        <form method="GET" action="<?= route('properties.index') ?>" class="filter-form">
            <div class="filter-group">
                <label for="office_id" class="filter-label"><?= trans('office') ?></label>
                <select name="office_id" id="office_id" class="filter-select">
                    <option value=""><?= trans('all_offices') ?></option>
                    <?php foreach ($offices as $office): ?>
                        <option value="<?= $office['id'] ?>" <?= ($currentOfficeId ?? null) == $office['id'] ? 'selected' : '' ?>>
                            <?= sanitize($office['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status" class="filter-label"><?= trans('status') ?></label>
                <select name="status" id="status" class="filter-select">
                    <option value=""><?= trans('all_statuses') ?></option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= trans('draft') ?></option>
                    <option value="published" <?= ($status ?? '') === 'published' ? 'selected' : '' ?>><?= trans('published') ?></option>
                    <option value="under_offer" <?= ($status ?? '') === 'under_offer' ? 'selected' : '' ?>><?= trans('under_offer') ?></option>
                    <option value="sold" <?= ($status ?? '') === 'sold' ? 'selected' : '' ?>><?= trans('sold') ?></option>
                    <option value="rented" <?= ($status ?? '') === 'rented' ? 'selected' : '' ?>><?= trans('rented') ?></option>
                    <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>><?= trans('archived') ?></option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="q" class="filter-label"><?= trans('search') ?></label>
                <input 
                    type="text" 
                    name="q" 
                    id="q" 
                    class="filter-input"
                    value="<?= sanitize($query ?? '') ?>"
                    placeholder="<?= trans('search_properties') ?>..."
                >
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <?= trans('filter') ?>
            </button>
            
            <a href="<?= route('properties.index') ?>" class="btn btn-link">
                <?= trans('clear_filters') ?>
            </a>
        </form>
    </div>

    <!-- Actions -->
    <div class="page-actions">
        <a href="<?= route('properties.create') ?>" class="btn btn-primary">
            <span>+</span> <?= trans('new_property') ?>
        </a>
    </div>

    <!-- Properties Table -->
    <?php if (empty($properties)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128193;</div>
            <h3><?= trans('no_properties') ?></h3>
            <p><?= trans('create_first_property') ?></p>
            <a href="<?= route('properties.create') ?>" class="btn btn-primary">
                <?= trans('create_property') ?>
            </a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= trans('reference') ?></th>
                        <th><?= trans('title') ?></th>
                        <th><?= trans('office') ?></th>
                        <th><?= trans('agent') ?></th>
                        <th><?= trans('price') ?></th>
                        <th><?= trans('status') ?></th>
                        <th><?= trans('updated') ?></th>
                        <th><?= trans('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $property): ?>
                        <tr>
                            <td class="col-reference">
                                <?= sanitize($property['reference_code']) ?>
                            </td>
                            <td class="col-title">
                                <a href="<?= route('properties.show', ['id' => $property['id']]) ?>">
                                    <?= sanitize($property['title']) ?>
                                </a>
                            </td>
                            <td>
                                <?= sanitize($property['office']['name'] ?? '') ?>
                            </td>
                            <td>
                                <?= sanitize(($property['agent']['first_name'] ?? '') . ' ' . ($property['agent']['last_name'] ?? '')) ?>
                            </td>
                            <td>
                                <?= format_price($property['price'] ?? 0, $property['currency'] ?? 'EUR') ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $property['listing_status'] ?? 'draft' ?>">
                                    <?= trans($property['listing_status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td>
                                <?= format_date($property['updated_at'] ?? $property['created_at']) ?>
                            </td>
                            <td class="col-actions">
                                <a href="<?= route('properties.edit', ['id' => $property['id']]) ?>" class="btn-icon" title="<?= trans('edit') ?>">
                                    &#9998;
                                </a>
                                <a href="<?= route('properties.show', ['id' => $property['id']]) ?>" class="btn-icon" title="<?= trans('view') ?>">
                                    &#128065;
                                </a>
                                <form 
                                    method="POST" 
                                    action="<?= route('properties.destroy', ['id' => $property['id']]) ?>"
                                    class="delete-form"
                                    onsubmit="return confirm('<?= trans('confirm_delete') ?>')"
                                >
                                    <?= csrf_input() ?>
                                    <button type="submit" class="btn-icon btn-danger" title="<?= trans('delete') ?>">
                                        &#128465;
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
