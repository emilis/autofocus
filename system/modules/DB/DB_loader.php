<?php

function DB_loader($obj_name, $obj_config)
{
    // Default DB SMI is implemented by ADOdb Lite:
    if (!isset($obj_config['object']))
        $obj_config['object'] = 'adodb_lite.adodb_lite';

    return loadObject($obj_config['object'], $obj_config);
}

?>
