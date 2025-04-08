<?php
include 'db/config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        
        // header("location:javascript://history.go(-1)");
        $awb = $_GET['number'];
        
    }else{

        if($_POST['subject'] == 'awb'){
            $awb = $_POST['number'];
        }else{
            $orderid = $_POST['number'];
            $meta = mysqli_fetch_assoc(mysqli_query($conn, "select * from orders_dhl where order_id='$orderid' "));
            $awb = $meta['reference_number'];
        }
    }

    $trackData = array(
        "trkType" => "cnno",
        "strcnno" => $awb,
        "addtnlDtl" => "Y"
    );
    
      // Function to authenticate and get the access token
      function authenticate($url, $username, $password) {
    // Create the full URL with query parameters
    $fullUrl = $url . "?username=" . urlencode($username) . "&password=" . urlencode($password);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
        return false;
    } else {
        // Return the access token
        return $response;
    }

    // Close cURL session
    curl_close($ch);
}

// Function to track a parcel
function trackParcel($url, $accessToken, $data) {
    // Encode the data to JSON
    $jsonData = json_encode($data);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "x-access-token: $accessToken"
    ));

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
    } else {
        // Output the response
        // echo "Response: $response";
      return $ar = json_decode($response);
    //   echo "<pre>";
    //   print_r($ar);
    //   echo "</pre>";
    }

    // Close cURL session
    curl_close($ch);
}

// Main script
$authUrl = "https://blktracksvc.dtdc.com/dtdc-api/api/dtdc/authenticate";
$username = "GL47580_yrk_json";
$password = "4ymkhKm";
$accessToken = authenticate($authUrl, $username, $password);

if ($accessToken) {
    $trackUrl = "https://blktracksvc.dtdc.com/dtdc-api/rest/JSONCnTrk/getTrackDetails";
    // $trackData = array(
    //     "trkType" => "cnno",
    //     "strcnno" => "D01385778",
    //     "addtnlDtl" => "Y"
    // );
   $data = trackParcel($trackUrl, $accessToken, $trackData);
   $trackDetails = $data->trackDetails;
   $trackHeader = $data->trackHeader;
}
?>
<!DOCTYPE html>
<html>
<head>
      <meta charset="utf-8">
      <meta http-equiv="x-ua-compatible" content="ie=edge">
      <title>Tracking Details</title>
      <meta name="robots" content="noindex, follow">
      <meta name="description" content="<?=$meta['desc']?>">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <?php include "top-links.php";?>
      
      
    <style>
        
       
        
        /* Table Styles */
        
        .table-wrapper{
            margin: 10px 70px 70px;
            box-shadow: 0px 35px 50px rgba( 0, 0, 0, 0.2 );
        }
        
        .fl-table {
            border-radius: 5px;
            font-size: 12px;
            font-weight: normal;
            border: none;
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            white-space: nowrap;
            background-color: white;
        }
        
        .fl-table td, .fl-table th {
            text-align: center;
            padding: 8px;
        }
        
        .fl-table td {
            border-right: 1px solid #f8f8f8;
            font-size: 12px;
        }
        
        .fl-table thead th {
            color: #ffffff;
            background: #4FC3A1;
        }
        
        
        .fl-table thead th:nth-child(odd) {
            color: #ffffff;
            background: #324960;
        }
        
        .fl-table tr:nth-child(even) {
            background: #F8F8F8;
        }
        
        /* Responsive */
        
        @media (max-width: 767px) {
            .fl-table {
                display: block;
                width: 100%;
            }
            .table-wrapper:before{
                content: "Scroll horizontally >";
                display: block;
                text-align: right;
                font-size: 11px;
                color: white;
                padding: 0 0 10px;
            }
            .fl-table thead, .fl-table tbody, .fl-table thead th {
                display: block;
            }
            .fl-table thead th:last-child{
                border-bottom: none;
            }
            .fl-table thead {
                float: left;
            }
            .fl-table tbody {
                width: auto;
                position: relative;
                overflow-x: auto;
            }
            .fl-table td, .fl-table th {
                padding: 20px .625em .625em .625em;
                height: 60px;
                vertical-align: middle;
                box-sizing: border-box;
                overflow-x: hidden;
                overflow-y: auto;
                width: 120px;
                font-size: 13px;
                text-overflow: ellipsis;
            }
            .fl-table thead th {
                text-align: left;
                border-bottom: 1px solid #f7f7f9;
            }
            .fl-table tbody tr {
                display: table-cell;
            }
            .fl-table tbody tr:nth-child(odd) {
                background: none;
            }
            .fl-table tr:nth-child(even) {
                background: transparent;
            }
            .fl-table tr td:nth-child(odd) {
                background: #F8F8F8;
                border-right: 1px solid #E6E4E4;
            }
            .fl-table tr td:nth-child(even) {
                border-right: 1px solid #E6E4E4;
            }
            .fl-table tbody td {
                display: block;
                text-align: center;
            }
            
            
        }
        
         @media (max-width: 767px) {
        /* Adjustments for smaller screens */
        .table-wrapper {
            margin: 10px 10px 10px; /* Adjust margins for smaller screens */
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2); /* Lighter shadow */
        }

        .fl-table td, .fl-table th {
            padding: 12px 8px; /* Increase padding for better touch interactions */
            font-size: 11px; /* Smaller font size for better readability on smaller screens */
        }

        .fl-table thead th {
            font-size: 12px; /* Increase header font size */
        }

        .fl-table tbody td {
            padding: 12px 8px; /* Increase padding */
            font-size: 11px; /* Smaller font size */
        }
    }
    
    </style>
</head>
<body>
    
    <div class="page-wrapper">
	  <?php include "header.php";?>
    
        <h2></h2>
        <h2></h2>
        <div class="table-wrapper" style="overflow-x:auto;">
            <table class="fl-table">
                <thead>
                <tr>
                    <th>ShipmentNo</th>
                    <th>CNProduct</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>BookedDate</th>
                    <th>ExpectedDeliveryDate</th>
                </tr>
                </thead>
                <tbody>
                <?php
                    $booked = DateTime::createFromFormat('dmY', $data->trackHeader->strBookedDate);
                    $expdate = DateTime::createFromFormat('dmY', $data->trackHeader->strExpectedDeliveryDate);
                    // $time = DateTime::createFromFormat('Hi', $data->trackHeader->strStatus);
                ?>
                <tr>
                    <td> <?= $data->trackHeader->strShipmentNo ?></td>
                    <td> <?= $data->trackHeader->strCNProduct ?></td>
                    <td> <?= $data->trackHeader->strOrigin ?></td>
                    <td> <?= $data->trackHeader->strDestination ?></td>
                    <td> <?= $data->trackHeader->strStatus ?></td>
                    <td><?= $booked->format('Y-m-d') ?> </td>
                    <td> <?= $expdate->format('Y-m-d') ?></td>
                </tr>
                <tbody>
            </table>
        </div>
        
        <div class="table-wrapper" style="overflow-x:auto;">
            <table class="fl-table">
                <thead>
                <tr>
                    <th>S.No</th>
                    
                    <th>Action</th>
                    
                    <th>Origin</th>
                    <th>Destination</th>
                   
                    <th>ActionDate</th>
                    <th>ActionTime</th>
                    <th>Remarks</th>
                    
                </tr>
                </thead>
                <tbody>
                <?php
                $count = 1;
                foreach($trackDetails as $data){
                    $date = DateTime::createFromFormat('dmY', $data->strActionDate);
                    $time = DateTime::createFromFormat('Hi', $data->strActionTime);
                ?>
                <tr>
                    <td> <?= $count++ ?> </td>
                    <!--<td> <?= $data->strCode ?></td>-->
                    <td><?= $data->strAction ?></td>
                    <!--<td><?= $data->strManifestNo ?></td>-->
                    <td><?= $data->strOrigin ?></td>
                    <td><?= $data->strDestination ?></td>
                    <!--<td ><?= $data->strOriginCode ?></td>-->
                    <!--<td ><?= $data->strDestinationCode ?></td>-->
                    <td > <?= $date->format('Y-m-d') ?></td>
                    <td><?= $time->format('H:i') ?></td>
                    <td ><?= $data->sTrRemarks ?></td>
                    <!--<td ><?= $data->strLatitude ?></td>-->
                    <!--<td><?= $data->strLongitude ?></td>-->
                </tr>
                <?php } ?>
                
                <tbody>
            </table>
        </div>
    
     <?php include "footer.php";?>
    </div>
    
</body>
</html>

