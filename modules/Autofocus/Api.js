/*
    Copyright 2011 Emilis Dambauskas

    This file is part of Autofocus application.

    Autofocus is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Autofocus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with Autofocus.  If not, see <http://www.gnu.org/licenses/>.
*/

// Requirements:
var gluestick = require("gluestick");
var Tasks = require("Autofocus/Tasks");
var Log = require("Autofocus/Log");

// Extend module:
gluestick.extendModule(exports, "ctl/Controller");


/**
 *
 */
exports.tasks_list = function(req) {
    return this.WebMapper.returnJson(
            Tasks.list(req.params.filter, req.params.options));
}


/**
 *
 */
exports.tasks_read = function(req) {
    return this.WebMapper.returnJson(
            Tasks.read(req.params.id));
}


/**
 *
 */
exports.tasks_write = function(req) {

    var id = req.params.id || false;
    if (id == "false") {
        id = false;
    }

    var action = id ? "edited" : "added";

    var data = req.params.data;

    var result = Tasks.write(id, data);
    if (result) {
        Log.write(false, {
            list_id: data.list_id,
            action: action,
            details: data.title
            });
    }

    return this.WebMapper.returnJson(result);
}


/**
 *
 */
exports.tasks_remove = function(req) {
    return this.WebMapper.returnJson(
            Tasks.remove(req.params.id));
}


/**
 *
 */
exports.tasks_action = function(req) {
    var id = req.params.id;
    var action = req.params.action;

    if (Tasks.exists(id)) {
        var task = Tasks.read(id);
        var entry = {
            list_id: task.list_id,
            details: task.title,
            action: action
        };

        switch (action) {
            case "done":
                Tasks.remove(id);
                Log.write(false, entry);
                break;
            case "remove":
                Tasks.remove(id);
                entry.action = "removed";
                Log.write(false, entry);
                break;
            case "later":
                task.reordered = new Date();
                Tasks.write(id, task);
                entry.action = "delayed";
                Log.write(false, entry);
                break;
        }

        return this.WebMapper.returnJson(true);
    } else {
        return this.showError(404);
    }
}


/**
 *
 */
exports.log_list = function(req) {
    return this.WebMapper.returnJson(
            Log.list(req.params.filter, req.params.options));
}


/**
 *
 */
exports.log_write = function(req) {
    var entry = {
        list_id: req.params.list_id,
	action: "status",
	details: req.params.details
    };

    Log.write(false, entry);
    return this.WebMapper.returnJson(true);
}



