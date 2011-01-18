<?php

/**
 * Loader function for objects implementing Site standard module interface.
 *
 * @function Site_loader
 * @return ref object Site object. 
 * @param string $obj_name Object instance name. 
 * @param array $obj_config Object config.  
 */ 
function &Site_loader($obj_name, $obj_config)
{
    // Default Site SMI is implemented by...?
    if (!isset($obj_config['object']))
        $obj_config['object'] = 'gluestickWebDemo';

    $object = &loadObject($obj_config['object'], $obj_config);
    return $object;
}


/*
 * Site object unloader is not used.
 */
?>
