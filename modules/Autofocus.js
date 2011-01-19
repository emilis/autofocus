/*
    Copyright 2011 Emilis Dambauskas

    This file is part of Autofocus module.

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
var TaskLists = require("Autofocus/TaskLists");
var Tasks = require("Autofocus/Tasks");
var Log = require("Autofocus/Log");


// Extend module:
gluestick.extendModule(exports, "ctl/Controller");

/**
 * Directory with template files.
 */
exports.tpl_dir = exports.getTplDir(module);


var log = require("ringo/logging").getLogger(module.id);

/**
 *
 */
exports.showIndex = function(req) {
    return this.returnHtml("showIndex", {
            lists: TaskLists.list(),
            log: Log.list(undefined, { limit: 30, order: { time: -1 } })
            });
}


/**
 *
 */
exports.showList = function(req) {
    var id = req.params.id || 1;

    var list = TaskLists.read(id);
    if (!list) {
        return this.showError(404);
    }

    return this.returnHtml("showList", {
            list: list,
            lists: TaskLists.list(),
            tasks: Tasks.list({ list_id: id }, { order: { reordered: 1 }}),
            log: Log.list({ list_id: id}, { limit: 30, order: { time: -1 } })
            });
};


/**
 *
 */
exports.addList = function(req) {
    var tlist = {};

    tlist.title = req.params.title;

    var id = TaskLists.write(false, tlist);
    
    return this.WebMapper.redirect(module.id, "showList", { id: id });
}

