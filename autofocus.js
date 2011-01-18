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


var autofocus = (function () {

    var api = {};

    var action_td = '<td class="actions"><a class="done">D</a><a class="later">L</a><a class="remove">R</a></td>';


    api.getTaskTable = function () { return document.getElementById("tasks"); };

    api.add = function() {
        api.write(false, {
            title: jQuery("#task-title").val()
            });
        jQuery("#task-title").val("");
        jQuery("#task-title").focus();
    };

    api.write = function(id, obj) {

        id = id || 1;

        jQuery("#task-table > tbody").append('<tr id="task-' + id + '"><td>' + obj.title + '</td>' + action_td + '</tr>');
    };

    return api;
})();
