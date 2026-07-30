<?php
require_once __DIR__ . '/includes/layout.php';

if (!empty($_SESSION['user_id'])) {
    redirect('admin_dashboard.php');
}

if (is_post()) {
    verify_csrf();
    $username = post_string('username', 50);
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        unset($_SESSION['csrf_token']);
        flash('success', 'Welcome back, ' . $user['username'] . '.');
        redirect('admin_dashboard.php');
    }
    flash('error', 'The username or password is incorrect.');
    redirect('login.php');
}

page_start('Sign in');
?>
<section class="auth-card">
    <h1>Welcome back</h1>
    <p>Sign in to manage FabricFlow operations.</p>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-group"><label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="50" required autofocus></div>
        <div class="form-group"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn" type="submit">Sign in</button>
    </form>
    <div class="auth-links"><a href="homepage.php">Back to home</a></div>
</section>
<?php page_end(); ?>
