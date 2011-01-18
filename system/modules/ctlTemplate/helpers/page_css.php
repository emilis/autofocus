<?php

$n = explode('.', getObjectName($args[0], 'long'));

?><link rel="stylesheet" type="text/css" href="<?php echo FILES_URL . "/$n[0]/$n[1].css"; ?>">
