<?php

function &SimpleCache_loader($obj_name, $obj_config)
{
    if (!isset($obj_config['object']))
        $obj_config['object'] = 'ctlSmi.ctlSimpleCache';

    $object = &loadObject($obj_config['object']);

    return $object;
}

?>
