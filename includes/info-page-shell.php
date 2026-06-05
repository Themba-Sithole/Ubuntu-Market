<?php
if (!isset($infoSections) || !is_array($infoSections)) {
    $infoSections = [];
}
$showToc = $infoToc ?? (count($infoSections) > 1);
include __DIR__ . '/header.php';
?>

  <div class="page-container info-page">
    <div class="info-page-hero">
      <nav class="info-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= site_url('index.php') ?>">Home</a>
        <span aria-hidden="true">/</span>
        <span><?= htmlspecialchars($infoHeading) ?></span>
      </nav>
      <h1 class="info-page-title"><?= htmlspecialchars($infoHeading) ?></h1>
      <?php if (!empty($infoSubtitle)): ?>
        <p class="info-page-lead"><?= htmlspecialchars($infoSubtitle) ?></p>
      <?php endif; ?>
      <p class="info-page-updated">Last updated: <?= date('F j, Y') ?></p>
    </div>

    <div class="info-page-layout">
      <?php if ($showToc): ?>
        <nav class="info-toc" aria-label="On this page">
          <h2>On this page</h2>
          <ol>
            <?php foreach ($infoSections as $i => $section): ?>
              <?php $slug = 'section-' . ($i + 1); ?>
              <li><a href="#<?= $slug ?>"><?= htmlspecialchars($section['title']) ?></a></li>
            <?php endforeach; ?>
          </ol>
        </nav>
      <?php endif; ?>

      <article class="info-content">
        <?php foreach ($infoSections as $i => $section): ?>
          <?php $slug = 'section-' . ($i + 1); ?>
          <section id="<?= $slug ?>" class="info-section">
            <h2><?= htmlspecialchars($section['title']) ?></h2>
            <?= $section['html'] ?>
          </section>
        <?php endforeach; ?>
      </article>
    </div>

    <div class="info-page-cta">
      <p>Still need help?</p>
      <a href="mailto:support@ubuntumarket.co.za" class="primary-btn">Email support</a>
      <a href="<?= site_url('pages/discovery.php') ?>" class="secondary-btn">Continue shopping</a>
    </div>
  </div>

<?php include __DIR__ . '/footer.php'; ?>
