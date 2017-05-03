<?php

require_once(__DIR__ . '/BaseListener.php');

// Shim to load appropriate adapter for the version of PHPUnit that's in use
if (\class_exists('\PHPUnit\Framework\TestListener')) {
    require_once(__DIR__ . '/Adapter/Current.php');
} else {
    require_once(__DIR__ . '/Adapter/Pre6.php');
}
