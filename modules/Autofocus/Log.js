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


