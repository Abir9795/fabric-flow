<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();
$orderId=filter_input(INPUT_GET,'order_id',FILTER_VALIDATE_INT);
if(!$orderId){flash('error','Invalid order.');redirect('manage_orders.php');}
$stmt=db()->prepare("SELECT o.*,c.name customer_name,c.email,c.phone_number,c.address,cr.name carrier_name FROM orders o JOIN customers c ON c.customer_id=o.customer_id LEFT JOIN carriers cr ON cr.carrier_id=o.carrier_id WHERE o.order_id=?");
$stmt->bind_param('i',$orderId);$stmt->execute();$order=$stmt->get_result()->fetch_assoc();
if(!$order){flash('error','Order not found.');redirect('manage_orders.php');}
$items=db()->prepare("SELECT f.fabric_name,f.fabric_type,oi.quantity,oi.unit_price,(oi.quantity*oi.unit_price) line_total FROM order_items oi JOIN fabrics f ON f.fabric_id=oi.fabric_id WHERE oi.order_id=?");
$items->bind_param('i',$orderId);$items->execute();$result=$items->get_result();
page_start('Invoice #'.$orderId,'orders');
?>
<div class="page-heading no-print"><div><h1>Invoice #<?= (int)$orderId ?></h1><p>Historical prices are preserved from the time of sale.</p></div><div class="actions" style="margin:0"><button class="btn" onclick="window.print()">Print invoice</button><a class="btn btn-secondary" href="manage_orders.php">Back</a></div></div>
<article class="card invoice">
<div class="split"><div><h1>FabricFlow</h1><p class="muted">Fabric management system</p></div><div style="text-align:right"><strong>INVOICE</strong><p>#<?= (int)$orderId ?><br><?= e($order['order_date']) ?></p></div></div>
<hr style="border:0;border-top:1px solid var(--line);margin:25px 0">
<div class="grid" style="grid-template-columns:1fr 1fr"><div><strong>Bill to</strong><p><?= e($order['customer_name']) ?><br><?= e($order['email']) ?><br><?= e($order['phone_number']) ?><br><?= nl2br(e($order['address'])) ?></p></div><div><strong>Order details</strong><p>Status: <?= e(ucfirst(strtolower($order['status']))) ?><br>Carrier: <?= e($order['carrier_name'] ?: 'Not assigned') ?></p></div></div>
<div class="table-wrap"><table><thead><tr><th>Fabric</th><th>Type</th><th>Quantity</th><th>Unit price</th><th>Total</th></tr></thead><tbody><?php while($item=$result->fetch_assoc()): ?><tr><td><?= e($item['fabric_name']) ?></td><td><?= e($item['fabric_type']) ?></td><td><?= (int)$item['quantity'] ?></td><td>$<?= number_format((float)$item['unit_price'],2) ?></td><td>$<?= number_format((float)$item['line_total'],2) ?></td></tr><?php endwhile; ?></tbody><tfoot><tr><td colspan="4" style="text-align:right"><strong>Total</strong></td><td class="invoice-total">$<?= number_format((float)$order['total_amount'],2) ?></td></tr></tfoot></table></div>
</article>
<?php page_end(); ?>
