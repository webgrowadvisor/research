<?php
    include '../config.php';

    date_default_timezone_set('Asia/Kolkata');
    $current = date('Y-m-d H:i:s');
    $sender_id = $_SESSION['user']['id'];
    $sender_mob = $_SESSION['user']['responsible_mobile'];

    $order_id = $_POST['oid'];
    $pickup_id = $_POST['pickup'];
    $grand_total = $_POST['grand_total'];
    $weight_send = $_POST['weight_send'];
    
    $o = mysqli_fetch_assoc(mysqli_query($conn, "select * from orders where id='$order_id' "));
    $p = mysqli_fetch_assoc(mysqli_query($conn, "select * from pickup_address where id='$pickup_id' "));
    $id_order = $o['order_id'];
    
    $delivery_service = $_POST['delivery_service'];
    $spl_service = $_POST['spl_service'];
    
    if($spl_service == 'Ground Express' || $spl_service == 'Ground Express 2'){
        $service_type = "GROUND EXPRESS";
    }else if($spl_service == 'Air Express'){
        $service_type = "STD EXP-A";
    }else if($spl_service == 'Air Cargo Express'){
        $service_type = "AIR CARGO";
    }else if($spl_service == 'Fast Track Premium Express'){
        $service_type = "PREMIUM";
    }else{
        echo "Service Is Empty";
        exit();
    }
    
    
    
        $data = array(
            "consignments" => array(
                array(
                    "customer_code" => "GL7607",
                    "reference_number" => "",
                    "service_type_id" => $service_type, 
                    "load_type" => $o['item_type'],
                    "description" => $o['product_name'],
                    "cod_favor_of" => "",
                    "cod_collection_mode" => "",
                    "consignment_type" => "Forward",
                    "dimension_unit" => "cm",
                    "length" => $o['length'], 
                    "width" => $o['breadth'], 
                    "height" => $o['height'], 
                    "weight_unit" => "kg",
                    // "weight" => $o['weight'],
                    "weight" => $weight_send,
                    "declared_value" => $o['order_val'],
                    "cod_amount" => "",
                    "num_pieces" => "001",
                    "customer_reference_number" => $id_order,
                    "is_risk_surcharge_applicable" => true,
                    "origin_details" => array(
                        "name" => $p['pickup_name'],
                        "phone" => $p['pickup_mobile'],
                        "alternate_phone" => $p['pickup_mobile'],
                        "address_line_1" => $p['address_one'],
                        "address_line_2" => $p['address_two'],
                        "pincode" => $p['pick_pincode'],
                        "city" => $p['pickup_land'],
                        "state" => $p['pickup_land']
                    ),
                    "destination_details" => array(
                        "name" => $o['c_name'],
                        "phone" => $o['c_mobile'],
                        "alternate_phone" => $o['c_mobile'],
                        "address_line_1" => $o['c_coplete_address'],
                        "address_line_2" => "",
                        "pincode" => $o['c_pincode'],
                        "city" => $o['c_state'],
                        "state" => $o['c_state']
                    ),
                    "pieces_detail" => array(
                        array(
                            "description" => $o['product_name'],
                            "declared_value" => $o['order_val'],
                            "weight" => $o['weight'],
                            "height" => $o['height'],
                            "length" => $o['length'],
                            "width" => $o['breadth']
                        )
                    )
                )
            )
        );
        
        
       $jsonData = json_encode($data);
        
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dtdcapi.shipsy.io/api/customer/integration/consignment/softdata",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                "api-key: ckajkfjpe5585454jfidalkjkdj",
                "Content-Type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            
            // echo $response;
            $data = json_decode($response, true);
        }
        
        if ($data['status'] == 'OK') {
            // Access the variables
            $success = $data['data'][0]['success'];
            $reference_number = $data['data'][0]['reference_number'];
            $courier_partner = $data['data'][0]['courier_partner'];
            $courier_account = $data['data'][0]['courier_account'];
            $courier_partner_reference_number = $data['data'][0]['courier_partner_reference_number'];
            $chargeable_weight = $data['data'][0]['chargeable_weight'];
            $self_pickup_enabled = $data['data'][0]['self_pickup_enabled'];
            $customer_reference_number = $data['data'][0]['customer_reference_number'];
            $pieces_reference_number = $data['data'][0]['pieces'][0]['reference_number'];
            $pieces_product_code = $data['data'][0]['pieces'][0]['product_code'];
            $barCodeData = $data['data'][0]['barCodeData'];
        
        
            // Order Details Insert in Table  
            $sqls = "INSERT INTO `orders_dhl`(`status`, `order_id`, `reference_number`, `reference_number_2`, `courier_partner`, `chargeable_weight`, `self_pickup_enabled`, 
                `delivery_service`, `spl_service`) 
                VALUES ('$success','$customer_reference_number','$reference_number','$pieces_reference_number','$courier_partner','$chargeable_weight','$self_pickup_enabled',
                '$delivery_service','$spl_service')";
            mysqli_query($conn, $sqls);
            
            // Order Status Updted  
            mysqli_query($conn, "UPDATE `orders` SET `status`='In-Transit' WHERE id='$order_id'");
            
            // Shipping charge maintion order booked
            mysqli_query($conn, "INSERT INTO `shipping_charge`(`user_id`, `order_id`, `awb`, `charge`, `remark`) 
            VALUES ('$sender_id','$customer_reference_number','$reference_number','$grand_total','Shipping Charge')");
            
            // amount debit for service 
            mysqli_query($conn, "INSERT INTO `recharge`(`user_id`, `amount`, `recharge_by`, `status`) VALUES ('$sender_id','-".$grand_total."','Shipping Charge','Active')");
            
            echo "Status: Order Booked !";
            
        } else {
            echo "Error: Status is Order Not Booked";
        }
        

?>


