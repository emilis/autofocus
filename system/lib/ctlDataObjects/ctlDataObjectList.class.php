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


/**
 * Class for lists of ctlDataObjects.
 */
class ctlDataObjectList implements Countable, Iterator, SeekableIterator
{
    var $name;
    var $config;
    
    var $dataObjectName;
    protected $dataObject;
    var $list = array();
    var $pos = 0;

    function __construct($obj_name = false, $obj_config = false)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;

        if (!$this->dataObjectName)
            $this->dataObjectName  = substr( getObjectName($this->name, 'long'), 0, -4); // strip '...List'

        $this->dataObject = &loadObject($this->dataObjectName);
    }

    function newDataObject()
    {
        return newObjectInstance($this->dataObjectName);
    }

    function count()
    {
        return count($this->list);
    }

    function seek($index)
    {
        if (array_key_exists($index, $this->list))
            return $this->list[$index];
        else
            return false;
    }

    function rewind()
    {
        $this->pos = 0;
    }

    function current()
    {
        return $this->list[$this->pos];
    }

    function key()
    {
        return $this->pos;
    }

    function next()
    {
        $this->pos++;
    }

    function valid()
    {
        return array_key_exists($this->pos, $this->list);
    }
}
