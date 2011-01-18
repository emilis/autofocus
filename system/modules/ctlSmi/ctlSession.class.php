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
 * Session interface implementation. Uses PHP session mechanism.
 */
class ctlSession
{
    /**
     * Object full name.
     * @attribute private string $name
     */
    var $name;
    
    /**
     * Configuration array.
     * @attribute private array $config
     */
    var $config;
    
    /**
     * Constructor.
     *
     * @param optional string $obj_fullname Object full name (as called from {@link loadObject()} function
     * @param optional array $obj_config Object configuration
     */
    function ctlSession($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;
    }
    
    /**
     * Starts session.
     */
    function start()
    {
        session_start();
    }
    
    /**
     * Closes session.
     */
    function close()
    {
        session_write_close();
        return TRUE;
    }
    
    
    /**
     * Sets session variable value.
     *
     * @param string $name Variable name
     * @param optional mixed $value Variable value
     */
    function setVar($name, $value = NULL)
    {
        $_SESSION[$name] = $value;
    }
    
    /**
     * Returns session variable value.
     *
     * @param string $name Variable name
     * @return mixed Variable value
     */
    function getVar($name)
    {
        return @$_SESSION[$name];
    }
    
    
    /**
     * Creates a session variable that references a given variable.
     *
     * @param string $name Session variable name
     * @param ref mixed $var Variable that should be referenced in session.
     */
    function registerVar($name, &$var)
    {
        $_SESSION[$name] = &$var;
    }
    
    
    /**
     * Destroys variable in session.
     *
     * @param string $name Session variable name.
     */
    function unsetVar($name)
    {
        unset($_SESSION[$name]);
    }
    
    
    /**
     * Destroys all session variables.
     */
    function unsetAllVars()
    {
        $_SESSION = array();
    }
    /**
     * An alias of clearVars
     */
    function clearVars()
    {
        return $this->unsetAllVars();
    }
    
    
    /**
     * Returns session variable value and destroys it afterwards.
     *
     * @param string $name Variable name
     * @return mixed Variable value
     */
    function getAndUnsetVar($name)
    {
        $val = @$_SESSION[$name];
        unset($_SESSION[$name]);
        return $val;
    }
}

