<?php
// registration form for buyers and sellers
require_once '../includes/auth.php';
$pageTitle = 'Register — Ubuntu Market';
$bodyClass = 'auth-page';
include __DIR__ . '/../includes/auth-header.php';
?>

<div class="auth-container">
  <div class="auth-card auth-card-wide">
    <div class="auth-logo">
      <img src="<?= asset_url('images/logo.png') ?>" alt="Ubuntu Market">
      <h1>Create Account</h1>
      <p>Join South Africa's trusted C2C marketplace</p>
    </div>

    <?php if (!empty($_GET['error'])): ?>
      <div class="auth-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="../auth/register.php" method="POST" class="auth-form" id="registerForm" novalidate>
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" placeholder="Your full name" required autocomplete="name">
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="+27 82 000 0000" autocomplete="tel">
      </div>

      <div class="form-row-auth">
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Min 8 characters" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required autocomplete="new-password">
        </div>
      </div>

      <div class="form-group">
        <label for="role">I want to</label>
        <select id="role" name="role">
          <option value="buyer">Buy products</option>
          <option value="seller">Buy &amp; sell products</option>
        </select>
      </div>

      <button type="submit" class="auth-btn">Create Account</button>

      <p class="auth-switch">
        Already have an account? <a href="login-page.php">Sign in</a>
      </p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/auth-footer.php'; ?>
