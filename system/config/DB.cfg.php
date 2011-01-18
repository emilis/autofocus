<?php

// DB config:
/*
$modconfig = array(
    'object' => 'adodb',
    'type' => 'mysqli',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'database' => 'kitwiki',
    'persistent' => 1,
    'fetch_mode' => 2, // ADODB_FETCH_ASSOC
    'start_query' => 'set names utf8',
    );
 */

$modconfig = array(
    'object' => 'adodb',
    'type' => 'sqlitepo',
    'host' => DATA_DIR . '/tasks.sqlite',
    'persistent' => 0, // using persistent=1 failed to create DB file for me (Emilis)
    'fetch_mode' => 2, // ADODB_FETCH_ASSOC
);

if ($_SERVER['REQUEST_METHOD'] == 'GET')
    $modconfig['debug'] = 0;

?>
