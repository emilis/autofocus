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
 * A simple Events handler. Sends event data using PHP mail() function.
 */
class ctlEventsMailer
{
    var $name;
    var $config;
    
    
    /**
     *
     */
    function ctlEventsMailer($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;
    }
    
    
    /**
     *
     */
    function sendMessage($action, $message, $headers = false)
    {
        if (!$headers)
        {
            $headers = array(
                'Time' => date('c'),
                'Server' => WEB_URL,
            );
        }

        if (!is_string($message))
            $message = serialize($message);
        
        $str = "\n===[ $action | $headers[Time] ]===\n";
        
        foreach ($headers as $key => $value)
            $str .= "$key: $value\n";
        
        $str .= "-----\n";
        $str .= $message;
        $str .= "\n======\n";
        
        $subject = $headers['Server'].'/'.$action.' @'.$headers['Time'];
        
        return @mail($this->config['to'], $subject, $str);
    }
}

