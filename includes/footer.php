<?php
require_once __DIR__ . '/helpers.php';
$scriptPath = script_url();
$footerYear = (int) date('Y');
?>

<footer class="site-footer footer" role="contentinfo">
  <div class="site-footer-inner">
    <div class="footer-top">
      <div class="footer-brand">
        <a href="<?= site_url('index.php') ?>" class="footer-logo">
          <img src="<?= asset_url('images/logo.png') ?>" alt="" width="36" height="36" loading="lazy">
          <span>Ubuntu Market</span>
        </a>
        <p>South Africa's premier C2C marketplace. Buy and sell safely in your community.</p>
        <div class="footer-socials" aria-label="Social media">
          <a href="https://www.facebook.com/profile.php?id=61584104951139" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
            <img src="<?= asset_url('images/icons/facebook.png') ?>" alt="" width="20" height="20" loading="lazy">
          </a>
          <a href="https://www.instagram.com/fenixsite/" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <img src="<?= asset_url('images/icons/instagram.png') ?>" alt="" width="20" height="20" loading="lazy">
          </a>
          <a href="https://www.linkedin.com/company/110095368/" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
            <img src="<?= asset_url('images/icons/linkedin.png') ?>" alt="" width="20" height="20" loading="lazy">
          </a>
          <a href="https://github.com/Themba-Sithole" class="footer-social-link" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
            <img src="<?= asset_url('images/icons/github.png') ?>" alt="" width="20" height="20" loading="lazy">
          </a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= site_url('pages/discovery.php') ?>">Browse Products</a></li>
          <li><a href="<?= site_url('pages/discovery.php') ?>">Categories</a></li>
          <li><a href="<?= site_url('pages/shop.php') ?>">Verified Shops</a></li>
          <li><a href="<?= site_url('pages/discovery.php', ['search' => 'deals']) ?>">Flash Deals</a></li>
          <li><a href="<?= site_url('pages/favorites.php') ?>">Wishlist</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="<?= site_url('pages/help-center.php') ?>">Help Center</a></li>
          <li><a href="<?= site_url('pages/safety-tips.php') ?>">Safety Tips</a></li>
          <li><a href="<?= site_url('pages/buyer-protection.php') ?>">Buyer Protection</a></li>
          <li><a href="<?= site_url('pages/seller-guide.php') ?>">Seller Guide</a></li>
          <li><a href="<?= site_url('pages/terms-of-service.php') ?>">Terms of Service</a></li>
        </ul>
      </div>

      <div class="footer-col footer-col-contact">
        <h4>Contact Us</h4>
        <ul>
          <li><a href="mailto:support@ubuntumarket.co.za">support@ubuntumarket.co.za</a></li>
          <li><a href="tel:+27623347216">+27 62 334 7216</a></li>
          <li><span>Cape Town, South Africa</span></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© <?= $footerYear ?> Ubuntu Market. All rights reserved.</p>
      <nav class="footer-bottom-links" aria-label="Legal">
        <a href="<?= site_url('pages/privacy-policy.php') ?>">Privacy Policy</a>
        <a href="<?= site_url('pages/cookie-policy.php') ?>">Cookie Policy</a>
        <a href="<?= site_url('pages/accessibility.php') ?>">Accessibility</a>
      </nav>
    </div>
  </div>
</footer>

<script src="<?= htmlspecialchars($scriptPath) ?>" defer></script>
</body>
</html>
