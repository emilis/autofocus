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
var serializable = require("ctl/objectfs/serializable");

// Extend DB table:
gluestick.extendModule(exports, "ctl/objectfs/dbtable");
serializable.upgradeExports(exports);

exports.connect("DB", "log");

/**
 *
 */
exports.serialize = function(data) {
    data.time = data.time || new Date();

    if (data.time instanceof Date) {
        data.time = data.time.getTime();
    }

    return data;
}


/**
 *
 */
exports.unserialize = function(data) {
    if (typeof(data.time) == "number") {
        data.time = new Date(data.time);
    }

    return data;
}


