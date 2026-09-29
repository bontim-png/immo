<div class="agents-page">
    <!-- Actions -->
    <div class="page-actions">
        <a href="<?= route('agents.create') ?>" class="btn btn-primary">
            <span>+</span> <?= trans('new_agent') ?>
        </a>
    </div>

    <!-- Agents Table -->
    <?php if (empty($agents)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128101;</div>
            <h3><?= trans('no_agents_yet') ?></h3>
            <p><?= trans('create_first_agent') ?></p>
            <a href="<?= route('agents.create') ?>" class="btn btn-primary">
                <?= trans('create_agent') ?>
            </a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= trans('name') ?></th>
                        <th><?= trans('office') ?></th>
                        <th><?= trans('email') ?></th>
                        <th><?= trans('phone') ?></th>
                        <th><?= trans('role') ?></th>
                        <th><?= trans('properties') ?></th>
                        <th><?= trans('status') ?></th>
                        <th><?= trans('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td class="col-name">
                                <?= sanitize($agent['first_name'] . ' ' . $agent['last_name']) ?>
                            </td>
                            <td>
                                <?= sanitize($agent['office']['name'] ?? '') ?>
                            </td>
                            <td>
                                <?= sanitize($agent['email'] ?? '') ?>
                            </td>
                            <td>
                                <?= sanitize($agent['phone'] ?? '') ?>
                            </td>
                            <td>
                                <span class="role-badge role-<?= $agent['role'] ?? 'agent' ?>">
                                    <?= trans($agent['role'] ?? 'agent') ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= route('properties.index') ?>?agent_id=<?= $agent['id'] ?>" class="link-count">
                                    <?= format_number($agent['property_count'] ?? 0) ?>
                                </a>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $agent['status'] ?? 'active' ?>">
                                    <?= trans($agent['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <a href="<?= route('agents.show', ['id' => $agent['id']]) ?>" class="btn-icon" title="<?= trans('view') ?>">
                                    &#128065;
                                </a>
                                <a href="<?= route('agents.edit', ['id' => $agent['id']]) ?>" class="btn-icon" title="<?= trans('edit') ?>">
                                    &#9998;
                                </a>
                                <form 
                                    method="POST" 
                                    action="<?= route('agents.destroy', ['id' => $agent['id']]) ?>"
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
