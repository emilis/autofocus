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
 * A simple logger. Can be used as an event handler.
 */
class ctlLog
{
    var $name;
    var $config;

    var $dir;
    var $fp;
    
    function ctlLog($obj_name, $obj_config)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;

        if (@$this->config['dir'])
            $this->dir = $this->config['dir'];
        else
            $this->dir = DATA_DIR . '/Log';
    }

    function write($origin, $message)
    {
        if (!is_dir($this->dir))
            mkdir($this->dir, 0777, TRUE);
        if (!$this->fp)
            $this->fp = fopen($this->dir . '/' . date('Y-m-d') . '.log', 'a');

        if (!is_array($message))
            $message = serialize($message);
        fwrite($this->fp, date('Y-m-d H:i:s') . "|$origin|$message|||\n");
    }

    function close()
    {
        if ($this->fp)
            return fclose($this->fp);
        else
            return TRUE;
    }

}

