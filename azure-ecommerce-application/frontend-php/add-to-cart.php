<?php
require __DIR__.'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$productId=filter_input(INPUT_POST,'product_id',FILTER_VALIDATE_INT); $quantity=filter_input(INPUT_POST,'quantity',FILTER_VALIDATE_INT) ?: 1;
if(!$productId || $quantity<1){http_response_code(422);exit('Dữ liệu không hợp lệ');}
$userId=currentUserId(); $key="cart:$userId"; $redis=redisClient(); $redis->hIncrBy($key,(string)$productId,$quantity); $redis->expire($key,86400);
header('Location: /cart.php',true,303);
