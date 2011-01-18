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
 * Initialization: registers mysql driver with java.sql.DriverManager.
 */
if (!registered) {
    // Register MySQL driver:
    var mysql_driver = new com.mysql.jdbc.Driver() 
    var driver_manager = java.sql.DriverManager
    driver_manager.registerDriver(mysql_driver)

    // Get data types:
    var Types = java.sql.Types;

    // Save registered state:
    var registered = true;
}

var log = require("ringo/logging").getLogger(module.id);


exports.config = {};
exports.last_connection = false;

//----------------------------------------------------------------------------

/**
 * Constructor. Connects to a database specified by configuration.
 *
 * @param {Object} config DB configuration options (host, db_name, user, password, [useUnicode, characterEncoding, start_query]).
 */
exports._constructor = function(config) {
    log.info("_constructor()", uneval(config));

    this.config = config;
    this.last_connection = false;

    this.connect();
}


/**
 * Connects to a MySQL database.
 *
 * @param {String} host optional DB host.
 * @param {String} db_name optional DB name.
 * @param {String} user optional DB user.
 * @param {String} password optional DB user password.
 * @returns {java.sql.Connection}
 */
exports.connect = function($host, $db_name, $user, $password) {

    if (!$host || $host == undefined) {
        if (this.last_connection && this.last_connection.isValid(0)) {
            return this.last_connection;
        }
        
        $host = this.config.host;
        $db_name = this.config.db_name;
        $user = this.config.user;
        $password = this.config.password;
    }

    // get connection:
    var url = "jdbc:mysql://" + $host + "/" + $db_name + "?user=" + $user + "&password=" + $password;
    url += "&autoReconnect=true";

    if (this.config.useUnicode)
        url += "&useUnicode=" + this.config.useUnicode;
    if (this.config.characterEncoding)
        url += "&characterEncoding=" + this.config.characterEncoding;


    this.last_connection = driver_manager.getConnection(url);
    log.info("connect()", this.last_connection, url);

    if (this.config.start_query != undefined) {
        var stmt = this.last_connection.createStatement();
        stmt.execute(this.config.start_query);
        stmt.close();
    }

    return this.last_connection;
}


/**
 * Closes MySQL connection.
 *
 * @param {java.sql.Connection} optional Connection to close. Uses default connection if empty.
 * @returns {Boolean}
 */
exports.close = function(conn) {
    log.info(conn, "close()");

    conn = this.getConnection(conn);
    return conn.close();
}


/**
 * Returns either provided connection or this.last_connection. Reconnects this.last_connection if needed.
 *
 * @param {java.sql.Connection} optional Returns given Connection object or default connection.
 * @returns {java.sql.Connection}
 */
exports.getConnection = function(conn) {
    if (conn == undefined)     {
        conn = this.last_connection;
        if (!conn.isValid(0) || conn.isClosed())
            conn = this.connect();
    }
    return conn;
}



/**
 * Queries MySQL database and returns a resultset.
 *
 * @param {String|java.sql.ResultSet} sql MySQL query string.
 * @param {java.sql.Connection} optional Connection to use.
 * @returns {java.sql.ResultSet|Number|Boolean} ResultSet or updated row count or false.
 */
exports.query = function(sql, conn) {
    conn = this.getConnection(conn);
    log.debug(conn, "query()", sql);

    var stmt = conn.createStatement();

    if (stmt.execute(sql, java.sql.Statement.RETURN_GENERATED_KEYS)) {
        var rs = stmt.getResultSet();
        return rs;
    } else {
        var result = stmt.getUpdateCount();
        if (result > -1) {
            var gc = stmt.getGeneratedKeys();
            if (gc) {
                result = this.get_col(gc);
            }
        } else {
            result = false;
        }

        stmt.close();
        return result;
    }
}


/**
 * Queries MySQL database using a PreparedStatement interface.
 *
 * @param {String|java.sql.ResultSet} sql MySQL query string.
 * @param {Array} Parameter array.
 * @param {java.sql.Connection} optional Connection to use.
 * @returns {java.sql.ResultSet|Number|Boolean} ResultSet or updated row count or false.
 */
exports.prepared_query = function(sql, params, conn) {
    conn = this.getConnection(conn);
    log.debug(conn, "prepared_query()", sql, params.length);

    var pStmt = conn.prepareStatement(sql, java.sql.Statement.RETURN_GENERATED_KEYS);

    for (var i=0; i<params.length; i++) {
        if (params[i] === null || params[i] === false) {
            pStmt.setNull(i+1, java.sql.Types.VARCHAR);
        } else {
            pStmt.setString(i+1, params[i]);
        }
    }

    if (pStmt.execute()) {
        return pStmt.getResultSet();
    } else {
        var result = pStmt.getUpdateCount();
        if (result > -1) {
            var gc = pStmt.getGeneratedKeys();
            if (gc) {
                result = this.get_col(gc);
            }
        } else {
            result = false;
        }

        pStmt.close();
        return result;
    }
}


/**
 * Returns active row as an object from a resultset.
 *
 * @param {String|java.sql.ResultSet} query Query string or ResultSet.
 * @returns {Object|Boolean} Result or false on failure.
 */
exports.get_row = function(rs) {
    
    if (typeof(rs) == "string")
        rs = this.query(rs);
    if (!rs || !rs.first())
        return false;

    var row = {};
    var rs_meta = rs.getMetaData();
    var column_count = rs_meta.getColumnCount();

    for (var i=1;i<=column_count; i++) {
        row[rs_meta.getColumnLabel(i)] = this.get_column_value(rs, i, rs_meta);
    }

    return row;
}


/**
 * Returns an array of all rows in a resultset. Rows are represented as objects with field values.
 *
 * @param {String|java.sql.ResultSet} query Query string or ResultSet.
 * @returns {Array|Boolean} Result or false on failure.
 */
exports.get_all = function(rs) {

    if (typeof(rs) == "string")
        rs = this.query(rs);
    if (!rs)
        return false;
    if (!rs.first())
        return [];

    var all = [];

    var rs_meta = rs.getMetaData();
    var column_count = rs_meta.getColumnCount();
    
    var column_names = [];
    for (var ci=1; ci<=column_count; ci++)
        column_names[ci] = rs_meta.getColumnLabel(ci);

    rs.beforeFirst();
    while (rs.next()) {
        var row = {};
        for (var ci=1; ci<=column_count; ci++)
            row[column_names[ci]] = this.get_column_value(rs, ci, rs_meta);
        all.push(row);
    }

    rs.getStatement().close();

    return all;
}


/**
 * Returns an iterator over all rows in a resultset.
 *
 * @param {String|java.sql.ResultSet} query Query string or ResultSet.
 * @returns {Iterator}
 */
exports.get_iterator = function(rs) {

    if (typeof(rs) == "string")
        rs = this.query(rs);
    if (!rs || !rs.first())
        throw StopIteration;

    try {
        var rs_meta = rs.getMetaData();
        var column_count = rs_meta.getColumnCount();
        
        var column_names = [];
        for (var ci=1; ci<=column_count; ci++)
            column_names[ci] = rs_meta.getColumnLabel(ci);

        rs.beforeFirst();
        while (rs.next()) {
            var row = {};
            for (var ci=1; ci<=column_count; ci++) {
                row[column_names[ci]] = this.get_column_value(rs, ci, rs_meta);
            }
            yield row;
        }
    } finally {
       rs.getStatement().close();
    }
}



/**
 * Returns an array of all values in one resultset column.
 * 
 * @param {String|java.sql.ResultSet} query Query string or ResultSet.
 * @param {Number} column optional Column number (starts from 1).
 * @returns {Array}
 */
exports.get_col = function(rs, ci) {

    if (typeof(rs) == "string")
        rs = this.query(rs);
    if (!rs)
        return false;
    if (!rs.first())
        return [];

    if (ci === undefined)
        ci = 1;

    var all = [];
    var rs_meta = rs.getMetaData();

    if (ci > rs_meta.getColumnCount())
        return [];
    
    rs.beforeFirst();
    while (rs.next()) {
        all.push( this.get_column_value(rs, ci) );
    }

    rs.getStatement().close();

    return all;
}


/**
 * Returns the value of the first column in the first row of the given ResultSet.
 *
 * @param {String|java.sql.ResultSet} query Query string or ResultSet.
 * @returns {String|Number|Boolean|Object}
 */
exports.get_one = function(rs) {

    if (typeof(rs) == "string")
        rs = this.query(rs);
    if (!rs || !rs.first())
        return false;

    var result = this.get_column_value(rs, 1);
    rs.getStatement().close();
    return result;
}


/**
 * Returns the value of the given column in the given ResultSet rs.
 *
 * @param {java.sql.ResultSet}
 * @param {Number|String} column Column name or number.
 * @param {java.sql.ResultSetMetaData} meta optional Metadata for resultset.
 * @returns {String|Number|Boolean|Object}
 */
exports.get_column_value = function(rs, column, meta) {

    if (!meta)
        meta = rs.getMetaData();

    var type = meta.getColumnType(column);
    var result = null;
    
    // Note: Types variable declared at the beginning of this module.
    switch (type) {
        case Types.NULL:
            return null;
            break;

        case Types.BIGINT:
        case Types.INTEGER:
        case Types.SMALLINT:
        case Types.TINYINT:
            result = rs.getLong(column);
            return rs.wasNull() ? null : result;
            break;
        
        case Types.BLOB:
        case Types.CHAR:
        case Types.CLOB:
        case Types.DATE:
        case Types.LONGVARCHAR:
        case Types.VARCHAR:
        case Types.BINARY:
        case Types.VARBINARY:
        case Types.LONGVARBINARY:
            result = rs.getBytes(column);
            if (rs.wasNull())
                return "";
            else
                return "" + new String(new java.lang.String(result, "UTF-8"));
            break;

        case Types.BOOLEAN:
            result = rs.getBoolean(column);
            return rs.wasNull() ? null : result;
            break;

        case Types.DECIMAL:
        case Types.DOUBLE:
        case Types.FLOAT:
        case Types.NUMERIC:
        case Types.REAL:
            result = rs.getDouble(column);
            return rs.wasNull() ? null : result;
            break;

        case Types.ARRAY:
            result = rs.getArray(column);
            return rs.wasNull() ? null : result;
            break;

        case Types.JAVA_OBJECT:
        case Types.OTHER:
            result = rs.getObject(column);
            return rs.wasNull() ? null : result;
            break;

        case Types.TIME:
        case Types.TIMESTAMP:
            result = rs.getString(column);
            return rs.wasNull() ? null : result;
            break;

        default:
            log.debug("get_column_value()", "Unknown type", type);
            result = rs.getBinaryStream(column);
            return rs.wasNull() ? null : result;
    }
}
