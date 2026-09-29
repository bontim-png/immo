<div class="offices-page">
    <!-- Actions -->
    <div class="page-actions">
        <?php if ($isAdmin): ?>
            <a href="<?= route('offices.create') ?>" class="btn btn-primary">
                <span>+</span> <?= trans('new_office') ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Offices Table -->
    <?php if (empty($offices)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127970;</div>
            <h3><?= trans('no_offices_yet') ?></h3>
            <p><?= trans('create_first_office') ?></p>
            <?php if ($isAdmin): ?>
                <a href="<?= route('offices.create') ?>" class="btn btn-primary">
                    <?= trans('create_office') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= trans('name') ?></th>
                        <th><?= trans('email') ?></th>
                        <th><?= trans('phone') ?></th>
                        <th><?= trans('city') ?></th>
                        <th><?= trans('agents') ?></th>
                        <th><?= trans('properties') ?></th>
                        <th><?= trans('status') ?></th>
                        <th><?= trans('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offices as $office): ?>
                        <tr>
                            <td class="col-name">
                                <a href="<?= route('offices.show', ['id' => $office['id']]) ?>">
                                    <?= sanitize($office['name']) ?>
                                </a>
                                <?php if ($office['legal_name'] && $office['legal_name'] !== $office['name']): ?>
                                    <br><small><?= sanitize($office['legal_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= sanitize($office['email'] ?? '') ?>
                            </td>
                            <td>
                                <?= sanitize($office['phone'] ?? '') ?>
                            </td>
                            <td>
                                <?= sanitize($office['city'] ?? '') ?>
                            </td>
                            <td>
                                <a href="<?= route('agents.index') ?>?office_id=<?= $office['id'] ?>" class="link-count">
                                    <?= format_number($office['agent_count'] ?? 0) ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?= route('properties.index') ?>?office_id=<?= $office['id'] ?>" class="link-count">
                                    <?= format_number($office['property_count'] ?? 0) ?>
                                </a>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $office['status'] ?? 'active' ?>">
                                    <?= trans($office['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <a href="<?= route('offices.show', ['id' => $office['id']]) ?>" class="btn-icon" title="<?= trans('view') ?>">
                                    &#128065;
                                </a>
                                <?php if ($isAdmin || $office['id'] == $userOfficeId): ?>
                                    <a href="<?= route('offices.edit', ['id' => $office['id']]) ?>" class="btn-icon" title="<?= trans('edit') ?>">
                                        &#9998;
                                    </a>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                    <form 
                                        method="POST" 
                                        action="<?= route('offices.destroy', ['id' => $office['id']]) ?>"
                                        class="delete-form"
                                        onsubmit="return confirm('<?= trans('confirm_delete') ?>')"
                                    >
                                        <?= csrf_input() ?>
                                        <button type="submit" class="btn-icon btn-danger" title="<?= trans('delete') ?>">
                                            &#128465;
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
