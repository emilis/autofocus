<?php

function &WebMapper_loader($obj_name, $obj_config)
{
    if (!isset($obj_config['object']))
        $obj_config['object'] = 'ctlSmi.ctlWebMapper';

    $object = &loadObject($obj_config['object'], $obj_config);
    return $object;
}

?>
