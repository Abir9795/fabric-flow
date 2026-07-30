<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();
$machineStatuses=['Active','Inactive'];

if(is_post()){
    verify_csrf();
    $action=post_string('action',30);
    try{
        if($action==='add'){
            $name=post_string('machine_name');$type=post_string('machine_type');$status=post_string('status',20);
            if($name===''||$type===''||!in_array($status,$machineStatuses,true)) throw new RuntimeException('Complete all machine fields.');
            $stmt=db()->prepare('INSERT INTO machines(machine_name,machine_type,status) VALUES(?,?,?)');$stmt->bind_param('sss',$name,$type,$status);$stmt->execute();
            flash('success','Machine added.');
        }elseif($action==='edit'){
            $id=post_int('machine_id');$name=post_string('machine_name');$type=post_string('machine_type');$status=post_string('status',20);
            if(!$id||$name===''||$type===''||!in_array($status,$machineStatuses,true)) throw new RuntimeException('Complete all machine fields.');
            $stmt=db()->prepare('UPDATE machines SET machine_name=?,machine_type=?,status=? WHERE machine_id=?');$stmt->bind_param('sssi',$name,$type,$status,$id);$stmt->execute();
            flash('success','Machine updated.');
        }elseif($action==='assign'){
            $machineId=post_int('machine_id');$employeeId=post_int('employee_id');
            if(!$machineId||!$employeeId) throw new RuntimeException('Choose a machine and employee.');
            $stmt=db()->prepare('INSERT INTO machine_assignments(machine_id,employee_id,assigned_date) VALUES(?,?,CURRENT_DATE)');$stmt->bind_param('ii',$machineId,$employeeId);$stmt->execute();
            flash('success','Employee assigned to machine.');
        }elseif($action==='delete_machine'){
            $id=post_int('machine_id');$stmt=db()->prepare('DELETE FROM machines WHERE machine_id=?');$stmt->bind_param('i',$id);$stmt->execute();flash('success','Machine deleted.');
        }elseif($action==='delete_assignment'){
            $id=post_int('assignment_id');$stmt=db()->prepare('DELETE FROM machine_assignments WHERE assignment_id=?');$stmt->bind_param('i',$id);$stmt->execute();flash('success','Assignment removed.');
        }
    }catch(Throwable $error){flash('error',$error instanceof RuntimeException?$error->getMessage():friendly_db_error($error));}
    redirect('manage_machine.php');
}
$machines=db()->query('SELECT * FROM machines ORDER BY machine_name');
$activeMachines=db()->query("SELECT machine_id,machine_name FROM machines WHERE status='Active' ORDER BY machine_name");
$employees=db()->query('SELECT employee_id,name FROM employees ORDER BY name');
$assignments=db()->query('SELECT ma.assignment_id,m.machine_name,e.name AS employee_name,ma.assigned_date FROM machine_assignments ma JOIN machines m ON m.machine_id=ma.machine_id JOIN employees e ON e.employee_id=ma.employee_id ORDER BY ma.assigned_date DESC');
page_start('Machines','machines');
?>
<div class="page-heading"><div><h1>Machines & assignments</h1><p>Maintain equipment availability and employee assignments.</p></div></div>
<div class="grid" style="grid-template-columns:1fr 1fr">
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="add"><h2>Add machine</h2><div class="form-group"><label>Name</label><input name="machine_name" required></div><div class="form-group"><label>Type</label><input name="machine_type" required></div><div class="form-group"><label>Status</label><select name="status"><?php foreach($machineStatuses as $s): ?><option><?= e($s) ?></option><?php endforeach; ?></select></div><div class="actions"><button class="btn">Add machine</button></div></form>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="assign"><h2>Assign employee</h2><div class="form-group"><label>Machine</label><select name="machine_id" required><option value="">Choose machine</option><?php while($m=$activeMachines->fetch_assoc()): ?><option value="<?= (int)$m['machine_id'] ?>"><?= e($m['machine_name']) ?></option><?php endwhile; ?></select></div><div class="form-group"><label>Employee</label><select name="employee_id" required><option value="">Choose employee</option><?php while($e=$employees->fetch_assoc()): ?><option value="<?= (int)$e['employee_id'] ?>"><?= e($e['name']) ?></option><?php endwhile; ?></select></div><div class="actions"><button class="btn">Assign employee</button></div></form>
</div>
<section class="card"><h2>Machine list</h2><div class="table-wrap"><table><thead><tr><th>Machine</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody><?php while($m=$machines->fetch_assoc()): ?><tr><td><?= e($m['machine_name']) ?></td><td><?= e($m['machine_type']) ?></td><td><span class="badge"><?= e($m['status']) ?></span></td><td><div class="actions" style="margin:0;flex-wrap:nowrap"><details><summary class="btn btn-small btn-secondary">Edit</summary><form method="post" class="card" style="position:absolute;right:30px;z-index:5;min-width:min(500px,90vw)"><?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="machine_id" value="<?= (int)$m['machine_id'] ?>"><div class="form-group"><label>Name</label><input name="machine_name" value="<?= e($m['machine_name']) ?>" required></div><div class="form-group"><label>Type</label><input name="machine_type" value="<?= e($m['machine_type']) ?>" required></div><div class="form-group"><label>Status</label><select name="status"><?php foreach($machineStatuses as $s): ?><option <?= $m['status']===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div><div class="actions"><button class="btn">Save changes</button></div></form></details><form method="post" data-confirm="Delete this machine?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_machine"><input type="hidden" name="machine_id" value="<?= (int)$m['machine_id'] ?>"><button class="btn btn-small btn-danger">Delete</button></form></div></td></tr><?php endwhile; ?></tbody></table></div></section>
<section class="card"><h2>Current assignments</h2><div class="table-wrap"><table><thead><tr><th>Machine</th><th>Employee</th><th>Assigned</th><th></th></tr></thead><tbody><?php while($a=$assignments->fetch_assoc()): ?><tr><td><?= e($a['machine_name']) ?></td><td><?= e($a['employee_name']) ?></td><td><?= e($a['assigned_date']) ?></td><td><form method="post" data-confirm="Remove this assignment?"><?= csrf_field() ?><input type="hidden" name="action" value="delete_assignment"><input type="hidden" name="assignment_id" value="<?= (int)$a['assignment_id'] ?>"><button class="btn btn-small btn-danger">Remove</button></form></td></tr><?php endwhile; ?></tbody></table></div></section>
<?php page_end(); ?>
