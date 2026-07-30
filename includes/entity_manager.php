<?php
require_once __DIR__ . '/layout.php';

function entity_manager(array $config): void
{
    require_auth();
    $table = $config['table'];
    $id = $config['id'];
    $fields = $config['fields'];

    if (is_post()) {
        verify_csrf();
        $action = post_string('action', 20);
        try {
            if ($action === 'delete') {
                $recordId = post_int($id);
                if (!$recordId) throw new RuntimeException('Invalid record.');
                $stmt = db()->prepare("DELETE FROM `$table` WHERE `$id` = ?");
                $stmt->execute([$recordId]);
                flash('success', $config['singular'] . ' deleted.');
            } elseif ($action === 'add' || $action === 'edit') {
                $values = [];
                foreach ($fields as $name => $field) {
                    $value = trim((string)($_POST[$name] ?? ''));
                    if (($field['required'] ?? false) && $value === '') {
                        throw new RuntimeException($field['label'] . ' is required.');
                    }
                    if (($field['type'] ?? '') === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new RuntimeException('Enter a valid email address.');
                    }
                    if (($field['type'] ?? '') === 'date' && $value !== '' && !valid_date($value)) {
                        throw new RuntimeException('Enter a valid ' . strtolower($field['label']) . '.');
                    }
                    if (in_array(($field['type'] ?? ''), ['number','decimal'], true) && ($value === '' || !is_numeric($value) || (float)$value < ($field['min'] ?? 0))) {
                        throw new RuntimeException($field['label'] . ' must be ' . ($field['min'] ?? 0) . ' or greater.');
                    }
                    if (!empty($field['options']) && !in_array($value, $field['options'], true)) {
                        throw new RuntimeException('Invalid ' . strtolower($field['label']) . '.');
                    }
                    $values[$name] = $value;
                }
                if ($action === 'add') {
                    $columns = implode(',', array_map(fn($name) => "`$name`", array_keys($fields)));
                    $marks = implode(',', array_fill(0, count($fields), '?'));
                    $stmt = db()->prepare("INSERT INTO `$table` ($columns) VALUES ($marks)");
                    $stmt->execute(array_values($values));
                    flash('success', $config['singular'] . ' added.');
                } else {
                    $recordId = post_int($id);
                    if (!$recordId) throw new RuntimeException('Invalid record.');
                    $sets = implode(',', array_map(fn($name) => "`$name` = ?", array_keys($fields)));
                    $stmt = db()->prepare("UPDATE `$table` SET $sets WHERE `$id` = ?");
                    $stmt->execute([...array_values($values), $recordId]);
                    flash('success', $config['singular'] . ' updated.');
                }
            }
        } catch (Throwable $error) {
            flash('error', $error instanceof RuntimeException ? $error->getMessage() : friendly_db_error($error));
        }
        redirect($config['file']);
    }

    $search = trim((string)($_GET['search'] ?? ''));
    $searchable = array_keys(array_filter($fields, fn($field) => in_array(($field['type'] ?? 'text'), ['text','email','textarea'], true)));
    if ($search !== '' && $searchable) {
        $where = implode(' OR ', array_map(fn($name) => "`$name` LIKE ?", $searchable));
        $stmt = db()->prepare("SELECT * FROM `$table` WHERE $where ORDER BY `$id` DESC");
        $stmt->execute(array_fill(0, count($searchable), "%$search%"));
        $records = $stmt->get_result();
    } else {
        $records = db()->query("SELECT * FROM `$table` ORDER BY `$id` DESC");
    }
    page_start($config['title'], $config['active']);
    ?>
    <div class="page-heading"><div><h1><?= e($config['title']) ?></h1><p><?= e($config['description']) ?></p></div></div>
    <section class="card">
        <h2>Add <?= e(strtolower($config['singular'])) ?></h2>
        <form method="post"><input type="hidden" name="action" value="add"><?= csrf_field() ?><div class="form-grid">
            <?php foreach ($fields as $name => $field) render_entity_field($name, $field); ?>
        </div><div class="actions"><button class="btn">Add <?= e(strtolower($config['singular'])) ?></button></div></form>
    </section>
    <section class="card">
        <h2><?= e($config['title']) ?> list</h2>
        <form class="search-row" method="get"><input name="search" value="<?= e($search) ?>" placeholder="Search <?= e(strtolower($config['title'])) ?>"><button class="btn btn-secondary">Search</button><?php if($search!==''): ?><a class="btn btn-secondary" href="<?= e($config['file']) ?>">Clear</a><?php endif; ?></form>
        <?php if ($records->num_rows): ?><div class="table-wrap"><table><thead><tr>
            <?php foreach ($fields as $field): ?><th><?= e($field['label']) ?></th><?php endforeach; ?><th>Actions</th>
        </tr></thead><tbody>
        <?php while ($row = $records->fetch_assoc()): ?><tr>
            <?php foreach ($fields as $name => $field): ?><td><?= ($field['type'] ?? '') === 'decimal' ? '$'.number_format((float)$row[$name],2) : e($row[$name]) ?></td><?php endforeach; ?>
            <td><div class="actions" style="margin:0;flex-wrap:nowrap">
                <?php if (!empty($config['view'])): ?><a class="btn btn-small" href="<?= e($config['view']['file']) ?>?<?= e($config['view']['param']) ?>=<?= (int)$row[$id] ?>"><?= e($config['view']['label']) ?></a><?php endif; ?>
                <details><summary class="btn btn-small btn-secondary">Edit</summary><form method="post" class="card" style="position:absolute;right:30px;z-index:5;min-width:min(520px,90vw)"><input type="hidden" name="action" value="edit"><input type="hidden" name="<?= e($id) ?>" value="<?= (int)$row[$id] ?>"><?= csrf_field() ?><div class="form-grid"><?php foreach($fields as $name=>$field) render_entity_field($name,$field,$row[$name]); ?></div><div class="actions"><button class="btn">Save changes</button></div></form></details>
                <form method="post" data-confirm="Delete this <?= e(strtolower($config['singular'])) ?>?"><input type="hidden" name="action" value="delete"><input type="hidden" name="<?= e($id) ?>" value="<?= (int)$row[$id] ?>"><?= csrf_field() ?><button class="btn btn-small btn-danger">Delete</button></form>
            </div></td>
        </tr><?php endwhile; ?></tbody></table></div><?php else: ?><div class="empty">No records found.</div><?php endif; ?>
    </section>
    <?php page_end();
}

function render_entity_field(string $name, array $field, mixed $value = ''): void
{
    $type = $field['type'] ?? 'text';
    $required = ($field['required'] ?? false) ? 'required' : '';
    echo '<div class="form-group"><label for="'.e($name).'">'.e($field['label']).'</label>';
    if (!empty($field['options'])) {
        echo '<select name="'.e($name).'" '.$required.'>';
        foreach ($field['options'] as $option) {
            echo '<option '.((string)$value === (string)$option ? 'selected' : '').'>'.e($option).'</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea name="'.e($name).'" '.$required.'>'.e($value).'</textarea>';
    } else {
        $htmlType = $type === 'decimal' ? 'number' : $type;
        $step = $type === 'decimal' ? 'step="0.01"' : '';
        $min = isset($field['min']) ? 'min="'.e($field['min']).'"' : '';
        echo '<input type="'.e($htmlType).'" name="'.e($name).'" value="'.e($value).'" '.$step.' '.$min.' '.$required.'>';
    }
    echo '</div>';
}
