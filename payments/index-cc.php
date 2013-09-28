<?php

// Load Stripe library
require_once 'lib/Stripe.php';

// Load configuration settings
require_once 'files/config.php';

//Check if mobile is set in url
$is_mobile = $_GET['mobile'];

// Force https
if( $_SERVER["HTTPS"] != "on") {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
	exit();
}

if ($_POST) {
	Stripe::setApiKey($config['secret-key']);

	// POSTed Variables
	$token = $_POST['stripeToken'];
	$first_name = ucfirst($_POST['first-name']);
	$last_name 	= ucfirst($_POST['last-name']);
	$name 			= $first_name . ' ' . $last_name;
	$address = $_POST['address']."\n" . ucfirst($_POST['city']) . ', ' . $_POST['state'] . ' ' . $_POST['zip'];
	$email   = $_POST['email'];
	$phone   = preg_replace('[\D]', '', $_POST['phone']);
	$otherinfo   = trim($_POST['otherinfo']);
	$amount  = (float) $_POST['amount'];
	$interval_with_count = $_POST['interval_with_count'];
	$interval_count = $_POST['interval_count'];
	$interval = $_POST['interval'];


	try {
		if ( ! isset($_POST['stripeToken']) ) {
			throw new Exception("The Stripe Token was not generated correctly");
		}

	if($otherinfo != "") {
	$otherinfo	= ' Other Info: '.$otherinfo;
	}

//Lets find out type of payment (ie. single, recurring)
if($interval_with_count == 'onetime') {

		//Create Customer
		/*$customer = Stripe_Customer::create(array(
			'card' => $token,
			'email' => strip_tags(trim($_POST['email'])),
			"description" => $plan['id']." Custom Info: ".$otherinfo
			)
				);	*/
				
		// Charge the card
		$donation = Stripe_Charge::create(array(
			'card' => $token,
			'description' => 'Donation by ' . $name . ' (' . $email . ')'.$otherinfo,
			'amount' => $amount * 100,
			'currency' => 'usd')
		);

		// Build and send the email
		$headers = "From: ".$config['email-from-name']." <".$config['email-from'].">" . "\r\nBcc: " . $config['email-bcc'] . "\r\n\r\n";

		// Find and replace values
		$find = array('%name%', '%amount%');
		$replace = array($name, '$' . $amount);

		$message = str_replace($find, $replace , $config['email-message']) . "\n\n";
		$message .= "Amount: $" . $amount . "\n";
		$message .= "Address: " . $address . "\n";
		$message .= "Phone: " . $phone . "\n";
		$message .= "Email: " . $email . "\n";
		$message .= "Date: " . date('M j, Y, g:ia', $donation['created']) . "\n";
		$message .= "Transaction ID: " . $donation['id'] . "\n\n\n";

		$subject = $config['email-subject'];

		// Send it
			mail($email,$subject,$message,$headers);
			
			//Start receipt for administrator!
			
			// Build and send the email
			$email = $config['email-notify'];
		$headers = "From: ".$config['email-from-name']." <".$config['email-from'].">" . "\r\n\r\n";

		// Find and replace values
		$find = array('%name%', '%amount%');
		$replace = array($name, '$' . $amount);

		$message = str_replace($find, $replace , $config['email-notify-message']) . "\n\n";
		$message .= "Amount: $" . $amount . "\n";
		$message .= "Frequency: Every ".$intc." ".$int."(s)\n";
		$message .= "Address: " . $address . "\n";
		$message .= "Phone: " . $phone . "\n";
		$message .= "Email: " . $email . "\n";
		$message .= "Date: " . date('M j, Y, g:ia', $customer['created']) . "\n";
		$message .= "Subscription ID: " . $plan['id'] . "\n\n\n";

		$subject = $config['email-notify-subject'];

		// Send it
			mail($email,$subject,$message,$headers);

		// Forward to "Thank You" page
		header('Location: ' . $config['thank-you']);
		exit;
}else if($interval_with_count != 'onetime') {

//Gather info for frequency
if($interval_with_count == 'custom') {
	$int = $interval;
	$intc = $interval_count;
}elseif($interval_with_count == 'week') {
	$int = "week";
	$intc = "1";
}elseif($interval_with_count == 'month') {
	$int = "month";
	$intc = "1";
}elseif($interval_with_count == 'year') {
	$int = "year";
	$intc = "1";
}elseif($interval_with_count == '3-month') {
	$int = "month";
	$intc = "3";
}elseif($interval_with_count == '6-month') {
	$int = "month";
	$intc = "6";
}

// Create the plan
		$plan = Stripe_Plan::create(array(
  "amount" => $amount * 100,
  "interval" => $int,
  "interval_count" => $intc,
  "name" => "Recurring Donation: ".$first_name." ".$last_name,
  "currency" => "usd",
  "id" => $email." Every ".$intc." ".ucfirst($int)."(s) Amount: $".$amount." -".time())
);

//Create the customer		
		$customer = Stripe_Customer::create(array(
			'card' => $token,
			'plan' => $plan['id'],
			'email' => strip_tags(trim($_POST['email'])),
			"description" => $plan['id'].$otherinfo
			)
				);	
		
				// Build and send the email
		$headers = "From: ".$config['email-from-name']." <".$config['email-from'].">" . "\r\n\r\n";

		// Find and replace values
		$find = array('%name%', '%amount%');
		$replace = array($name, '$' . $amount);

		$message = str_replace($find, $replace , $config['email-message']) . "\n\n";
		$message .= "Amount: $" . $amount . "\n";
		$message .= "Frequency: Every ".$intc." ".$int."(s)\n";
		$message .= "Address: " . $address . "\n";
		$message .= "Phone: " . $phone . "\n";
		$message .= "Email: " . $email . "\n";
		$message .= "Date: " . date('M j, Y, g:ia', $customer['created']) . "\n";
		$message .= "Subscription ID: " . $plan['id'] . "\n\n\n";

		$subject = $config['email-subject'];

		// Send it
			mail($email,$subject,$message,$headers);
			
			//Start receipt for administrator!
			
			// Build and send the email
			$email = $config['email-notify'];
		$headers = "From: ".$config['email-from-name']." <".$config['email-from'].">" . "\r\n\r\n";

		// Find and replace values
		$find = array('%name%', '%amount%');
		$replace = array($name, '$' . $amount);

		$message = str_replace($find, $replace , $config['email-notify-message']) . "\n\n";
		$message .= "Amount: $" . $amount . "\n";
		$message .= "Frequency: Every ".$intc." ".$int."(s)\n";
		$message .= "Address: " . $address . "\n";
		$message .= "Phone: " . $phone . "\n";
		$message .= "Email: " . $email . "\n";
		$message .= "Date: " . date('M j, Y, g:ia', $customer['created']) . "\n";
		$message .= "Subscription ID: " . $plan['id'] . "\n\n\n";

		$subject = $config['email-notify-subject'];

		// Send it
			mail($email,$subject,$message,$headers);
		

		// Forward to "Thank You" page
		header('Location: ' . $config['thank-you']);
		exit;

	
}
	}
	catch (Exception $e) {
		$error = $e->getMessage();
	}
}

?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html lang="en-us" class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>    <html lang="en-us" class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>    <html lang="en-us" class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en-us" class="no-js"> <!--<![endif]-->
	<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <? if (!$is_mobile) {?>
		<link rel="stylesheet" type="text/css" href="files/styles/style.css" media="all">
        <? }else{?>
        <link rel="stylesheet" type="text/css" href="files/styles/style_mobile.css" media="all">
        <? }?>
		<style>
		#conditional-row{
			margin-left:20px;
			padding-left:29px;
			background:url(images/arrow.png) no-repeat;
			background-position:6px 3px
			}
		.interval-count select[name=interval-count]{width:40px; font-size:12px;}
		.interval select[name=interval]{width:auto; height:auto;}
		#preloadedImages{width: 0px; height: 0px; display: inline; background-image: url(images/arrow.png);}
		</style>
		<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript">
			Stripe.setPublishableKey('<?=$config['publishable-key']?>');
			
			function showcustomfrequency(element){ 
    			document.getElementById("custom").style.display = element=="custom"?"block":"none"; 
			} 
		</script>
		<script type="text/javascript" src="files/js/stripe-script.js"></script>
        <script type="text/javascript" src="files/js/jquery.masked.js"></script>
		<script type="text/javascript">
		$(function($){
  			 $("#phone").mask("(999) 999-9999",{placeholder:" "});
		});
		</script>
	</head>
	<body>

		<div class="wrapper">

			<div class="messages">
				<!-- Error messages go here go here -->
			</div>

			<form action="#" method="POST" class="donation-form">
				<? if($is_mobile) {?>
                <div style="float:left;">
                <? }?>
                <fieldset>
					<legend>
						Contact Information
					</legend>
					<div class="form-row form-first-name">
						<label>First Name</label>
						<input type="text" name="first-name" class="first-name text">
					</div>
					<div class="form-row form-last-name">
						<label>Last Name</label>
						<input type="text" name="last-name" class="last-name text">
					</div>
					<div class="form-row form-email">
						<label>Email</label>
						<input type="email" name="email" class="email text">
					</div>
					<div class="form-row form-phone">
						<label>Phone</label>
						<input type="tel" id="phone" pattern="([0-9]{3}) [0-9]{3}-[0-9]{4}"  name="phone" class="phone text">
					</div>
					<div class="form-row form-address">
						<label>Address</label>
						<textarea name="address" cols="30" rows="2" class="address text"></textarea>
					</div>
					<div class="form-row form-city">
						<label>City</label>
						<input type="text" name="city" class="city text">
					</div>
					<div class="form-row form-state">
						<label>State</label>
						<select name="state" class="state text">
							<option value="AL">AL</option>
							<option value="AK">AK</option>
							<option value="AZ">AZ</option>
							<option value="AR">AR</option>
							<option value="CA">CA</option>
							<option value="CO">CO</option>
							<option value="CT">CT</option>
							<option value="DE">DE</option>
							<option value="DC">DC</option>
							<option value="FL">FL</option>
							<option value="GA">GA</option>
							<option value="HI">HI</option>
							<option value="ID">ID</option>
							<option value="IL">IL</option>
							<option value="IN">IN</option>
							<option value="IA">IA</option>
							<option value="KS">KS</option>
							<option value="KY">KY</option>
							<option value="LA">LA</option>
							<option value="ME">ME</option>
							<option value="MD">MD</option>
							<option value="MA">MA</option>
							<option value="MI">MI</option>
							<option value="MN">MN</option>
							<option value="MS">MS</option>
							<option value="MO">MO</option>
							<option value="MT">MT</option>
							<option value="NE">NE</option>
							<option value="NV">NV</option>
							<option value="NH">NH</option>
							<option value="NJ">NJ</option>
							<option value="NM">NM</option>
							<option value="NY">NY</option>
							<option value="NC">NC</option>
							<option value="ND">ND</option>
							<option value="OH">OH</option>
							<option value="OK">OK</option>
							<option value="OR">OR</option>
							<option value="PA">PA</option>
							<option value="RI">RI</option>
							<option value="SC">SC</option>
							<option value="SD">SD</option>
							<option value="TN">TN</option>
							<option value="TX">TX</option>
							<option value="UT">UT</option>
							<option value="VT">VT</option>
							<option value="VA">VA</option>
							<option value="WA">WA</option>
							<option value="WV">WV</option>
							<option value="WI">WI</option>
							<option value="WY">WY</option>
						</select>
					</div>
					<div class="form-row form-zip">
						<label>Zip</label>
						<input type="text" pattern="[0-9]*" name="zip" class="zip text">
					</div>
                    <div class="form-row form-otherinfo">
						<label>Special Instructions</label>
                        
						<textarea name="otherinfo" class="otherinfo text"></textarea>
					</div>
				</fieldset>
			<? if($is_mobile) {?>
                </div>
                <div style="clear:left; float:left;">
                <? }?>
				<fieldset>
					<legend>
						Your Generous Donation
					</legend>
					<div class="form-row form-amount">
						<label><input type="radio" name="amount" class="set-amount" value="25"> $25</label>
						<label><input type="radio" name="amount" class="set-amount" value="500"> $500</label>
						<label><input type="radio" name="amount" class="set-amount" value="2500"> $2,500</label>
						<label><input type="radio" name="amount" class="set-amount" value="100"> $100</label>
						<label><input type="radio" name="amount" class="set-amount" value="1000"> $1,000</label>
						<label><input type="radio" name="amount" class="set-amount" value="5000"> $5,000</label>
						<label><input type="radio" name="amount" class="other-amount" value="0"> Other:</label> <input type="text" pattern="[0-9]*" class="amount text" disabled>
					</div>
                    <div class="form-row form-frequency">
						<label>Frequency</label>
                        <select name="interval_with_count" class="frequency text" onchange="showcustomfrequency(this.value);">
  	  					<option value="onetime" selected="selected">One-time</option>
      					<option value="week">Weekly</option>
      					<option value="month">Monthly</option>
  	  					<option value="year">Yearly</option>
  	  					<option value="3-month">Every 3 months</option>
  	  					<option value="6-month">Every 6 months</option>
  	  					<option value="custom">Custom</option>
						</select>
						</div>
                        <div id="custom" style="display:none;">
                        <div id="conditional-row">
                        <label>every</label>
                        <select name="interval_count" class="interval-count">
  	  <option value="2" selected="selected">2</option>
  	  <option value="3">3</option>
  	  <option value="4">4</option>
  	  <option value="5">5</option>
  	  <option value="6">6</option>
  	  <option value="7">7</option>
  	  <option value="8">8</option>
  	  <option value="9">9</option>
  	  <option value="10">10</option>
  	  <option value="11">11</option>
  	  <option value="12">12</option>
</select>
<select name="interval" class="interval">
  	  <option value="month" selected="selected">months</option>
  	  <option value="week">weeks</option>
</select>
</div>
                        </div>
					
					<div class="form-row form-number">
						<label>Card Number</label>
						<input type="text" autocomplete="off" class="card-number text" value="4242424242424242" pattern="[0-9]*">
					</div>
					<div class="form-row form-cvc">
						<label>CVC</label>
						<input type="text" pattern="[0-9]*" autocomplete="off" value="1234" class="card-cvc text">
					</div>
					<div class="form-row form-expiry">
						<label>Expiration Date</label>
						<select class="card-expiry-month text">
							<option value="01">January</option>
							<option value="02">February</option>
							<option value="03">March</option>
							<option value="04">April</option>
							<option value="05">May</option>
							<option value="06">June</option>
							<option value="07">July</option>
							<option value="08">August</option>
							<option value="09">September</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
						</select>
						<select class="card-expiry-year text">
							<option value="2012">2012</option>
							<option value="2013">2013</option>
							<option value="2014" selected>2014</option>
							<option value="2015">2015</option>
							<option value="2016">2016</option>
							<option value="2017">2017</option>
							<option value="2018">2018</option>
							<option value="2019">2019</option>
							<option value="2020">2020</option>
						</select>
					</div>
					<div class="form-row form-submit">
						<input type="submit" class="submit-button" value="Submit Donation">
					</div>
				</fieldset>
                <? if($is_mobile) {?>
                </div>
                <? }?>
			</form>

      <script>if (window.Stripe) $(".donation-form").show()</script>
      <noscript><p>JavaScript is required for the donation form.</p></noscript>
		</div>

	</body>
</html>
