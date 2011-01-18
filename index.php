<?php
/*
    Start-up file for Gluestick framework.
 */
error_reporting(E_ALL);

$index_dirname = dirname(__FILE__);

// Go to install.php if it still exists.
if (file_exists("$index_dirname/install.php"))
    header('Location: install.php');

include dirname(__FILE__) . '/system/config/config.inc.php';

include_once KERNEL_FILE;

echo loadObject('WebMapper')->mapRequest();

