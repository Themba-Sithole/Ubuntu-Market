<?php
// filter sidebar used on Discover and Shop pages
$activeFilterChips = $activeFilterChips ?? [];
$activeFilterCount = count($activeFilterChips);
?>
<aside class="filter-panel" id="filterPanel" aria-label="Product filters">
  <div class="filter-panel-head">
    <div class="filter-panel-title-wrap">
      <h2><?= htmlspecialchars($filterPanelTitle) ?></h2>
      <?php if ($activeFilterCount > 0): ?>
        <span class="filter-panel-badge"><?= $activeFilterCount ?> active</span>
      <?php endif; ?>
    </div>
    <button type="button" class="filter-panel-close" aria-label="Close filters">×</button>
  </div>

  <form method="get" action="<?= htmlspecialchars($filterFormAction) ?>" class="filter-form" id="filterForm">
    <div class="filter-group">
      <label for="filter_search">Search</label>
      <input
        type="search"
        id="filter_search"
        name="search"
        value="<?= htmlspecialchars($search) ?>"
        placeholder="Keywords, titles, brands…"
        autocomplete="off"
      >
    </div>

    <div class="filter-group">
      <label for="filter_category_id">Category</label>
      <select id="filter_category_id" name="category_id">
        <option value="">All categories</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category['category_id'] ?>" <?= (string) $categoryId === (string) $category['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="filter-group">
      <label for="filter_brand_id">Brand</label>
      <select id="filter_brand_id" name="brand_id">
        <option value="">All brands</option>
        <?php foreach ($brands as $brand): ?>
          <option value="<?= $brand['brand_id'] ?>" <?= (string) $brandId === (string) $brand['brand_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($brand['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <fieldset class="filter-fieldset">
      <legend>Price range</legend>
      <div class="filter-row">
        <div class="filter-group filter-group--price">
          <label for="filter_price_min">Min</label>
          <div class="price-input-wrap">
            <span class="price-prefix" aria-hidden="true">R</span>
            <input
              type="number"
              id="filter_price_min"
              name="price_min"
              min="0"
              step="1"
              inputmode="numeric"
              value="<?= $priceMin !== '' && is_numeric($priceMin) ? htmlspecialchars($priceMin) : '' ?>"
              placeholder="No min"
            >
          </div>
        </div>
        <div class="filter-group filter-group--price">
          <label for="filter_price_max">Max</label>
          <div class="price-input-wrap">
            <span class="price-prefix" aria-hidden="true">R</span>
            <input
              type="number"
              id="filter_price_max"
              name="price_max"
              min="0"
              step="1"
              inputmode="numeric"
              value="<?= $priceMax !== '' && is_numeric($priceMax) ? htmlspecialchars($priceMax) : '' ?>"
              placeholder="No max"
            >
          </div>
        </div>
      </div>
    </fieldset>

    <div class="filter-actions">
      <button type="submit" class="primary-btn filter-apply-btn">Apply filters</button>
      <a href="<?= htmlspecialchars($filterResetUrl) ?>" class="filter-reset-btn">Clear all</a>
    </div>
  </form>
</aside>
<div class="filter-backdrop" id="filterBackdrop" aria-hidden="true"></div>
