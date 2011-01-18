<?php

require_once LIB_DIR . "/ctlCommonApps/ctlCommonSiteApp.class.php";

/**
 *
 */
class Autofocus extends ctlCommonSiteApp {

    /**
     *
     */
    function showIndex() {

        $lists = newObjectInstance("Autofocus.AutofocusListList");
        $lists->select();
        
        return $this->showContent("showIndex", array(
            "lists" => $lists
        ));
    }


    /**
     *
     */
    function addList() {

        $list = newObjectInstance("Autofocus.AutofocusList");
        $list->assign($_REQUEST);

        if (!$list->validate()) {
            return $this->redirect("showIndex", array(
                "error" => "Please fix the errors before submitting a new list.",
                "errors" => $list->getErrors(),
            ));
        } else if (!$list->save()) {
            return $this->redirect("showIndex", array(
                "error" => "Failed to save new list.",
            ));
        } else {
            return $this->redirect("showList", false, array("id" => $list->id));
        }
    }


    /**
     *
     */
    function showList() {
        $id = @$_REQUEST["id"];
        if (!$id) {
            return $this->showError(404);
        }

        $tasks = newObjectInstance("Autofocus.AutofocusTaskList");
        $tasks->select(array("list_id" => $id));

        return $this->showContent("showList", array(
            "tasks" => $tasks,
        ));
    }
}
