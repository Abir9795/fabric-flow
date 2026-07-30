<?php
require_once __DIR__ . '/includes/entity_manager.php';
entity_manager([
 'table'=>'customers','id'=>'customer_id','file'=>'manage_customers.php','active'=>'customers',
 'title'=>'Customers','singular'=>'Customer','description'=>'Create, update, and safely maintain customer records.',
 'view'=>['file'=>'view_orders.php','param'=>'customer_id','label'=>'Orders'],
 'fields'=>[
  'name'=>['label'=>'Name','required'=>true],
  'email'=>['label'=>'Email','type'=>'email','required'=>true],
  'phone_number'=>['label'=>'Phone number'],
  'address'=>['label'=>'Address','type'=>'textarea'],
  'registration_date'=>['label'=>'Registration date','type'=>'date','required'=>true],
 ],
]);
