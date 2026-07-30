<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();
$statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

if (is_post()) {
    verify_csrf();
    $orderId = post_int('order_id');
    $action = post_string('action', 20);
    if (!$orderId) {
        flash('error', 'Invalid order.');
        redirect('manage_orders.php');
    }
    try {
        if ($action === 'status') {
            $status = post_string('status', 20);
            if (!in_array($status, $statuses, true)) {
                throw new RuntimeException('Invalid order status.');
            }
            $stmt = db()->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
            $stmt->bind_param('si', $status, $orderId);
            $stmt->execute();
            flash('success', "Order #$orderId status updated.");
        } elseif ($action === 'delete') {
            $conn = db();
            $conn->begin_transaction();
            $items = $conn->prepare('SELECT fabric_id, quantity FROM order_items WHERE order_id = ? FOR UPDATE');
            $items->bind_param('i', $orderId);
            $items->execute();
            $result = $items->get_result();
            $restore = $conn->prepare('UPDATE fabrics SET stock_quantity = stock_quantity + ? WHERE fabric_id = ?');
            while ($item = $result->fetch_assoc()) {
                $restore->bind_param('ii', $item['quantity'], $item['fabric_id']);
                $restore->execute();
            }
            $delete = $conn->prepare('DELETE FROM orders WHERE order_id = ?');
            $delete->bind_param('i', $orderId);
            $delete->execute();
            $conn->commit();
            flash('success', "Order #$orderId deleted and its inventory restored.");
        }
    } catch (Throwable $error) {
        if (isset($conn)) $conn->rollback();
        flash('error', $error instanceof RuntimeException ? $error->getMessage() : friendly_db_error($error));
    }
    redirect('manage_orders.php');
}

$orders = db()->query("SELECT o.*, c.name AS customer_name, cr.name AS carrier_name FROM orders o JOIN customers c ON c.customer_id=o.customer_id LEFT JOIN carriers cr ON cr.carrier_id=o.carrier_id ORDER BY o.order_date DESC, o.order_id DESC");
page_start('Orders', 'orders');
?>
<div class="page-heading"><div><h1>Orders</h1><p>Track status, totals, invoices, and inventory-safe deletion.</p></div><a class="btn" href="add_order.php">＋ New order</a></div>
<section class="card">
<?php if ($orders->num_rows): ?><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Carrier</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php while ($row=$orders->fetch_assoc()): ?>
<tr>
    <td>#<?= (int)$row['order_id'] ?></td><td><?= e($row['customer_name']) ?></td><td><?= e($row['carrier_name'] ?: '—') ?></td><td><?= e($row['order_date']) ?></td><td class="amount">$<?= number_format((float)$row['total_amount'],2) ?></td>
    <td><form method="post" class="actions" style="margin:0;flex-wrap:nowrap"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="order_id" value="<?= (int)$row['order_id'] ?>"><select name="status" style="min-width:125px"><?php foreach($statuses as $status): ?><option <?= strcasecmp($row['status'],$status)===0?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-small btn-secondary">Save</button></form></td>
    <td><div class="actions" style="margin:0;flex-wrap:nowrap"><a class="btn btn-small" href="invoice.php?order_id=<?= (int)$row['order_id'] ?>">Invoice</a><form method="post" data-confirm="Delete this order and return its quantities to inventory?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="order_id" value="<?= (int)$row['order_id'] ?>"><button class="btn btn-small btn-danger">Delete</button></form></div></td>
</tr>
<?php endwhile; ?></tbody></table></div><?php else: ?><div class="empty">No orders yet.</div><?php endif; ?>
</section>
<?php page_end(); ?>
