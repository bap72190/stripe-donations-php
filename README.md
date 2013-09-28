stripe-donations-php
====================

Stripe Integration with single and repeating donations


Edit the config file in files->config.php. Put your stripe keys, emails information, etc in here.

To include this on a page on your website, simply paste this on the page where you want the form to appear.

Include this at the top of the php page. Must be the very first thing included.

<?php
// Force https
if( $_SERVER["HTTPS"] != "on") {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
	exit();
}
session_start(); 


include ($_SERVER['DOCUMENT_ROOT']."/payments/Mobile_Detect.php");
$detect = new Mobile_Detect();
if ($detect->isMobile()) {
    $mobile = '?mobile=true';
	$mheight = '650';
	$mwidth = '250';
}else {
	$mobile='';
	$mheight = '590';
	$mwidth = '840';
}
?>

Include this where you want the form to actually appear.

<? $financial_form = 'stripe-give'; include('../payments/forms.php');?>
