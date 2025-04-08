
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
    //     "strcnno" => "D451385778",
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
       #TrackingDeatilsTable    {
        border-spacing: 1;
        border-collapse: collapse;
        background: white;
        border-radius: 6px;
        overflow: hidden;
        max-width: 800px;
        width: 100%;
        margin: 0 auto;
        position: relative;
        padding:30px 0;
        
      }
        #TrackingDeatilsTable * {
        position: relative;
      }
        #TrackingDeatilsTable td, table th {
        padding-left: 8px;
      }
        #TrackingDeatilsTable thead tr {
        height: 60px;
        background: #ffed86;
        font-size: 16px;
      }
        #TrackingDeatilsTable tbody tr {
        height: 48px;
        border-bottom: 1px solid #e3f1d5;
      }
        #TrackingDeatilsTable tbody tr:last-child {
        border: 0;
      }
        #TrackingDeatilsTable td, table th {
        text-align: left;
      }
        #TrackingDeatilsTable td.l, table th.l {
        text-align: right;
      }
        #TrackingDeatilsTable td.c, table th.c {
        text-align: center;
      }
        #TrackingDeatilsTable td.r, table th.r {
        text-align: center;
      }
      @media screen and (max-width: 35.5em) {
          #TrackingDeatilsTable {
          display: block;
        }
          #TrackingDeatilsTable > *,  #TrackingDeatilsTable tr,  #TrackingDeatilsTable td,  #TrackingDeatilsTable th {
          display: block;
        }
          #TrackingDeatilsTable thead {
          display: none;
        }
          #TrackingDeatilsTable tbody tr {
          height: auto;
          padding: 8px 0;
        }
          #TrackingDeatilsTable tbody tr td {
          padding-left: 45%;
          margin-bottom: 12px;
        }
          #TrackingDeatilsTable tbody tr td:last-child {
          margin-bottom: 0;
        }
          #TrackingDeatilsTable tbody tr td:before {
          position: absolute;
          font-weight: 700;
          width: 40%;
          left: 10px;
          top: 0;
        }
          #TrackingDeatilsTable tbody tr td:nth-child(1):before {
          content: "Shipment No";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(2):before {
          content: "CN Product";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(3):before {
          content: "Origin";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(4):before {
          content: "Destination";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(5):before {
          content: "Status";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(5):before {
          content: "Booked Date";
        }
          #TrackingDeatilsTable tbody tr td:nth-child(5):before {
          content: "Expected Delivery Date";
        }
      }
  </style>
    
</head>
<body>
    
    <div class="page-wrapper">
        
	  <?php include "header.php";?>
    
        	<div class="pbmit-title-bar-wrapper mb-5">
			<div class="container">
				<div class="pbmit-title-bar-content">
					<div class="pbmit-title-bar-content-inner">
						<div class="pbmit-tbar">
							<div class="pbmit-tbar-inner">
								<h1 class="pbmit-tbar-title"> Tracking Details</h1>
							</div>
						</div>
						<div class="pbmit-breadcrumb">
							<div class="pbmit-breadcrumb-inner">
								<span><a title="" href="#" class="home"><span>Home</span></a></span>
								<span class="sep">
									<i class="pbmit-base-icon-angle-right"></i>
								</span>
								<span><span class="post-root post post-post current-item"> Tracking Details</span></span>
							</div>
						</div>
					</div>
				</div> 
			</div> 
		</div>
		
      <table id="TrackingDeatilsTable" >
      <thead>
        <tr>
          <th>Shipment No</th>
          <th>CN Product</th>
          <th>Origin</th>
          <th>Destination</th>
          <th> Status</th>
          <th>Booked Date</th>
          <th>Expected Delivery Date</th>
        </tr>
      <thead>
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
           
      </tbody>
      <table/>
      
    
     <table id="TrackingDeatilsTable" >
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
      <thead>
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
           
      </tbody>
      <table/>
      
    
    <div class="mb-5">
     <?php include "footer.php";?>
     </div>
    </div>
    
</body>
</html>