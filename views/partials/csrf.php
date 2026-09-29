<?php if (isset($csrf) && $csrf === false): ?>
    <input type="hidden" name="csrf_token" value="">
<?php else: ?>
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
<?php endif; ?>
