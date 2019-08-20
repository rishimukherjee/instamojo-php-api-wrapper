<?php

require_once __DIR__ . '/vendor/autoload.php';

    $instaobj = Instamojo\Instamojo::init('app',[
        "client_id" =>  $_ENV["CLIENT_ID"],
        "client_secret" => $_ENV["CLIENT_SECRET"]
       
    ],true);
 
    
    $transaction_id = "TEST_".time();
    var_dump([
        "name" => "XYZ",
        "email" => "xyz@squareboat.com",
        "phone" => "9999999988",
        "amount" => 200,
        "transaction_id" => $transaction_id,
        "currency" => "INR"
    ]);

    $gateway_order = $instaobj->createGatewayOrder([
        "name" => "XYZ",
        "email" => "xyz@squareboat.com",
        "phone" => "9999999988",
        "amount" => 200,
        "transaction_id" => $transaction_id,
        "currency" => "INR"
    ]);
    
    var_dump($gateway_order);

?>