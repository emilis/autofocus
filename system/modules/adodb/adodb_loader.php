<?php

/**
 * Loader function for the ADOdb Database Abstraction Library for PHP
 *
 * @function adodb_loader
 * @return ref object ADOdb connection object. 
 * @param string $obj_name Object instance name. 
 * @param array $obj_config Object config.  
 */ 
function &adodb_loader($obj_name, $obj_config)
{
    // 1. Try loading adodb library files:
    if (!function_exists('ADONewConnection'))
        require_once LIB_DIR . '/adodb/adodb.inc.php';
    
    // 3. Create connection object for the given database type:
    $conn = &ADONewConnection($obj_config['type']);
    
    // 4. Connect to database:
    if (@$obj_config['persistent'])
        $conn->PConnect($obj_config['host'],@$obj_config['user'],@$obj_config['password'],@$obj_config['database']);
    else
        $conn->Connect($obj_config['host'],@$obj_config['user'],@$obj_config['password'],@$obj_config['database']);
        
    // 5. More configuration:
    if (array_key_exists('debug', $obj_config))
        $conn->debug = $obj_config['debug'];
    if (array_key_exists('fetch_mode', $obj_config))
        $conn->SetFetchMode($obj_config['fetch_mode']);
 
    // 6. Default queries:
    if (array_key_exists('start_query', $obj_config))
        $conn->Execute($obj_config['start_query']);
    
    // 6. Return connection object:
    return $conn;
}


/**
 * Unloader function for ADOdb connection object.
 *
 * @function adodb_unloader
 * @return boolean TRUE. 
 * @param ref object ADOdb connection object.
 */  
function adodb_unloader($obj_name, &$obj)  
{
    // 1. Close database connection and return the result:
    $obj->Close();
    return TRUE;
}

?>
