<?php
/*
    Copyright 2005,2006,2007,2008,2009 Emilis Dambauskas

    This file is part of ctlSmi library.

    ctlSmi library is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ctlSmi library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ctlSmi library.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Sample SimpleCache interface implementation. Just stores data in memory during current request. Not very useful :-).
 */
class ctlSimpleCache
{
    public $name;
    public $config;

    private $cache;

    function __construct($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;

        $this->cache = array();
    }


    function clear()
    {
        $this->cache = array();
    }


    function get($key)
    {
        if (isset($this->cache[$key]))
            return $this->cache[$key];
        else
            return NULL;
    }


    function set($key, &$value)
    {
        $this->cache[$key] = $value;
    }


    function delete($key)
    {
        unset($this->cache[$key]);
    }

}

