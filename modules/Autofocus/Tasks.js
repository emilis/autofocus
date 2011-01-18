// Requirements:
var gluestick = require("gluestick");
var serializable = require("ctl/objectfs/serializable");

// Extend DB table:
gluestick.extendModule(exports, "ctl/objectfs/dbtable");
serializable.upgradeExports(exports);

exports.connect("DB", "tasks");

/**
 *
 */
exports.serialize = function(data) {
    data.created = data.created || new Date();
    data.reordered = data.reordered || data.created;

    if (data.created instanceof Date) {
        data.created = data.created.getTime();
    }

    if (data.reordered instanceof Date) {
        data.reordered = data.reordered.getTime();
    }

    return data;
}


/**
 *
 */
exports.unserialize = function(data) {
    if (typeof(data.created) == "number") {
        data.created = new Date(data.created);
    }

    if (typeof(data.reordered) == "number") {
        data.reordered = new Date(data.reordered);
    }

    return data;
}



