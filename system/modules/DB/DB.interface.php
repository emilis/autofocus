<?php

/*
    DB interfaces.
    Based on ADOdbLite (main functions + pear,extend modules):
        http://adodblite.sourceforge.net/functions.php
        http://adodblite.sourceforge.net/modules.php
    ADOdb documentation:
        http://phplens.com/lens/adodb/docs-adodb.htm
*/
interface DB
{
    public var $debug;

    function Affected_Rows();
    function Close();
    function Concat(); // $args = array('string', 'string', 'string');
    function ErrorMsg();
    function ErrorNo();
    function Execute($sql, $inputarray = false);
    function GetAll($sql);
//    function GetArray($sql);
//    function IfNull($field, $ifNull);
    function Insert_ID();
    function IsConnected();
    function qstr($string, $magic_quotes = false);
    function Qmagic($string);
    function SelectDB($dbname);
    function SelectLimit($sql, $nrows = false, $offset = false, $inputarray = false);
    function Version();

    // pear:
//    function Disconnect();
    function ErrorNative();
    function GetCol($sql);
    function GetOne($sql);
    function GetRow($sql);
    function LimitQuery($sql, $offset = false, $nrows = false, $inputarray = false);
    function Query($sql, $inputarray = false);
    function Quote($string);
    function SetFetchMode($mode); // 'ADODB_FETCH_DEFAULT' | 'ADODB_FETCH_NUM' | 'ADODB_FETCH_ASSOC' | 'ADODB_FETCH_BOTH'

    // extend:
//    function GetAssoc($sql, $inputarray = false, $force_array = false, $first2cols = false);
    function GenID($seqname = 'adodbseq', $startID = 1);
    function CreateSequence($seqname = 'adodbseq', $startID = 1);
    function DropSequence($seqname = 'adodbseq');
}

interface DBResultSet
{
    public var $EOF;
    public var $fields;
    
    function Close();
    function EOF();
    function FetchField($fieldOffset);
    function FieldCount();
    function Fields($column = false);
    function GetAll($numrows = false);
//    function GetArray($numrows = false);
    function GetRows($numrows = false);
    function Move($row = false);
    function MoveFirst();
    function MoveLast();
    function MoveNext();
    function RecordCount();
    
    // pear:
    function FetchInto($array);
    function FetchRow();
    function Free();
    function NumCols();
    function NumRows();

    // extend:
//    function GetAssoc($force_array = false);
//    function PO_RecordCount($table, $where);
    function NextRecordSet();
    function CurrentRow();
    function AbsolutePosition();
}
