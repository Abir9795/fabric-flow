<?php
require_once __DIR__ . '/includes/entity_manager.php';
entity_manager([
 'table'=>'employees','id'=>'employee_id','file'=>'manage_employees.php','active'=>'employees',
 'title'=>'Employees','singular'=>'Employee','description'=>'Maintain employee contact details, roles, and joining dates.',
 'fields'=>[
  'name'=>['label'=>'Name','required'=>true],
  'email'=>['label'=>'Email','type'=>'email','required'=>true],
  'role'=>['label'=>'Role','required'=>true],
  'date_of_joining'=>['label'=>'Date of joining','type'=>'date','required'=>true],
 ],
]);
