<?php $title=$i18n->t('dashboard.title'); require __DIR__.'/layouts/header.php'; ?>
<section class="page-head"><div><div class="eyebrow">IMMO / <?= htmlspecialchars(strtoupper($i18n->getLocale())) ?></div><h1><?= htmlspecialchars($i18n->t('dashboard.title')) ?></h1><p><?= htmlspecialchars($i18n->t('dashboard.subtitle')) ?></p></div><button class="primary" type="button" onclick="alert('Property creation comes in the next build.')">+ <?= htmlspecialchars($i18n->t('app.properties')) ?></button></section>
<section class="stats">
<?php foreach ([['properties','stat.properties'],['published','stat.published'],['offices','stat.offices'],['agents','stat.agents']] as [$key,$label]): ?><div class="stat"><div class="stat-label"><?= htmlspecialchars($i18n->t($label)) ?></div><div class="stat-value"><?= number_format($counts[$key],0,'','.') ?></div></div><?php endforeach; ?>
</section>
<section class="panel"><div class="panel-head"><h2><?= htmlspecialchars($i18n->t('recent.title')) ?></h2><span class="muted"><?= count($properties) ?></span></div>
<div class="property-list">
<?php foreach ($properties as $p): ?>
<a class="property-row" href="/property?id=<?= (int)$p['id'] ?>">
<div class="thumb"><?php if($p['hero_photo']): ?><img src="<?= htmlspecialchars($p['hero_photo']) ?>" alt=""><?php else: ?><span>⌂</span><?php endif; ?></div>
<div class="property-main"><strong><?= htmlspecialchars($p['title']) ?></strong><span><?= htmlspecialchars($p['city'] ?? '') ?> · <?= htmlspecialchars($p['office_name']) ?></span></div>
<div class="property-agent"><span><?= htmlspecialchars($p['agent_name']) ?></span><small><?= htmlspecialchars($i18n->t('status.'.$p['listing_status'])) ?></small></div>
<div class="property-price"><?= $p['price'] !== null ? number_format((float)$p['price'],0,',','.') . ' ' . htmlspecialchars($p['currency']) : '—' ?></div>
<div class="arrow">→</div>
</a>
<?php endforeach; ?>
</div></section>
<?php require __DIR__.'/layouts/footer.php'; ?>
