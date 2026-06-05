<?php
// login form - actual login happens in auth/login.php
require_once '../includes/auth.php';
$pageTitle = 'Login — Ubuntu Market';
$bodyClass = 'auth-page';
include __DIR__ . '/../includes/auth-header.php';
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="<?= asset_url('images/logo.png') ?>" alt="Ubuntu Market">
      <h1>Welcome back</h1>
      <p>Sign in to your Ubuntu Market account</p>
    </div>

    <?php if (!empty($_GET['error'])): ?>
      <div class="auth-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['success'])): ?>
      <div class="auth-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="../auth/login.php" method="POST" class="auth-form" novalidate>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password" required autocomplete="current-password">
      </div>

      <button type="submit" class="auth-btn">Sign In</button>

      <p class="auth-switch">
        Don't have an account? <a href="register-page.php">Create one</a>
      </p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/auth-footer.php'; ?>
