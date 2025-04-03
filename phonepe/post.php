<?php
// set post fields
$post = [
    'size' => $_POST['size'],
    'merchantId' => $_POST['merchantId'],
    'storeId' => $_POST['storeId'],
    'qrCodeId'   => $_POST['qrCodeId'],
];

$ch = curl_init('https://developer.phonepe.com/v2/reference/transaction-list-api-1/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

// execute!
$response = curl_exec($ch);

// close the connection, release resources used
curl_close($ch);

// do anything you want with your response
var_dump($response);

?>