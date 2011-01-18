<?php

/**
 * Loader function for the Session objects.
 *
 * @function Session_loader
 * @return ref object Module object.
 * @param string $obj_name Object instance name.
 * @param array $obj_config Module config.
 */
function &Session_loader($obj_name, $obj_config)
{
    if (!isset($obj_config['object']))
        $obj_config['object'] = 'ctlSmi.ctlSession';
    $object = loadObject($obj_config['object'] ,$obj_config);
    
    // Start session:
    $object->start();
    
    return $object;
}


/**
 * Unloader function for Session interface object.
 *
 * @function Session_unloader
 * @return boolean Null. 
 * @param ref object Session interface object.
 */  
function Session_unloader($obj_name, &$object)  
{
    // 1. Write session data and close session:
    return $object->close();
}

?>
