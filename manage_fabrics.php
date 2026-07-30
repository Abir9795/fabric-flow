<?php
require_once __DIR__ . '/includes/entity_manager.php';
entity_manager([
 'table'=>'fabrics','id'=>'fabric_id','file'=>'manage_fabrics.php','active'=>'fabrics',
 'title'=>'Inventory','singular'=>'Fabric','description'=>'Track fabric pricing and available stock. Values cannot be negative.',
 'fields'=>[
  'fabric_name'=>['label'=>'Fabric name','required'=>true],
  'fabric_type'=>['label'=>'Fabric type','required'=>true,'options'=>['Natural','Synthetic','Blend','Semi-synthetic']],
  'price_per_meter'=>['label'=>'Price per meter','type'=>'decimal','required'=>true,'min'=>0],
  'stock_quantity'=>['label'=>'Stock quantity','type'=>'number','required'=>true,'min'=>0],
 ],
]);
