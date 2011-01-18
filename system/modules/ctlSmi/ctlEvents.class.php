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

require_once MODULES_DIR . '/Events/Events.interface.php';

/**
 * Implements Events interface.
 */
class ctlEvents implements Events
{
    var $callbacks;

    function __construct($obj_name, $obj_config)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;

        if (is_array($this->config) && array_key_exists('callbacks', $this->config))
            $this->callbacks = $this->config['callbacks'];
        else
            $this->callbacks = array();
    }

    function create($event_name, $data = false)
    {
        foreach ($this->getCallbackList($event_name) as $callback)
            call_user_func($this->getCallbackFunction($callback), $event_name, $data);
    }


    function registerCallback($event_pattern, $callback)
    {
        $this->callbacks[$event_pattern][] = $callback;
    }


    function unregisterCallback($event_pattern, $callback = false)
    {
        
        unset($this->callbacks[$event_pattern]);
    }

    //------------------------------------------------------------------------

    private function getCallbackFunction($callback)
    {
        static $functions = array();
        if (!array_key_exists($callback, $functions))
        {
            if (is_string($callback) && strpos($callback, ':') && strpos($callback, '::') === FALSE)
            {
                list($obj_name, $method) = explode(':', $callback);
                $functions[$callback] = array(
                    loadObject($obj_name),
                    $method
                    );
            }
            else
                $functions[$callback] = $callback;
        }
        
        return $functions[$callback];
    }

    private function getCallbackList($event_name)
    {
        $patterns = array_keys($this->callbacks);
        $callbacks = array();

        foreach ($patterns as $pattern)
        {
            if (strpos($pattern, '*') === FALSE)
            {
                if ($pattern == $event_name)
                    $callbacks = array_merge($callbacks, (array) $this->callbacks[$pattern]);
            }
            else if ($pattern == '*')
            {
                $callbacks = array_merge($callbacks, (array) $this->callbacks[$pattern]);
            }
            else
            {
                if (strpos($pattern, '*') === 0)
                {
                    if (strpos($pattern, '*') === strlen($pattern))
                    {
                        // *pattern*
                        if (strpos($event_name, substr($pattern, 1, -1)) !== FALSE)
                            $callbacks = array_merge($callbacks, (array) $this->callbacks[$pattern]);
                    }
                    else
                    {
                        // *pattern
                        if (strpos($event_name, substr($pattern, 1)) !== FALSE)
                           $callbacks = array_merge($callbacks, (array) $this->callbacks[$pattern]);
                    }
                }
                else if (strpos($pattern, '*') === strlen($pattern))
                {
                    // pattern*
                    if (strpos($event_name, substr($pattern, 0, -1)) !== FALSE)
                        $callbacks = array_merge($callbacks, (array) $this->callbacks[$pattern]);
                }
                else
                {
                    // invalid pat*tern
                    trigger_error("Invalid pattern '$pattern' in " . __FILE__ . ':' . __LINE__ . '.');
                }
            }

        }

        return $callbacks;
    }
}
