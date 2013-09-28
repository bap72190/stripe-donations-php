<?php

//Credit Card Config Info
return $config = Array(

	// Enable test mode (not require HTTPS)
	'test-mode'  => true,

	// Secret Key from Stripe.com Dashboard
	'secret-key' => '',

	// Publishable Key from Stripe.com Dashboard
	'publishable-key' => '',

	// Where to send upon successful donation (must include http:// or https://)
	'thank-you'  => '',

	// Who the email will be from.
	'email-from' => '',
	'email-from-name' => '',

	// Who should a donation notification be sent to? Probably an administrative email.
	'email-notify'  => '',

	// Subject of email receipt
	'email-subject' => 'Thank you for your donation!',
	
	// Subject of administrator email receipt
	'email-notify-subject' => 'New CC Donation!',

	// Email message. %name% is the donor's name. %amount% is the donation amount
	'email-message' => "Dear %name%,\n\nThank you for your donation of %amount%. We rely on the financial support from people like you to keep our ministry alive. Below is your donation receipt to keep for your records.",
	
	// Email message for administrators. %name% is the donor's name. %amount% is the donation amount
	'email-notify-message' => "You have recevied a new donation! %name% has donated %amount%. Below is the full receipt for this donation."

);

?>