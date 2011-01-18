<?php
/*
    Main Gluestick framework configuration. Defines constants pointing to commonly used files and directories.
 */

!defined('CONFIG_DIR')  ? define('CONFIG_DIR', dirname(__FILE__)) : null;

!defined('WEB_DIR')     ? define('WEB_DIR', $index_dirname): null;
!defined('FILES_DIR')   ? define('FILES_DIR', WEB_DIR . '/files'): null;
!defined('UPLOADS_DIR') ? define('UPLOADS_DIR', WEB_DIR . '/uploads'): null; 

// Tries guessing WEB_URL from $_SERVER variables. Should work correctly in most cases.
// Change with a predefined value for production usage:
!defined('WEB_URL')     ? define('WEB_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'])
): null;
!defined('FILES_URL')   ? define('FILES_URL', WEB_URL . '/files'): null;
!defined('UPLOADS_URL') ? define('UPLOADS_URL', WEB_URL . '/uploads'): null;

!defined('SYSTEM_DIR')  ? define('SYSTEM_DIR', dirname(CONFIG_DIR)): null;
!defined('DATA_DIR')    ? define('DATA_DIR', SYSTEM_DIR . '/data'): null;
!defined('LIB_DIR')     ? define('LIB_DIR', SYSTEM_DIR . '/lib'): null;
!defined('MODULES_DIR') ? define('MODULES_DIR', SYSTEM_DIR . '/modules'): null;
!defined('KERNEL_FILE') ? define('KERNEL_FILE', LIB_DIR . '/gluestick/kernel.php'): null;


