<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();
$statuses = ['Present', 'Absent', 'On Leave'];

if (is_post()) {
    verify_csrf();
    $date = post_string('date', 10);
    $attendance = $_POST['attendance'] ?? [];
    if (!valid_date($date) || !$attendance) {
        flash('error', 'Select a valid date and attendance status.');
        redirect('add_attendance.php');
    }
    $conn = db();
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare('INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?');
        foreach ($attendance as $employeeId => $status) {
            $employeeId = filter_var($employeeId, FILTER_VALIDATE_INT);
            if (!$employeeId || !in_array($status, $statuses, true)) {
                throw new RuntimeException('Invalid attendance entry.');
            }
            $stmt->bind_param('isss', $employeeId, $date, $status, $status);
            $stmt->execute();
        }
        $conn->commit();
        flash('success', 'Attendance saved for ' . $date . '.');
    } catch (Throwable $error) {
        $conn->rollback();
        flash('error', $error instanceof RuntimeException ? $error->getMessage() : friendly_db_error($error));
    }
    redirect('add_attendance.php');
}

$employees = db()->query('SELECT employee_id, name, role FROM employees ORDER BY name');
page_start('Record attendance', 'attendance');
?>
<div class="page-heading"><div><h1>Record attendance</h1><p>Saving the same employee and date updates the existing entry.</p></div><a class="btn btn-secondary" href="attendance_list.php">Attendance history</a></div>
<form method="post" class="card">
<?= csrf_field() ?>
<div class="form-group" style="max-width:260px;margin-bottom:18px"><label for="date">Attendance date</label><input id="date" name="date" type="date" value="<?= date('Y-m-d') ?>" required></div>
<div class="table-wrap"><table><thead><tr><th>Employee</th><th>Role</th><th>Status</th></tr></thead><tbody>
<?php while ($row=$employees->fetch_assoc()): ?><tr><td><?= e($row['name']) ?></td><td><?= e($row['role']) ?></td><td><select name="attendance[<?= (int)$row['employee_id'] ?>]"><?php foreach($statuses as $status): ?><option><?= e($status) ?></option><?php endforeach; ?></select></td></tr><?php endwhile; ?>
</tbody></table></div>
<div class="actions"><button class="btn">Save attendance</button></div>
</form>
<?php page_end(); ?>
