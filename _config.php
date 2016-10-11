<?php

use SilverStripe\Dev\Deprecation;

if (!defined('USERFORMS_DIR')) {
    define('USERFORMS_DIR', basename(__DIR__));
}

Deprecation::notification_version('3.0', 'userforms');
