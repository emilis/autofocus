<?php

require_once LIB_DIR . "/ctlDataObjects/ctlValidatableDbRow.class.php";

class AutofocusList extends ctlValidatableDbRow {

    var $tableName = "lists";

    function validate_title() {
        if (!$this->title) {
            return "Please enter title for the task list.";
        }
    }

}
