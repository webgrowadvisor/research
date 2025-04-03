<?php
error_reporting(0);
$MERCHANT_KEY = "piRLGedw";
$SALT = "QuaObIKM88";

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
// Merchant Key and Salt as provided by Payu.

// $PAYU_BASE_URL = "https://test.payu.in";		// For Sandbox Mode
$PAYU_BASE_URL = "https://secure.payu.in";    // For Live Mode

// $amount = 100;
// $name = "User";
// $email = "email@gmail.com";
// $mobile = 1234569870;

$action = '';
		$posted = array(
			'key' =>  $MERCHANT_KEY,
			'txnid' =>  $txnid,
			'amount'  =>   $amount,
			'firstname' =>  $name,
			'email' =>  $email,
			'phone' =>  $mobile,   //mobile no
			'productinfo' =>  'Renewal Account',
			'surl'  =>  'http://localhost/renewalc/payu/success.php',
			'furl'  =>  'http://localhost/renewalc/payu/failure.php',
			'service_provider'  =>  'payu_paisa',
		);

if(!empty($_POST)) {
    //print_r($_POST);
  foreach($_POST as $key => $value) {
    $posted[$key] = $value;
  }
}

$formError = 0;

if(empty($posted['txnid'])) {
  // Generate random transaction id
  $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
} else {
  $txnid = $posted['txnid'];
}
$hash = '';
// Hash Sequence
$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
if(empty($posted['hash']) && sizeof($posted) > 0) {
  if(
          empty($posted['key'])
          || empty($posted['txnid'])
          || empty($posted['amount'])
          || empty($posted['firstname'])
          || empty($posted['email'])
          || empty($posted['phone'])
          || empty($posted['productinfo'])
          || empty($posted['surl'])
          || empty($posted['furl'])
		  || empty($posted['service_provider'])
  ) {
    $formError = 1;
  } else {
    //$posted['productinfo'] = json_encode(json_decode('[{"name":"tutionfee","description":"","value":"500","isRequired":"false"},{"name":"developmentfee","description":"monthly tution fee","value":"1500","isRequired":"false"}]'));
	$hashVarsSeq = explode('|', $hashSequence);
    $hash_string = '';	
	foreach($hashVarsSeq as $hash_var) {
      $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
      $hash_string .= '|';
    }

    $hash_string .= $SALT;


    $hash = strtolower(hash('sha512', $hash_string));
    $action = $PAYU_BASE_URL . '/_payment';
  }
} elseif(!empty($posted['hash'])) {
  $hash = $posted['hash'];
  $action = $PAYU_BASE_URL . '/_payment';
}
?>
<html>

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
	<title>PayU Now</title>
	<meta content="Admin Dashboard" name="description" />
	<meta content="Themesbrand" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<!-- App Icons -->
	<link rel="shortcut icon" href="../assets/public/assets/images/favicon.ico">
	<!-- App css -->
	<link href="../assets/public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="../assets/public/assets/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../assets/public/assets/css/style.css" rel="stylesheet" type="text/css" />

</head>

<body onload="submitPayuForm()" >
	<!-- <body > -->

	<script>
		var hash = '<?php echo $hash ?>';
		function submitPayuForm() {
			if (hash == '') {
				return;
			}
			var payuForm = document.forms.payuForm;
			payuForm.submit();
		}
	</script>

	<div id="preloader">
		<div id="status">
			<div class="spinner"></div>
		</div>
	</div>
	<!-- Begin page -->
	<div class="accountbg"></div>
	<div class="wrapper-page">

		<div class="card">
			<div class="card-body">

				<h3 class="text-center m-0">
					<!-- <a href="" class="logo logo-admin"><img src="../assets/public/assets/images/logo.png" height="30" alt="logo"></a> -->
					<h4 class="font-18 m-b-5 text-center">Pay Now </h4>
				</h3>

				<div class="p-3">
					<!-- <h4 class="font-18 m-b-5 text-center">Welcome Back !</h4> -->
					<p class="text-muted text-center">Please fill all mandatory fields.</p>

					<?php if($formError) { ?>										
						<span style="color:red">Please fill all mandatory fields.</span>
					<?php } ?>

					<form action="<?php echo $action; ?>" method="post" name="payuForm">
						<input type="hidden" name="key" value="<?php echo $MERCHANT_KEY ?>" />
						<input type="hidden" name="hash" value="<?php echo $hash ?>" />
						<input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
						
							<div class="form-group">
								<label for="username">Amount:</label>
								<input name="amount" class="form-control" required=""
									value="<?php echo (empty($posted['amount'])) ? '' : $posted['amount'] ?>" />
							</div>

							<div class="form-group">
								<label for="username">First Name:</label>
								<input name="firstname" class="form-control" required="" id="firstname"
									value="<?php echo (empty($posted['firstname'])) ? '' : $posted['firstname']; ?>" />
							</div>

							<div class="form-group">
								<label for="username">Emaile:</label>
								<input name="email" class="form-control" required="" id="email"
									value="<?php echo (empty($posted['email'])) ? '' : $posted['email']; ?>" />
							</div>

							<div class="form-group">
								<label for="username">Phone:</label>
								<input name="phone" class="form-control" required=""
									value="<?php echo (empty($posted['phone'])) ? '' : $posted['phone']; ?>" />
							</div>

							<div class="form-group">
								<label for="username">Product Info:</label>
								<textarea required="" class="form-control"
									name="productinfo"><?php echo (empty($posted['productinfo'])) ? '' : $posted['productinfo'] ?></textarea>
							</div>

							<input type="hidden" name="surl" value="<?php echo (empty($posted['surl'])) ? '' : $posted['surl'] ?>"
								size="64" />
							<input type="hidden" name="furl" value="<?php echo (empty($posted['furl'])) ? '' : $posted['furl'] ?>"
								size="64" />
							<input type="hidden" name="service_provider" value="payu_paisa" size="64" />

							<div class="form-group row m-t-20">
								<div class="col-sm-12 text-right">
									<?php if(!$hash) { ?>
									<!-- <td colspan="4"><input name="payunow" type="submit" value="Submit" /></td> -->
									<button name="payunow" class="btn btn-primary w-100 waves-effect waves-light"
										type="submit">Submit</button>
									<?php } ?>
								</div>
							</div>
					</form>
				</div>

			</div>
		</div>


	</div>

	<script src="../assets/public/assets/js/jquery.min.js"></script>
	<script src="../assets/public/assets/js/bootstrap.bundle.min.js"></script>
	<script src="../assets/public/assets/js/modernizr.min.js"></script>
	<script src="../assets/public/assets/js/jquery.slimscroll.js"></script>
	<script src="../assets/public/assets/js/waves.js"></script>
	<script src="../assets/public/assets/js/jquery.nicescroll.js"></script>
	<script src="../assets/public/assets/js/jquery.scrollTo.min.js"></script>

	<!-- App js -->
	<script src="../assets/public/assets/js/app.js"></script>
</body>

</html>
