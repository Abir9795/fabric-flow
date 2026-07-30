<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();

$statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

if (is_post()) {
    verify_csrf();
    $customerId = post_int('customer_id');
    $carrierId = post_int('carrier_id') ?: null;
    $orderDate = post_string('order_date', 10);
    $status = post_string('status', 20);
    $quantities = $_POST['quantity'] ?? [];
    $items = [];

    foreach ($quantities as $fabricId => $quantity) {
        $fabricId = filter_var($fabricId, FILTER_VALIDATE_INT);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
        if ($fabricId && $quantity && $quantity > 0) {
            $items[(int)$fabricId] = (int)$quantity;
        }
    }

    if (!$customerId || !valid_date($orderDate) || !in_array($status, $statuses, true) || !$items) {
        flash('error', 'Choose a customer, valid date, status, and at least one fabric quantity.');
        redirect('add_order.php');
    }

    $conn = db();
    try {
        $conn->begin_transaction();
        $total = 0.0;
        $resolved = [];
        $stockStmt = $conn->prepare('SELECT price_per_meter, stock_quantity FROM fabrics WHERE fabric_id = ? FOR UPDATE');
        foreach ($items as $fabricId => $quantity) {
            $stockStmt->bind_param('i', $fabricId);
            $stockStmt->execute();
            $fabric = $stockStmt->get_result()->fetch_assoc();
            if (!$fabric) {
                throw new RuntimeException('A selected fabric no longer exists.');
            }
            if ((int)$fabric['stock_quantity'] < $quantity) {
                throw new RuntimeException('Not enough stock for one or more selected fabrics.');
            }
            $price = (float)$fabric['price_per_meter'];
            $total += $price * $quantity;
            $resolved[] = [$fabricId, $quantity, $price];
        }

        $stmt = $conn->prepare('INSERT INTO orders (customer_id, carrier_id, order_date, status, total_amount) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iissd', $customerId, $carrierId, $orderDate, $status, $total);
        $stmt->execute();
        $orderId = $conn->insert_id;

        $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, fabric_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
        $reduceStmt = $conn->prepare('UPDATE fabrics SET stock_quantity = stock_quantity - ? WHERE fabric_id = ? AND stock_quantity >= ?');
        foreach ($resolved as [$fabricId, $quantity, $price]) {
            $itemStmt->bind_param('iiid', $orderId, $fabricId, $quantity, $price);
            $itemStmt->execute();
            $reduceStmt->bind_param('iii', $quantity, $fabricId, $quantity);
            $reduceStmt->execute();
            if ($reduceStmt->affected_rows !== 1) {
                throw new RuntimeException('Inventory changed while the order was being saved.');
            }
        }
        $conn->commit();
        flash('success', "Order #$orderId was created successfully.");
        redirect('manage_orders.php');
    } catch (Throwable $error) {
        $conn->rollback();
        $message = $error instanceof RuntimeException ? $error->getMessage() : friendly_db_error($error);
        flash('error', $message);
        redirect('add_order.php');
    }
}

$customers = db()->query('SELECT customer_id, name FROM customers ORDER BY name');
$carriers = db()->query('SELECT carrier_id, name FROM carriers ORDER BY name');
$fabrics = db()->query('SELECT fabric_id, fabric_name, fabric_type, price_per_meter, stock_quantity FROM fabrics WHERE stock_quantity > 0 ORDER BY fabric_name');

page_start('New order', 'orders');
?>
<div class="page-heading"><div><h1>Create order</h1><p>Inventory is checked and reserved when the order is saved.</p></div><a class="btn btn-secondary" href="manage_orders.php">Back to orders</a></div>
<form method="post">
<?= csrf_field() ?>
<section class="card">
    <div class="form-grid">
        <div class="form-group"><label for="customer">Customer</label><select id="customer" name="customer_id" required><option value="">Select customer</option><?php while ($row=$customers->fetch_assoc()): ?><option value="<?= (int)$row['customer_id'] ?>"><?= e($row['name']) ?></option><?php endwhile; ?></select></div>
        <div class="form-group"><label for="carrier">Carrier (optional)</label><select id="carrier" name="carrier_id"><option value="">No carrier</option><?php while ($row=$carriers->fetch_assoc()): ?><option value="<?= (int)$row['carrier_id'] ?>"><?= e($row['name']) ?></option><?php endwhile; ?></select></div>
        <div class="form-group"><label for="date">Order date</label><input id="date" type="date" name="order_date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="form-group"><label for="status">Status</label><select id="status" name="status"><?php foreach ($statuses as $value): ?><option><?= e($value) ?></option><?php endforeach; ?></select></div>
    </div>
</section>
<section class="card">
    <h2>Fabric quantities</h2>
    <div class="table-wrap"><table><thead><tr><th>Fabric</th><th>Type</th><th>Price / meter</th><th>Available</th><th style="width:150px">Quantity</th></tr></thead><tbody>
    <?php while ($row=$fabrics->fetch_assoc()): ?>
        <tr><td><?= e($row['fabric_name']) ?></td><td><?= e($row['fabric_type']) ?></td><td>$<?= number_format((float)$row['price_per_meter'],2) ?></td><td><?= (int)$row['stock_quantity'] ?></td><td><input type="number" min="0" max="<?= (int)$row['stock_quantity'] ?>" name="quantity[<?= (int)$row['fabric_id'] ?>]" value="0"></td></tr>
    <?php endwhile; ?>
    </tbody></table></div>
    <div class="actions"><button class="btn" type="submit">Create order</button></div>
</section>
</form>
<?php page_end(); ?>
