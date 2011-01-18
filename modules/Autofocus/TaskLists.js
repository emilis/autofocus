// Requirements:
var gluestick = require("gluestick");

// Extend DB table:
gluestick.extendModule(exports, "ctl/objectfs/dbtable");
exports.connect("DB", "task_lists");


