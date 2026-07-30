<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();

$search = trim((string)($_GET['search'] ?? ''));
$allowedSort = ['date'=>'a.date','employee_name'=>'e.name','status'=>'a.status'];
$sortKey = (string)($_GET['sort'] ?? 'date');
$sort = $allowedSort[$sortKey] ?? 'a.date';
$direction = strtolower((string)($_GET['order'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
$sql = "SELECT a.attendance_id,e.name AS employee_name,e.email,a.date,a.status FROM attendance a JOIN employees e ON e.employee_id=a.employee_id";
if ($search !== '') {
    $sql .= " WHERE e.name LIKE ? OR e.email LIKE ?";
}
$sql .= " ORDER BY $sort $direction, a.attendance_id DESC";
$stmt = db()->prepare($sql);
if ($search !== '') {
    $term = "%$search%";
    $stmt->bind_param('ss',$term,$term);
}
$stmt->execute();
$records = $stmt->get_result();
page_start('Attendance', 'attendance');
?>
<div class="page-heading"><div><h1>Attendance history</h1><p>Search and review recorded employee attendance.</p></div><a class="btn" href="add_attendance.php">＋ Record attendance</a></div>
<section class="card">
<form class="search-row" method="get"><input name="search" value="<?= e($search) ?>" placeholder="Search employee or email"><button class="btn btn-secondary">Search</button></form>
<?php if($records->num_rows): ?><div class="table-wrap"><table><thead><tr><th><a href="?sort=employee_name&order=asc">Employee</a></th><th>Email</th><th><a href="?sort=date&order=desc">Date</a></th><th><a href="?sort=status&order=asc">Status</a></th></tr></thead><tbody>
<?php while($row=$records->fetch_assoc()): ?><tr><td><?= e($row['employee_name']) ?></td><td><?= e($row['email']) ?></td><td><?= e($row['date']) ?></td><td><span class="badge"><?= e($row['status']) ?></span></td></tr><?php endwhile; ?>
</tbody></table></div><?php else: ?><div class="empty">No attendance records found.</div><?php endif; ?>
</section>
<?php page_end(); ?>
