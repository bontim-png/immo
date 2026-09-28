<?php
$title = $title ?? 'IMMO';
$locale = $i18n->getLocale();
?><!doctype html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> · IMMO</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <div class="brand"><span class="brand-mark">I</span><span>IMMO</span></div>
  <nav>
    <a class="nav-item active" href="/"><span>⌂</span><?= htmlspecialchars($i18n->t('app.dashboard')) ?></a>
    <a class="nav-item" href="#"><span>▣</span><?= htmlspecialchars($i18n->t('app.properties')) ?></a>
    <a class="nav-item" href="#"><span>⌂</span><?= htmlspecialchars($i18n->t('app.offices')) ?></a>
    <a class="nav-item" href="#"><span>◉</span><?= htmlspecialchars($i18n->t('app.agents')) ?></a>
  </nav>
  <div class="sidebar-bottom"><span class="muted">V1 foundation</span></div>
</aside>
<main class="main">
<header class="topbar"><div></div><div class="top-actions">
  <?php foreach (['fr'=>'FR','en'=>'EN','nl'=>'NL'] as $code=>$label): ?><a class="lang <?= $locale===$code?'selected':'' ?>" href="?lang=<?= $code ?>"><?= $label ?></a><?php endforeach; ?>
  <div class="avatar">TB</div>
</div></header>
<div class="content">
