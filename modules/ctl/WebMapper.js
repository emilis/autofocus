/*
    Copyright 2009,2010 Emilis Dambauskas

    This file is part of Cheap Tricks Library for RingoJS.

    Cheap Tricks Library is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Cheap Tricks Library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Cheap Tricks Library.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @fileoverview Maps HTTP request parameters to appropriate module functions.
 */

// Requirements:
var config = require("config");
var gluestick = require("gluestick");
var ringo_arrays = require("ringo/utils/arrays");
var urlencode = java.net.URLEncoder.encode;

// Internal vars:
var log = require("ringo/logging").getLogger(module.id);

var default_status = 200;
var default_headers = {
    "Content-Type": "text/html; charset=UTF-8"
};


/**
 * Configures the module.
 */
exports._constructor = function(config) {
    module.config = config || {
        'default_call': ['Site', 'showIndex'],
        'allowed': [
            'Site'
            ] 
    };
}


/**
 * Default action from config.
 */
exports.index = function(req) {
    if (module.config) {
        return this.mapRequest(req);
    } else {
        return gluestick.loadModule("WebMapper").mapRequest(req);
    }
}


/**
 * Decides which module function to call, based on request parameters.
 * Wraps the result in a response object.
 */
exports.mapRequest = function(req) {

    var p = req.params || {};
    var mod_name = "";
    var action = "";

    // Find out which module and function to call:
    if (typeof(p.call) == 'string' && p.call.indexOf(".") > 0) {
        var call = p.call.split(".");
        action = call.pop();
        mod_name = call.join(".");
    } else if (typeof(p.module) == 'string' && typeof(p.action) == 'string') {
        [mod_name, action] = [p.module, p.action];
    } else {
        [mod_name, action] = module.config.default_call;
    }

    // Check if web clients are allowed to call this function:
    if (!isCallAllowed(mod_name, action)) {
        log.warn("mapRequest", "404", req.method, req.path, uneval(req.params));
        var result = gluestick.loadModule("Site").showError(404);
    } else {
        // Get result from module function:
        var result = gluestick.loadModule(mod_name)[action](req);
    }

    // Return result:
    if (typeof(result) != "string") {
        return this.returnResponse(result);
    } else {
        return this.returnResponse({
            "status":   default_status,
            "headers":  default_headers,
            "body":     [ result ]
        });
    }
}


/**
 * Returns response for HTML data.
 * @param {String} html
 * @returns {Object}
 */
exports.returnHtml = function(html) {
    return {
        "status":   default_status,
        "headers":  default_headers,
        "body":     [ html ]
    };
}


/**
 * Returns response for Json data.
 * @param {String|Object} data
 * @returns {Object}
 */
exports.returnJson = function(json) {

    if (typeof(json) != "string") {
        json = JSON.stringify(json);
    }

    return {
        status: 200,
        headers: { "Content-Type": "application/x-javascript; charset=utf-8" },
        body: [json]
    };
}


/**
 * Adds missing fields to response and returns it.
 */
exports.returnResponse = function(response) {

    response.status = response.status || default_status;

    if (!response.headers) {
        response.headers = default_headers;
    } else {
        for (var key in default_headers) {
            if (!response.headers[key])
                response.headers[key] = default_headers[key];
        }
    }

    return response;
}


/**
 * Checks if the module function call is allowed by WebMapper configuration and module variable "web_actions".
 */
function isCallAllowed(obj_name, action) {

    if (!ringo_arrays.contains(module.config.allowed, obj_name)) {
        return false;
    } else {
        var obj = gluestick.loadModule(obj_name);
        if (typeof(obj.web_actions) == "object" && (obj.web_actions instanceof Array)) {
            return ringo_arrays.contains(obj.web_actions, action);
        } else {
            return true;
        }
    }
}


/**
 *
 */
exports.getUrl = function(module_name, method, params, full) {
    if (full) {
        var url = config.URLS.full;
    } else {
        var url = config.URLS.base;
    }
    url += "/?call=" + module_name + "." + method;

    if (params) {
        for (var key in params) {
            url += "&" + urlencode(key) + "=";
            if (params[key] && typeof(params[key] == "string")) {
                url += urlencode(params[key]);
            }
        }
    }

    return url;
}


/**
 *  Returns a response object with redirect status and header.
 */
exports.redirect = function(module_name, method, params) {

    return this.redirectToUrl(
            this.getUrl( module_name, method, params ));
}


/**
 * Returns a response object with redirect status and header.
 */
exports.redirectToUrl = function(url) {
    log.debug("redirectToUrl", url);

    return {
        "status":   301,
        "headers":  { Location: url },
        "body":     []
    };
}

