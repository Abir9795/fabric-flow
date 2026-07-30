<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();

$stats = db()->query("
    SELECT
      (SELECT COUNT(*) FROM orders) AS orders_count,
      (SELECT COUNT(*) FROM customers) AS customers_count,
      (SELECT COUNT(*) FROM employees) AS employees_count,
      (SELECT COALESCE(SUM(stock_quantity), 0) FROM fabrics) AS stock_count
")->fetch_assoc();
$recent = db()->query("
    SELECT o.order_id, o.order_date, o.status, o.total_amount, c.name AS customer_name
    FROM orders o JOIN customers c ON c.customer_id = o.customer_id
    ORDER BY o.order_id DESC LIMIT 6
");
$lowStock = db()->query("SELECT fabric_name, stock_quantity FROM fabrics WHERE stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 6");

page_start('Dashboard', 'dashboard');
?>
<div class="page-heading">
    <div><h1>Operations overview</h1><p>Welcome back, <?= e($_SESSION['username']) ?>. Here is what is happening today.</p></div>
    <a class="btn" href="add_order.php">＋ New order</a>
</div>
<section class="grid stats">
    <div class="stat"><span>Total orders</span><strong><?= number_format((int)$stats['orders_count']) ?></strong></div>
    <div class="stat"><span>Customers</span><strong><?= number_format((int)$stats['customers_count']) ?></strong></div>
    <div class="stat"><span>Employees</span><strong><?= number_format((int)$stats['employees_count']) ?></strong></div>
    <div class="stat"><span>Units in stock</span><strong><?= number_format((int)$stats['stock_count']) ?></strong></div>
</section>
<div class="grid" style="grid-template-columns:minmax(0,2fr) minmax(260px,1fr);margin-top:22px">
    <section class="card">
        <div class="split"><h2>Recent orders</h2><a href="manage_orders.php">View all</a></div>
        <div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Status</th><th>Total</th></tr></thead><tbody>
        <?php while ($row = $recent->fetch_assoc()): ?>
            <tr><td><a href="invoice.php?order_id=<?= (int)$row['order_id'] ?>">#<?= (int)$row['order_id'] ?></a></td><td><?= e($row['customer_name']) ?></td><td><?= e($row['order_date']) ?></td><td><span class="badge"><?= e(ucfirst(strtolower($row['status']))) ?></span></td><td class="amount">$<?= number_format((float)$row['total_amount'], 2) ?></td></tr>
        <?php endwhile; ?>
        </tbody></table></div>
    </section>
    <section class="card">
        <h2>Low stock</h2>
        <?php if ($lowStock->num_rows): ?>
            <?php while ($item = $lowStock->fetch_assoc()): ?><div class="split" style="padding:10px 0;border-bottom:1px solid var(--line)"><span><?= e($item['fabric_name']) ?></span><strong class="low-stock"><?= (int)$item['stock_quantity'] ?> left</strong></div><?php endwhile; ?>
        <?php else: ?><p class="muted">All inventory levels look healthy.</p><?php endif; ?>
        <div class="actions"><a class="btn btn-secondary" href="manage_fabrics.php">Manage inventory</a></div>
    </section>
</div>
<?php page_end(); ?>
