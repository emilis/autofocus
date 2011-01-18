
var gluestick = require("gluestick");
var TaskLists = require("Autofocus/TaskLists");

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
            lists: TaskLists.list()
            });
}


/**
 *
 */
exports.showList = function(req) {
    var id = req.params.id || 1;

    var list = TaskLists.read(id);

    return this.returnHtml("showList", {
            list: list
            });
