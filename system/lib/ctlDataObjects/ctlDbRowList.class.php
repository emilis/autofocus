<?php
/*
    Copyright 2007,2008,2009 Emilis Dambauskas

    This file is part of ctlDataObjects library.

    ctlDataObjects library is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ctlDataObjects library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ctlDataObjects library.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/ctlDataObjectList.class.php';

/**
 * A class for lists of objects extending ctlDbRow.
 */
class ctlDbRowList extends ctlDataObjectList
{
    var $tableName;

    function __construct($obj_name = false, $obj_config = false)
    {
        parent::__construct($obj_name, $obj_config);

        if (!$this->tableName)
            $this->tableName = $this->dataObject->tableName;
    }

    function &getDb()
    {
        return $this->dataObject->getDb();
    }

    // -----------------------------------------------------------------------

    function __call($method, $params)
    {
        $start = substr($method, 0, 10); // select_by_...
        $field = substr($method, 10); // ...field

        switch ($start)
        {
            case 'select_by_':
                return $this->select(array($field => $params[0]), @$params[1]);
                break;
            case 'filter_by_':
                return $this->filter(array($field => $params[0]), @$params[1]);
                break;
            default:
                throw new BadMethodCallException("Method $method does not exist.");
        }
    }

    function select($where = false, $order = false)
    {
        $sql = "SELECT * FROM `$this->tableName` ";

        if ($where)
            $sql .= $this->getWhereSql($where);

        if ($order)
            $sql .= $this->getOrderBySql($order);

        return $this->query($sql);
    }

    function filter($where = false, $order = false)
    {
        $sql = "SELECT * FROM `$this->tableName` ";

        if ($where)
            $sql .= $this->getWhereSql($where, 'WHERE', true);

        if ($order)
            $sql .= $this->getOrderBySql($order);
        return $this->query($sql);
    }

    function query($sql)
    {
        $this->list = array();
        $this->pos = 0;

        if (!$rs = $this->getDb()->Execute($sql))
            return false;
        else
        {
            while (!$rs->EOF)
            {
                $this->assignRecord($rs->fields, $this->pos);
                $rs->MoveNext();
                $this->pos++;
            }
            $rs->Close();
            $this->pos = 0;

            return true;
        }
    }

    function assignRecord(&$data, $pos)
    {
        $this->list[$pos] = $this->newDataObject();
        $this->list[$pos]->assign($data);
    }

    // -----------------------------------------------------------------------
    
    protected function getWhereSql($where, $prefix = 'WHERE', $like = false)
    {
        assert(is_array($where));
        $sql = ' ';

        if ($like)
            $where = array_filter($where, create_function('$var', 'return !empty($var);'));

        foreach($where as $field => $value)
        {
            $sql .= "$prefix `$field`"; // AND `field`
            
            if (is_array($value))
            {
                $sql .= ' IN(';
                $separator = '';
                foreach ($value as $i => $val)
                {
                    $sql .= $separator.$this->dataObject->quoteValue($field, $val);
                    $separator = ',';
                }
                $sql .= ') ';
            }
            else if ($like)
            {
                $sql .= " LIKE " . $this->dataObject->quoteValue($field, "%$value%") . " ";
            }
            else
            {
                $sql .= " = " . $this->dataObject->quoteValue($field, $value);
            }
            
            $prefix = ' AND ';
        }
        return $sql;
    }

    protected function getOrderBySql($order)
    {
        if (!is_array($order) || !count($order))
            return '';
        else
        {
            $sql = ' ORDER BY ';
            $separator = '';
            foreach ($order as $field => $direction)
            {
                $sql .= "$separator $field $direction";
                $separator = ',';
            }
            return $sql;
        }
    }
}
