<?php
/*
    Start-up file for Gluestick framework.
 */
error_reporting(E_ALL);

$index_dirname = dirname(__FILE__);

include dirname(__FILE__) . '/system/config/config.inc.php';

include_once KERNEL_FILE;

echo loadObject('Autofocus.AutofocusInstaller')->install();

