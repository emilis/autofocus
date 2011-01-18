<?php

require_once LIB_DIR . "/ctlCommonApps/ctlCommonSiteApp.class.php";

class Autofocus extends ctlCommonSiteApp {
    function showIndex() {

        $lists = newObjectInstance("Autofocus.AutofocusListList");
        $lists->select();

        return $this->showContent("showIndex", array(
            "lists" => $lists
        ));
    }
}
