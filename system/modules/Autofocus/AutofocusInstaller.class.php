<?php

class AutofocusInstaller {

    function install() {
        $db = &loadObject("DB");

        $query = <<<'EOT'
CREATE TABLE "lists" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "title" TEXT
)

CREATE TABLE "tasks" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "list_id" INTEGER NOT NULL,
    "title" TEXT NOT NULL,
    "added" INTEGER NOT NULL,
    "reordered" INTEGER NOT NULL
);

CREATE TABLE "log" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "list_id" INTEGER NOT NULL,
    "time" INTEGER NOT NULL,
    "action" TEXT NOT NULL,
    "details" TEXT
);
EOT;

        echo '<h1>Running install script</h1>';

        $db->debug = true;
        $db->query($query);

        echo '<p><strong>Now remove install.php script.</strong></p>';
    }
}
