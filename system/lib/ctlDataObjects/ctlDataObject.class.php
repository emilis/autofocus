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
 * Class for hashes with read/write methods.
 */
abstract class ctlDataObject
{
    var $objectName;
    var $objectConfig;

    var $fieldNames = array();
    
    function __construct($obj_name = false, $obj_config = false)
    {
        $this->objectName = $obj_name;
        $this->objectConfig = $obj_config;
    }
    
    function getFieldNames()
    {
        return $this->fieldNames;
    }

    /**
     *
     */
    function toHash()
    {
        $fields = $this->getFieldNames();
        $data = array();
        
        foreach ($fields as $field)
        {
            if (property_exists($this, $field))
                $data[$field] = $this->$field;
            else
                $data[$field] = null;
        }
            
        return $data;
    }


    /**
     *
     */
    function assign($hash)
    {
        $fields = $this->getFieldNames();
        foreach ($fields as $field)
        {
            if (array_key_exists($field, $hash))
                $this->$field = $hash[$field];
            else
                $this->$field = null;
        }
    }

    
    /**
     *
     */
    function updateValues($hash)
    {
        $fields = $this->getFieldNames();
        foreach ($fields as $field)
        {
            if (array_key_exists($field, $hash))
                $this->$field = $hash[$field];
        }
    }

    abstract function save();
    abstract function delete();
    abstract function read($id);
}
