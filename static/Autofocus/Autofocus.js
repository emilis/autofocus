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


var autofocus = (function () {

    var api = {};

    var action_td = '<td class="actions"><a class="done">D</a><a class="later">L</a><a class="remove">R</a></td>';

    var stringToArray = function(str) {
	    var arr = str.trim().replace(/[- T:.Z]/g, "-").split("-").map(function (item) { return parseInt(item, 10); });
	    
	    for (var i=0;i<7;i++) {
		if (!arr[i])
		    arr[i] = 0;
	    }

	    return arr;
	}

    var fromUTCString = function(str) {

	    if (str instanceof Date) {
		return str;
	    } else {
		var arr = stringToArray(str);
		arr[1]--; // prepare month value
		return new Date(Date.UTC(arr[0], arr[1], arr[2], arr[3], arr[4], arr[5], arr[6]));
	    }
	}



    api.getTaskTable = function () { return document.getElementById("tasks"); };

    api.add = function() {
        api.write(false, {
            list_id: jQuery("#list-id").val(),
            title: jQuery("#task-title").val()
            });
        jQuery("#task-title").val("");
        jQuery("#task-title").focus();
    };

    api.write = function(id, obj) {

        jQuery.post("/", {
                call: "Autofocus/Api.tasks_write",
                id: id,
                data: obj
            }, function (data) {
                jQuery("#task-table > tbody").append('<tr id="task-' + data + '"><td>' + obj.title + '</td>' + action_td + '</tr>');
                api.checkLog();
            }, "json");
    };

    api.log = function(form) {

    	jQuery.post("/", {
		call: "Autofocus/Api.log_write",
		list_id: form.elements["list-id"].value,
		details: form.elements["status-details"].value
	    }, function(data) {
	    	form.elements["status-details"].value="";
	        api.checkLog();
	    }, "json");
    };

    api.checkLog = function() {
        jQuery.post("/", {
                call: "Autofocus/Api.log_list",
                filter: {
                    list_id: jQuery("#list-id").val()
                },
                options: {
                    order: { time: -1 }
                },
            }, function (data) {
                var html = "";
                for each (var entry in data) {
		    entry.time = fromUTCString(entry.time);
                    html += '<tr>';
                    html += '<td class="time">' + entry.time.getHours() + ":" + entry.time.getMinutes() + '</td>';
                    html += '<td class="' + entry.action + '">' + entry.action + '</td>';
                    html += '<td>' + entry.details + '</td>';
                    html += '</tr>';
                }

                jQuery("#log-table > tbody").html(html);
            }, "json");
    };

    api.init = function() {
        // Map actions to task buttons:
        ["done", "later", "remove"].map(function (action) {
            jQuery("a." + action).live("click", function () {
                var id = this.parentNode.parentNode.id.split("-").pop();
                jQuery.post("/", {
                        call: "Autofocus/Api.tasks_action",
                        id: id,
                        action: action
                    }, function(data) {
                        id = "#task-" + id;
                        if (action != "later") {
                            jQuery(id).remove();
                        } else {
                            var html = '<tr id="' + id.substr(1) + '">' + jQuery(id).html() + '</tr>';
                            jQuery(id).remove();
                            jQuery("#task-table > tbody").append(html);
                        }
                        api.checkLog();
                    });
                });
            });
        }

    return api;
})();


jQuery(document).ready(autofocus.init);
