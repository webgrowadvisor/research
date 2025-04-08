<?php
    include '../config.php';
    
    $sender_id = $_SESSION['user']['id'];
    
    $oderid = $_POST['reference_number'];
    $orderIDs = $_POST['orderID'];
    $remark = $_POST['remark'];
    
    // URL and data for the curl request
    $url = "https://dtdcapi.shipsy.io/api/customer/integration/consignment/cancel";
    $data = json_encode(array(
        "AWBNo" => ["$oderid"],
        "customerCode" => "GL5665"
    ));

    // Initialize curl session
    $ch = curl_init();

    // Set curl options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Api-key: ghghgjh82c8d671645454c67adcbc0'
    ));

    // Execute curl session
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    // Close curl session
    curl_close($ch);

    // Display response
   
    $array = json_decode($response, true);
    
    $success = $array['success'];
    
    if($success == 'true'){
        mysqli_query($conn, "UPDATE `orders` SET `status`='Cancel' WHERE order_id='$orderIDs'");
        
        mysqli_query($conn, "UPDATE `orders_dhl` SET `status`='Cancel', `cancel_remark`='$remark' WHERE order_id='$orderIDs'");
        
        mysqli_query($conn, "UPDATE `shipping_charge` SET `remark`='Cancel and Money Refund' WHERE order_id='$orderIDs'");
        
        $d = mysqli_fetch_assoc(mysqli_query($conn, "select * from shipping_charge where order_id='$orderIDs' "));
        $refound = $d["charge"];
        
        mysqli_query($conn, "INSERT INTO `recharge`(`user_id`, `amount`, `recharge_by`, `status`) 
        VALUES ('$sender_id','$refound','Refund Cancel Shipping','Active')");
        
        echo "Order Cancel";
       
    }else{
        echo "Order Cancel Error";
    }
?>
<script>
    setTimeout(function() {
        window.location.href = '../label-download.php';
    }, 2000);
</script>
