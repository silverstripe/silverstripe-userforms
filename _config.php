<?php
// Ensure compatibility with PHP 7.2 ("object" is a reserved word),
// with SilverStripe 3.6 (using Object) and SilverStripe 3.7 (using SS_Object)
if (!class_exists('SS_Object')) class_alias('Object', 'SS_Object');

if (!defined('USERFORMS_DIR')) {
    define('USERFORMS_DIR', basename(__DIR__));
}

Deprecation::notification_version('3.0', 'userforms');
