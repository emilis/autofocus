
var config = require("config");
var ctlTemplate = require("ctl/Template/Cached");
var ctlRequest = require("ctl/Request");
var fs = require("fs");
var WebMapper = require("ctl/WebMapper");


// These get used a lot:
exports.dirname = fs.directory(module.path) + "/Site";


var log = require("ringo/logging").getLogger(module.id);

/**
 * Home page of the website.
 */
exports.showIndex = function(req) {
    return require("Autofocus").showIndex();
};



/**
 * Main template for the website.
 */
exports.showContent = function(content) {
    if (typeof(content) == 'string')
        content = { html: content };

    content.title = content.title || "";

    return ctlTemplate.fetch(this.dirname + "/tpl/showContent.ejs", content);
}


/**
 * Error page for the website.
 */
exports.showError = function(msg) {
    if (typeof(msg) == 'undefined')
        msg = 404;

    if (typeof(msg) == "number")
        var status = msg;
    else
        var status = 501;

    return {
        status: status,
        headers: {},
        body: [ ctlTemplate.fetch(this.dirname + "/tpl/showError.ejs", { code: msg }) ]
    };
}


/**
 * Static pages for the website.
 */
exports.showPage = function(req, name) {
    log.info("showPage", name, ctlRequest.getRemoteAddr(req));

    var file_name = this.dirname + "/pages/" + name + ".ejs";

    if (!fs.exists(file_name))
        return this.showError(404);
    else
    {
        return WebMapper.returnHtml(
            this.showContent(
                ctlTemplate.fetchObject( file_name)));
    }
}


/**
 * HTML blocks for the website.
 */
exports.showBlock = function(name) {
    return ctlTemplate.fetch( this.dirname + "/blocks/" + name + ".ejs" );
}
