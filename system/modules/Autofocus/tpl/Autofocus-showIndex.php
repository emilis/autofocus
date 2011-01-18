<?php

echo '<ul id="lists">';

foreach ($lists as $list) {
    echo '<li><a href="' . WEB_URL . '/?call=Autofocus:showList&id=' . $list->id . '">' . $list->title . '</a></li>';
}

echo '</ul>';

?>
<form method="post">
<input type="hidden" name="call" value="Autofocus:addList">
<input name="title">
<input type="submit" value="+ Add list">
</form>
