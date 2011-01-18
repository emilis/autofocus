<?php

require_once LIB_DIR . "/ctlSmi/ctlSite.class.php";

class AutofocusSite extends ctlSite {

    function showIndex() {
        return $this->showContent( file_get_contents(WEB_DIR . '/list.html') );
    }
}
