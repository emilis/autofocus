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

require_once dirname(__FILE__) . '/ctlDbRowList.class.php';

/**
 * A list for objects extending ctlDbRow. Allows retrieving portions of large resultsets.
 */
class ctlPagedDbRowList extends ctlDbRowList
{
    var $pageSize = 20;
    var $page = 0;
    var $lastQuery;

    var $totalCount;
    var $countedQuery;

    function __construct($obj_name, $obj_config = false)
    {
        parent::__construct($obj_name, $obj_config);

        if (array_key_exists('pageSize', $obj_config))
            $this->pageSize = $obj_config['pageSize'];
    }

    function query($sql)
    {
        $this->list = array();
        $this->pos = 0;
        $this->lastQuery = $sql;
        
        if (!$rs = $this->getDb()->SelectLimit($sql, $this->pageSize, $this->page * $this->pageSize))
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

    function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    function setActivePage($page)
    {
        $this->page = $page;
    }

    function count_total($refresh = false)
    {
        if ($refresh || $this->lastQuery !== $this->countedQuery)
        {
            // change 'select * from table' -> 'select count(*) from table':
            $from_pos = stripos($this->lastQuery, ' from ');
            $sql = 'select count('
                . substr($this->lastQuery, 7, $from_pos - 7) // 7 == strlen('select ');
                . ') ' 
                . substr($this->lastQuery, $from_pos);
            if ($order_pos = stripos($sql, 'order by'))
                $sql = substr($sql, 0, $order_pos);
            $this->countedQuery = $this->lastQuery;
            $this->totalCount = intval($this->getDb()->GetOne($sql));
        }
        
        return $this->totalCount;
    }
}


