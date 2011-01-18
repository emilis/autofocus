#!/usr/bin/php
<?php

/*
    Gluestick framework PHP Command Line interface.
    
    Copyright 2007,2009 Emilis Dambauskas

    This file is part of Gluestick framework.

    Gluestick framework is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Gluestick framework is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Gluestick framework.  If not, see <http://www.gnu.org/licenses/>.
*/

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
// gluestick-cli.php -h outputs the comment below:
/*man

NAME
    gluestick-cli.php - Gluestick PHP Command Line Interface

SYNOPSIS
    gluestick-cli.php [STARTUP OPTIONS] [ACTIONS] [CALL WITH PARAMS]
    
STARTUP OPTIONS

    -i index_dirname    Directory where website root is located (index.php / WEB_URL).
    
    -c config_file      Site config file.
    
    -s site_name        Site name
    
    -v                  Print version information and exit.
    
    -h                  Print help page and exit.

    
ACTIONS
    
    -a                  Start interactive console. All further arguments will be treated as standard input in the console (no call processing).
    
    -e snippet          Execute PHP code snippet (can be multiple).
    
    -f                  Include PHP file (can be multiple).

CALL

    A object method call is in a form: "module.object:method".
    
CALL PARAMS

    All following arguments will be passed as arguments to the method call.
    
AUTHOR
    
    Emilis Dambauskas <emilis.d@gmail.com>
    
LICENSE
    
    This is FREE software. It is available under GNU General Public License v3 or later from the author(s).

man*/


// --- GET CTLF USER CONFIG: --- 

// try reading /home/user/.ctlf/sites.ini:
$user_config = $_ENV['HOME'] . '/.gluestick/sites.ini';
if (file_exists($user_config) && is_readable($user_config))
{
    $user_config = parse_ini_file($user_config, TRUE);
}

var_dump($user_config);

//  default site if not specified:
if (!is_array($user_config))
{
    $user_config = array();
    $user_config['default'] = array(
        'index_dirname' => '/var/www/gluestick',
        'config.inc.php' => '/var/www/gluestick/system/config/config.inc.php',
        );
}

$index_dirname = $user_config['default']['index_dirname'];
$config_inc = $user_config['default']['config.inc.php'];


// --- FIX $argv: ---

// remove script name from $argv:
array_shift($argv);

// no options -- help:
if ($argc === 1)
    $argv[] = '-h';


// --- PROCESS STARTUP OPTIONS: ---

while ($opt = array_shift($argv))
{
    //var_dump($opt);
    switch ($opt)
    {
        case '-v':
            echo basename(__FILE__) . ' version ' . date('Ymd', filectime(__FILE__));
            echo "\n";
            exit();
            break;
            
        case '-h':
            $contents = file_get_contents(__FILE__);
            $pos1 = strpos($contents, '/*man');
            $pos2 = strpos($contents, 'man*/');
            echo substr($contents, $pos1 + 6, $pos2 - $pos1 - 7);
            
            echo "\n";
            exit();
            break;
        
        case '-i':
            $index_dirname = array_shift($argv);
            break;
        
        case '-c':
            $config_inc =  array_shift($argv);
            break;
        
        case '-s':
            $site = array_shift($argv);
            if (!isset($user_config[$site]))
                die("Site '$site' does not exist in user config.");
            else
            {
                $index_dirname = $user_config[$site]['index_dirname'];
                $config_inc = $user_config[$site]['config.inc.php'];
            }
            break;
        
        // No startup options present. Fix $argv and exit loop:
        default:
            array_unshift($argv, $opt);
            break 2; // exit while loop
    }
    
}


// --- LOAD CTLF: ---

// include site config:
include $config_inc;
// load engine:
include KERNEL_FILE;


// --- ACTIONS + CALL: ---

while ($opt = array_shift($argv))
{
    switch (@$opt)
    {
        // INCLUDE FILE:
        case '-f':
            include array_shift($argv);
            break;
        
        // EVAL SNIPPET:
        case '-e':
            eval(array_shift($argv));
            break;
        
        // CONSOLE:
        case '-a':
            echo "\n-- PHP console with Gluestick --\n\n";
            $console = &loadObject('ctlConsole');
            
            // process command line arguments as code inside console:
            while ($arg = array_shift($argv))
            {
                echo "\nphp> $arg\n";
                
                $input = $console->processInput($arg);
                
                if (!$input)
                    continue;
                else if (false === eval($input))
                    $console->debug($input);
            }
            
            // process user input in console:
            $console->open();
            while (!$console->eof())
            {
                echo "\nphp> ";
                
                $input = $console->processInput($console->read());
                
                if (!$input)
                    continue;
                else if (false === eval($input))
                    $console->debug($input);
            }
            $console->close();
            
            // exit:
            exit();
            break;
        
        
        // --- CALL: ---
        default:
            list($obj_name, $method_name) = explode(':', $opt);
            $obj = &loadObject($obj_name);
            
            $result = call_user_func_array(array($obj, $method_name), $argv);
            if (is_string($result))
                echo $result;
            else
                echo json_encode($result);
            echo "\n";
            exit();
    }
}

?>
