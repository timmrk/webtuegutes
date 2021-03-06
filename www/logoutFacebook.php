<?php
//Include FB config file
require './includes/DEF.php';
require './includes/fb/fbConfig.php';

//Remove App permissions
$fbUid = $_COOKIE['fb_id'];
$facebook->api('/'.$fbUid.'/permissions','DELETE');

//Unset user data from session
unset($_SESSION['userData']);

//Destroy session data
$facebook->destroySession();

// facebook cookies weg
unset($_COOKIE['fb_id']);
unset($_COOKIE['fb_email']);
unset($_COOKIE['fb_first_name']);
unset($_COOKIE['fb_last_name']);
unset($_COOKIE['fb_gender']);
unset($_COOKIE['fb_picture']);
unset($_COOKIE['fb_link']);

// unsere Cookies weg
unset($_COOKIE['fb_iduser']);
unset($_COOKIE['fb_username']);
unset($_COOKIE['fb_privacykey']);

$_USER->logout();
?>