<?php

require_once LIB_DIR . "/ctlCommonApps/ctlCommonSiteApp.class.php";

class Autofocus extends ctlCommonSiteApp {
    function showIndex() {
        return $this->showContent("showIndex");
    }
}
