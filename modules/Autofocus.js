
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

