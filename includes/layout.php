<?php
require_once __DIR__ . '/bootstrap.php';

function page_start(string $title, string $active = ''): void
{
    $authenticated = !empty($_SESSION['user_id']);
    $flashes = take_flashes();
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · FabricFlow</title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php if ($authenticated): ?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="admin_dashboard.php"><span>F</span> FabricFlow</a>
        <nav>
            <?php
            $links = [
                'dashboard' => ['admin_dashboard.php', 'Dashboard'],
                'orders' => ['manage_orders.php', 'Orders'],
                'customers' => ['manage_customers.php', 'Customers'],
                'fabrics' => ['manage_fabrics.php', 'Inventory'],
                'carriers' => ['manage_carriers.php', 'Carriers'],
                'employees' => ['manage_employees.php', 'Employees'],
                'attendance' => ['attendance_list.php', 'Attendance'],
                'machines' => ['manage_machine.php', 'Machines'],
                'users' => ['userlist.php', 'Administrators'],
            ];
            foreach ($links as $key => [$href, $label]): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="logout.php" class="logout-form">
            <?= csrf_field() ?>
            <button class="nav-logout" type="submit">Sign out</button>
        </form>
    </aside>
    <div class="page">
        <header class="topbar">
            <button class="menu-toggle" type="button" aria-label="Toggle navigation">☰</button>
            <div><strong><?= e($title) ?></strong><small>Signed in as <?= e($_SESSION['username'] ?? '') ?></small></div>
        </header>
        <main class="content">
<?php else: ?>
<main class="auth-shell">
<?php endif; ?>
<?php foreach ($flashes as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endforeach;
}

function page_end(): void
{
    $authenticated = !empty($_SESSION['user_id']);
    echo $authenticated ? '</main></div></div>' : '</main>';
    ?>
<script>
document.querySelector('.menu-toggle')?.addEventListener('click', () => document.body.classList.toggle('nav-open'));
document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
}));
</script>
</body>
</html>
<?php
}
