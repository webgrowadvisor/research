<?php
session_start();
unset($_SESSION['cart']);
unset($_SESSION['order_id']);


$status=$_POST["status"];
$firstname=$_POST["firstname"];
$amount=$_POST["amount"];
$txnid=$_POST["txnid"];
$posted_hash=$_POST["hash"];
$key=$_POST["key"];
$productinfo=$_POST["productinfo"];
$email=$_POST["email"];
$salt="UkojH5TS";

// Salt should be same Post Request 

If (isset($_POST["additionalCharges"])) {
       $additionalCharges=$_POST["additionalCharges"];
        $retHashSeq = $additionalCharges.'|'.$salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
  } else {
        $retHashSeq = $salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
         }
		 $hash = hash("sha512", $retHashSeq);
       if ($hash != $posted_hash) {
	       echo "<center><h3>Invalid Transaction. Please try again</h3></center>";
		   } else {
          echo "<br><br><center><h2>Thank You. Your order status is ". $status .".</h2></center>";
          echo "<center><h3>Your Transaction ID for this transaction is ".$txnid.".</h3></center>";
          echo "<center><h4>We have received a payment of Rs. " . $amount . ". Your order will soon be shipped.</h4></center>";
          echo "<center> <a href='../shop.php' > Home Page </a> </center>";
		   }
?>	
