<?php
defined('ABSPATH') || exit;

require_once __DIR__ . '/authentication/class-otp.php';
require_once __DIR__ . '/authentication/class-otp-security.php';
require_once __DIR__ . '/authentication/class-mobile-login.php';

new Beautia_Mobile_Login();
