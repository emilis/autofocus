<?php

if (strpos(':', $args[0]))
    list($object, $action) = explode(':', $args[0]);
else
{
    $object = $args[0];
    $action = $args[1];
}

echo '<form method="POST" action="' . WEB_URL . '/"';
if ($args[2])
    echo ' name="'.$args[2].'" id="'.$args[2].'" ';
echo ' enctype="multipart/form-data">'; // always multipart, to prevent confusion with uploads
echo '<input type="hidden" name="object" value="' . $object . '">';
echo '<input type="hidden" name="action" value="' . $action . '">';

if (isset($args[3]) && is_array($args[3]))
    echo $this->show('hidden_fields', $args[3]);

?>
