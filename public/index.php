<?php

/**
 * WACM - WhatsApp Assistant & Contact Manager
 * Front Controller Entry Point
 */

define('WACM_START', microtime(true));

// Base directory path
$basePath = dirname(__DIR__);

// Load Autoloader
require_once $basePath . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Autoloader::addNamespace('App', $basePath . '/app');

// Load Global Helpers
require_once $basePath . '/app/Helpers/functions.php';

// Bootstrap and Run the Application
\App\Core\App::bootstrap($basePath);
\App\Core\App::run();
