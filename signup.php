<?php
require_once __DIR__ . '/includes/layout.php';

$userCount = (int) db()->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'];
$allowed = $userCount === 0 || !empty($_SESSION['user_id']);
if (!$allowed) {
    flash('error', 'New administrator accounts can only be created by a signed-in administrator.');
    redirect('login.php');
}

if (is_post()) {
    verify_csrf();
    $username = post_string('username', 50);
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        flash('error', 'Username must be 3–50 characters and use only letters, numbers, dots, dashes, or underscores.');
    } elseif (strlen($password) < 8) {
        flash('error', 'Password must contain at least 8 characters.');
    } elseif ($password !== $confirmation) {
        flash('error', 'The password confirmation does not match.');
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->bind_param('ss', $username, $hash);
            $stmt->execute();
            flash('success', 'Administrator account created successfully.');
            redirect(!empty($_SESSION['user_id']) ? 'userlist.php' : 'login.php');
        } catch (Throwable $error) {
            flash('error', friendly_db_error($error));
        }
    }
    redirect('signup.php');
}

page_start('Create administrator', 'users');
?>
<section class="<?= !empty($_SESSION['user_id']) ? 'card' : 'auth-card' ?>" style="max-width:520px;margin:auto">
    <h1>Create administrator</h1>
    <p class="muted">Use a unique username and a password of at least eight characters.</p>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-group"><label for="username">Username</label><input id="username" name="username" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" required autofocus></div>
        <div class="form-group"><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <div class="form-group"><label for="confirmation">Confirm password</label><input id="confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
        <button class="btn" type="submit">Create account</button>
    </form>
</section>
<?php page_end(); ?>
