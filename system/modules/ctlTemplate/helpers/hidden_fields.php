<?php

foreach ($args[0] as $key => $value)
{
    $id = str_replace(array('[',']'), '_', $key);
    echo '<input type="hidden" id="'.$id.'" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
}

?>