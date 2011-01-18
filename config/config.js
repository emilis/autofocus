/*
 * PolicyFeed configuration for website and crawler.
 */

var fs = require("fs");

var ROOT_DIR = fs.absolute(module.directory + "../");

exports.httpConfig = {
    staticDir: ROOT_DIR + '/static/'
};

exports.urls = [

    // Default mapping by request parameters. See: ctl/WebMapper.mapRequest().
    [ /.*/,             createRequestHandler("WebMapper", "mapRequest") ] 
];

/*/ Left over from RingoJS demoapp config.
exports.middleware = [
    require('ringo/middleware/gzip').middleware,
    require('ringo/middleware/etag').middleware,
    require('ringo/middleware/responselog').middleware,
    require('ringo/middleware/error').middleware,
    require('ringo/middleware/notfound').middleware
];
//*/

// the JSGI app
exports.app = require('ringo/webapp').handleRequest;

/*/ Left over from RingoJS demoapp config.
exports.macros = [
    require('./helpers'),
    require('ringo/skin/macros'),
    require('ringo/skin/filters')
];
//*/

exports.charset = 'UTF-8';
exports.contentType = 'text/html';


/**
 * Email where your visitors should send their problems:
 */
exports.supportEmail = "policyfeed@mailinator.com";

// --- Gluestick constants: ---

exports.DIRS = {
    root:       ROOT_DIR,
    files:      ROOT_DIR + "/static/files",
    uploads:    ROOT_DIR + "/static/uploads",
    config:     ROOT_DIR + "/config",
    data:       ROOT_DIR + "/data",
    lib:        ROOT_DIR + "/lib",
    packages:   ROOT_DIR + "/lib/packages",
    modules:    ROOT_DIR + "/modules"
};

var base_url = "";
exports.URLS = {
    base:       base_url,
    full:       "http://localhost:8080" + base_url,
    files:      base_url + "/static/files",
    uploads:    base_url + "/static/uploads"
};


// --- Gluestick interfaces: ---

exports.gluestick = {
    interfaces: {
        DB: {
            module: "ctl/DB/Sqlite",
            clone: true,
            config: { filename: exports.DIRS.data + "/default.sqlite3" }
        },
        Events: {
            module: "ctl/Events",
            config: {
                callbacks: [
                    [ /(debug|error|warning)/,  "ctl/Events/ShellWriter:printEvent" ]
                ]
            }
        },
        Site: "Autofocus/Site",
        WebMapper: {
            module: "ctl/WebMapper",
            config: {
                default_call: ["Site", "showIndex"],
                allowed: [
                    "Site",
                    "Autofocus",
                ]
            }
        }
    }
};


// --- Module config: ---

exports["ctl/objectfs/json"] = {
    file_dir: exports.DIRS.data + "/jsonfs"
};

//----------------------------------------------------------------------------


function createRequestHandler(mod_name, func_name) {
    var module = false;
    return function() {
        if (!module)
            module = require("gluestick").loadModule(mod_name);
        return module[func_name].apply(module, arguments);
    }
}
