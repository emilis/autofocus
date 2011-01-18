

exports.showIndex = function(req) {
    return {
        status: 200,
        headers: {},
        body: [ this.getDesign() ]
    };
};

exports.getDesign = function() {
    var fs = require("fs");
    var config = require("config");
    return fs.read(config.DIRS.root + "/list.html");
}
