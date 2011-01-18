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
 * WebMapper interface implementation. Maps HTTP requests to objects and methods.
 */
class ctlWebMapper
{
    /**
     * Object full name.
     * @attribute private string $name
     */
    var $name;
    
    /**
     * Configuration array.
     * @attribute private array $conrfig
     */
    var $config;
    
    /**
     * Sets default values for attributes.
     * 
     * @param string $obj_name Object name (as called from {@link loadObject()} function
     * @param array $obj_config Configuration variables
     */
    function ctlWebMapper($obj_name, $obj_config)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;
        
        if (!@$this->config['default_call'])
            $this->config['default_call'] = 'Site:showIndex';

        // Allow only Site if no config. If you want to forbid all objects, pass an empty array.
        if (!isset($this->config['allowed_objects']) || !is_array($this->config['allowed_objects']))
            $this->config['allowed_objects'] = array('Site.Site');
    }


    /**
     *
     */
    function showError($code = 404, $message = FALSE)
    {
        if ($message)
        {
            $log = &loadObject('Log');
            $log->write("$this->name:showError", $message);
        }
        
        $site = &loadObject('Site');
        return $site->showError($code);
    }
    
    
    /**
     * Maps GET/POST request to appropriate module object.
     *
     * @return mixed Result from module object call.
     */
    function mapRequest()
    {
        // --- 1. create $obj_fullname and $action params:
        if (@$_REQUEST['object'] && (@$_REQUEST['action'] || @$_REQUEST['operation']))
        {
            $obj_name = $_REQUEST['object'];
            $action = (@$_REQUEST['action']) ? $_REQUEST['action'] : $_REQUEST['operation'];
        }
        else if (@$_REQUEST['call'])
        {
            list($obj_name, $action) = explode(':', $_REQUEST['call']);
        }
        else if (@$_REQUEST['script'])
        {
            $obj_name = 'Site.Site';
            $action = 'showScript';
        }
        else if (@$_REQUEST['page'])
        {
            $obj_name = 'Site.Site';
            $action = 'showPage';
        }
        else if (@$this->config['default_call'])
        {
            list($obj_name, $action) = explode(':', $this->config['default_call']);
        }
        else
        {
            return $this->showError(404, 'Could not decode request and no config[default_call] found in '.__FILE__.':'.__LINE__);
        }
        
        // fix $obj_name:
        $obj_name = getObjectName($obj_name, 'instance');

        // --- 2. check for permissions to call the object from web
        //if (!in_array($obj_name, $this->config['allowed_objects']))
        if (!$this->isObjectAllowed($obj_name))
            return $this->showError(404, "Request for `$obj_name:$action` which is not in config[allowed_objects] list.");
        
        // --- 3. load object and call method on it:
        $object = &loadObject($obj_name);
        
        if (method_exists($object, 'getWebMethods'))
        {
            $allowed_methods = $object->getWebMethods();
            assert(is_array($allowed_methods));
            if (!in_array($action, $allowed_methods))
                return $this->showError(404, "Request for `$obj_name:$action` which is not in `$obj_name:getWebMethods()` list.");
        }

        if ( !method_exists($object, $action) )
            return $this->showError(404, "Request for a non-existant method `$obj_name:$action`.");

        return call_user_func(array(&$object, $action));
    } // end of method mapRequest()

    
    /**
     *
     */
    function isObjectAllowed($obj_name)
    {
        if (in_array($obj_name, $this->config['allowed_objects']))
            return TRUE;
        else
        {
            for ($i=0,$size=count($this->config['allowed_objects']); $i<$size; $i++)
            {
                $allowed = $this->config['allowed_objects'][$i];
                if (strpos($allowed, '*'))
                {
                    // strpos("Module.Object", "Module.*") === 0
                    if (0 === strpos($obj_name, substr($allowed, 0, -1)))
                        return TRUE;
                }
            }
            return FALSE;
        }
    }
    
    
    /**
     * Redirects HTTP request to a page specified by $call.
     *
     * @param string $call Method address in format: "Module.Object:Method"
     * @param optional array $params Additional parameters to send
     * @return string Always NULL, because the result of this method is usually returned from page object methods
     */
    function redirect($call, $params = NULL)
    {
        // send HTTP redirect:
        header('Location: '.$this->getUri($call, $params));
        
        return NULL;
    }
    
    
    /**
     * Creates URI from given method call and additional parameters
     *
     * @param string $call Method address in format: "Module.Object:Method"
     * @param optional array $params Additional parameters to send
     * @return string URI for the method call (Note that it is not checked if the URI exists at all)
     */
    function getUri($call, $params = NULL)
    {
        $call = explode(':', $call);
        
        $url = 'index.php?object='.$call[0].'&action='.$call[1];
        
        // add params to URL:
        if (is_array($params) && sizeof($params))
        {
            foreach ($params as $name => $value)
            {
                $url .= '&'.urlencode($name).'='.urlencode($value);
            }
        }
        
        return $url;
    }
}

