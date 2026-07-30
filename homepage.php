<?php
require_once __DIR__ . '/includes/layout.php';
if (!empty($_SESSION['user_id'])) {
    redirect('admin_dashboard.php');
}
page_start('Welcome');
?>
<section class="auth-card" style="text-align:center;max-width:620px">
    <div class="brand" style="color:var(--navy);justify-content:center"><span>F</span> FabricFlow</div>
    <h1>Fabric operations, organized.</h1>
    <p>Manage inventory, orders, customers, carriers, employees, attendance, and machines from one secure workspace.</p>
    <a class="btn" href="login.php">Sign in</a>
</section>
<?php page_end(); ?>
