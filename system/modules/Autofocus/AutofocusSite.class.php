<?php

require_once LIB_DIR . "/ctlSmi/ctlSite.class.php";

class AutofocusSite extends ctlSite {

    function showIndex() {
        return loadObject("Autofocus")->showIndex();
    }
}
