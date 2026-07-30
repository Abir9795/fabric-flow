<?php
require_once __DIR__ . '/includes/entity_manager.php';
entity_manager([
 'table'=>'carriers','id'=>'carrier_id','file'=>'manage_carriers.php','active'=>'carriers',
 'title'=>'Carriers','singular'=>'Carrier','description'=>'Maintain delivery partners and their contact information.',
 'view'=>['file'=>'view_carrier_orders.php','param'=>'carrier_id','label'=>'Orders'],
 'fields'=>[
  'name'=>['label'=>'Name','required'=>true],
  'phone_number'=>['label'=>'Phone number','required'=>true],
  'email'=>['label'=>'Email','type'=>'email','required'=>true],
  'company'=>['label'=>'Company'],
  'date_of_registration'=>['label'=>'Registration date','type'=>'date','required'=>true],
 ],
]);
