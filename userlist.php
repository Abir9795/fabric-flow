<?php
require_once __DIR__ . '/includes/layout.php';
require_auth();
if(is_post()){
    verify_csrf();$id=post_int('id');
    if($id===(int)$_SESSION['user_id']) flash('error','You cannot delete your own signed-in account.');
    else{
        $count=(int)db()->query('SELECT COUNT(*) total FROM users')->fetch_assoc()['total'];
        if($count<=1) flash('error','The final administrator account cannot be deleted.');
        else{try{$stmt=db()->prepare('DELETE FROM users WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();flash('success','Administrator removed.');}catch(Throwable $e){flash('error',friendly_db_error($e));}}
    }
    redirect('userlist.php');
}
$users=db()->query('SELECT id,username FROM users ORDER BY username');
page_start('Administrators','users');
?>
<div class="page-heading"><div><h1>Administrators</h1><p>Accounts with access to the management system.</p></div><a class="btn" href="signup.php">＋ Add administrator</a></div>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Username</th><th>Account</th><th></th></tr></thead><tbody><?php while($u=$users->fetch_assoc()): ?><tr><td><?= e($u['username']) ?></td><td><?= (int)$u['id']===(int)$_SESSION['user_id']?'<span class="badge badge-success">Current account</span>':'Administrator' ?></td><td><?php if((int)$u['id']!==(int)$_SESSION['user_id']): ?><form method="post" data-confirm="Remove this administrator account?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn btn-small btn-danger">Remove</button></form><?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div></section>
<?php page_end(); ?>
