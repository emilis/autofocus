<?php
/*
    Gluestick framework kernel.
    
    Copyright 2008,2009 Emilis Dambauskas <emilis.d@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * Conventions inside functions:
 *  $obj_name  - string in any form
 *  $obj_iname - (instance form) string in form "module.object.instance" or "module.object".
 *  $obj_lname - (long form) string in form "module.object"
 *  $obj_sname - (short form) string in form "module" or "module.object"
 *  $obj_oname - (object form) string in format "object"
 *  $mod_name  - string in format "module"
 *
 *  see function getObjectName() and its comments.
 */

/**
 * Loaded object array.
 * @var array $_OBJECTS
 */
global $_OBJECTS;
// clear object library when loading engine:
$_OBJECTS = array();


/**
 * Object and module loading configuration.
 * @var array $loading_info
 */
global $loading_info;
if (!isset($loading_info) || !is_array($loading_info) )
    $loading_info = array();


/**
 * Object configuration.
 * @var array $config
 */
global $config;
if (!isset($config) || !is_array($config))
    $config = array();

//------------------------------------------------------------------------------

// Register shutdown function:
if (!function_exists('shutdownGluestick'))
{
    /**
     * Calls unloadObject() for all loaded objects.
     *
     * @use $_OBJECTS
     */
    function shutdownGluestick()
    {
        global $_OBJECTS;

        // get object names in reverse order ( newest first ):
        $object_names = array_reverse(array_keys($_OBJECTS));
        
        foreach ($object_names as $oname)
        {
            unloadObject($oname, $_OBJECTS[$oname]);
        }
    }
}

register_shutdown_function('shutdownGluestick');

//------------------------------------------------------------------------------

/**
 * Returns module name part of the object name.
 *
 * @return string Module name. 
 * @param string $obj_name Object name (any form).
 */  
function getModuleName($obj_name)
{
    // return first element before '.'
    $obj_name = explode('.', $obj_name);
    return $obj_name[0];
}


/**
 * Returns object name forms.
 *
 * @return string Specified object name form. 
 * @param string $obj_name Any form of object name (Mod / Mod.Obj / Mod.Obj.Instance).
 * @param optional string $name_type Type of the name to return. Forms: object, short, long, instance, instance_id
 */  
function getObjectName($obj_name, $name_type = 'object')
{
    // split "module.object.instance" into array("module","object","instance")
    $arr = explode('.', $obj_name);
    
    switch ($name_type)
    {
        case 'instance':
            if (count($arr) > 1) // if long name or instance name
                return implode('.', $arr); // return full instance name ("Mod.Obj" or "Mod.Obj.Instance")
            else
                return $arr[0].'.'.$arr[0]; // return "Mod.Mod" name
        break;
        
        case 'long':
            if (count($arr) > 1)  // if long name or instance name
                return $arr[0].'.'.$arr[1]; // return "Mod.Obj"
            else
                return $arr[0].'.'.$arr[0]; // return "Mod.Mod"
        break;
        
        case 'short':
            if (count($arr) > 1 && $arr[0] != $arr[1])  // if long name or instance name
                return implode('.', $arr); // return full instance name ("Mod.Obj" or "Mod.Obj.Instance") since Mod and Obj differ.
            else
                return $arr[0]; // return "Mod", since Mod == Obj
        break;
        
        case 'instance_id':
            if (count($arr) > 2) // if instance name
                return $arr[2]; // return "Instance"
            else
                return '';
        break;
        
        case 'object':
        default:
            if (count($arr) > 1)  // if long name or instance name
                return $arr[1]; // return Obj part
            else
                return $arr[0]; // return Mod part
    }
}


/**
 * Finds module directory by given object name.
 *
 * @return mixed Directory name string on success, FALSE on failure. 
 * @param string $obj_name Any form of object name.  
 */ 
function getModuleDir($obj_name)
{
    // TODO: this is incorrect! What about loading_info[$obj_name][type] == 'object' objects?
    $dir = MODULES_DIR . '/' . getModuleName($obj_name);

    if (is_readable($dir) && is_dir($dir))
        return $dir;
    else
        return FALSE;
}


/**
 * Checks if the object is already loaded.
 *
 * @return boolean TRUE if module is loaded successfully, FALSE otherwise. 
 * @param string $obj_name Any form of object name.  
 * @use $_OBJECTS
 */ 
function isObjectLoaded($obj_name)
{
    global $_OBJECTS;
    
    // get instance name of the object:
    $obj_iname = getObjectName($obj_name, 'instance');
    
    // check in global $_OBJECTS array:
    return array_key_exists($obj_iname, $_OBJECTS); 
}


//------------------------------------------------------------------------------

/**
 * Finds out information about how to load an object.
 *
 * @return array Object loading information in form: array('type'=> 'function' /  'class' / 'call', 'l_name' => function, class name or Object:method call).
 * @param string $obj_iname Object instance name.
 * @use $loading_info
 */
function getObjectLoadingInfo($obj_iname)
{
    global $loading_info;

    assert(is_array($loading_info));
    
    //--- 1. search memory: ---
    
    // return at once if found in memory:
    if (isset($loading_info[$obj_iname]['type']))
        return $loading_info[$obj_iname];
    
    // search for non-instance object loading info:
    $obj_lname = getObjectName($obj_iname, 'long');
    if (isset($loading_info[$obj_lname]['type']))
        return $loading_info[$obj_lname];
        
    // maybe we should use module_loader function?:
    $mod_name = getModuleName($obj_iname);
    if (isset($loading_info[$mod_name]['type']) && $loading_info[$mod_name]['type'] == 'function')
        return $loading_info[$mod_name];
        
    //--- 2. build $loading_info[obj_lname] ---
    
    $mod_dir = MODULES_DIR . '/' . $mod_name;
    // check if module dir exists:
    if (!is_dir($mod_dir))
        trigger_error("KERNEL getObjectLoadingInfo($obj_iname) error: module directory $mod_dir does not exist.", E_USER_ERROR);
        
    // check for module_loader file:
    if (is_file("$mod_dir/{$mod_name}_loader.php"))
    {
        // create loading info for module and return:
        $loading_info[$mod_name] = array(
            'type' => 'function',
            'l_name' => $mod_name.'_loader',
            );
        return $loading_info[$mod_name];
    }
    else
    {
        $obj_oname = getObjectName($obj_iname, 'object');
        if (is_file("$mod_dir/$obj_oname.class.php"))
        {
            // create loading info for object (referenced by long name) and return:
            $loading_info[$obj_lname] = array(
                'type' => 'class',
                'l_name' => $obj_oname,
                );
            return $loading_info[$obj_lname];
        }
        else
        {
            trigger_error("KERNEL getObjectLoadingInfo($obj_iname) error: neither function file nor class file found.", E_USER_ERROR);
        }
    }
}


/**
 * Builds config for an object.
 *
 * @param string $obj_iname Object instance name.
 * @param optional array $obj_config Additional configuration for the object.
 * @return array Config array for the object.
 * @use $config
 */
function getObjectConfig($obj_iname, $obj_config = FALSE)
{
    global $config;

    assert(is_array($config));
    
    // config loading status:
    static $config_loaded;
    if (!is_array($config_loaded))
        $config_loaded = array();
    
    // return instance config at once if found:
    if (array_key_exists($obj_iname, $config_loaded) && array_key_exists($obj_iname, $config))
    {
        if (is_array($obj_config))
            return array_merge($config[$obj_iname], $obj_config);
        else
            return $config[$obj_iname];
    }
    
    // get CONFIG_DIR listing into hash:
    static $file_cache;
    if (!is_array($file_cache))
    {
        $file_cache = array();
        $d = dir(CONFIG_DIR);
        while (FALSE !== ($entry = $d->read()))
            $file_cache[$entry] = TRUE;
    }
    
    // get needed object name forms:
    $mod_name = getModuleName($obj_iname);
    $obj_lname = getObjectName($obj_iname, 'long');
    
    // create variables if they do not exist yet:
    if (!array_key_exists($mod_name, $config)) $config[$mod_name] = array();
    if (!array_key_exists($obj_lname, $config)) $config[$obj_lname] = array();
    if (!array_key_exists($obj_iname, $config)) $config[$obj_iname] = array();
    
    // --- Get info from config files: ---
    $mod_config_prefix = CONFIG_DIR . '/' . $mod_name;

    // e.g. config/Module/
    if (array_key_exists($mod_name, $file_cache)) // alias of is_file(CONFIG_DIR.'/'.$mod_name)
    {
        // we've got CONFIG_DIR/Module/ directory:
        
        // get needed object name forms
        $obj_oname = getObjectName($obj_iname, 'object');
        if ($instance_id = getObjectName($obj_iname, 'instance_id'))
            $obj_oiname = $obj_oname.'.'.$instance_id; // "Object.Instance"
        
        // module config file:
        // e.g. config/Module/_.cfg.php
        unset($modconfig);
        if (is_file("$mod_config_prefix/_.cfg.php"))
            include_once "$mod_config_prefix/_.cfg.php";
        if (isset($modconfig) && is_array($modconfig))
            $config[$mod_name] = array_merge($config[$mod_name], $modconfig);
        
        // object config file:
        // e.g. config/Module/Object.cfg.php
        unset($objconfig);
        if (is_file("$mod_config_prefix/$obj_oname.cfg.php"))
            include_once "$mod_config_prefix/$obj_oname.cfg.php";
        if (isset($objconfig))
            $config[$obj_lname] = array_merge($config[$obj_lname], $objconfig);
            
        // object instance config file:
        // e.g. config/Module/Object.Instance.cfg.php
        if ($instance_id)
        {
            unset($objconfig);
            if (is_file("$mod_config_prefix/$obj_oiname.cfg.php"))
                include_once "$mod_config_prefix/$obj_oiname.cfg.php";
            if (isset($objconfig))
                $config[$obj_iname] = array_merge($config[$obj_iname], $objconfig);
        }
    }
    // e.g. config/Module.cfg.php
    else if (array_key_exists($mod_name.'.cfg.php', $file_cache))
    {
        // we've got CONFIG_DIR/Module.cfg.php file:
        unset($modconfig);
        include_once "$mod_config_prefix.cfg.php";
        if (isset($modconfig) && is_array($modconfig))
            $config[$mod_name] = array_merge($config[$mod_name], $modconfig);
    }
    
    // --- Return: ---
    
    $config_loaded[$obj_iname] = TRUE;
    
    // merge config arrays (object config over module config)
    $result = $config[$obj_iname] = array_merge(
        $config[$mod_name],
        $config[$obj_lname],
        $config[$obj_iname]
        );

    if (is_array($obj_config))
        return array_merge($result, $obj_config);
    else
        return $result;
}

//------------------------------------------------------------------------------

/**
 * Loads object and stores a reference to it in $_OBJECTS.
 *
 * @return ref object Reference to an object in $_OBJECTS. Triggers E_USER_ERROR on failure. 
 * @param string $obj_name Any form of object name. 
 * @param optional array $obj_config Additional configuration for the object.
 * @use $_OBJECTS 
 */ 
function &loadObject($obj_name, $obj_config = array())
{
    global $_OBJECTS;
    
    // get instance name:
    $obj_iname = getObjectName($obj_name, 'instance');
    
    // return object at once if it is found in $_OBJECTS[] cache:
    if (!array_key_exists($obj_iname, $_OBJECTS))
    {
        $_OBJECTS[$obj_iname] = &_createObject($obj_iname, $obj_config);
    }
    
    return $_OBJECTS[$obj_iname];
}


/**
 * Creates a unique instance of an object. 
 * 
 * @return object Loaded object (not a reference). Triggers E_USER_ERROR on failure. 
 * @param string $obj_name Any form of object name. 
 * @param optional array $obj_config Additional configuration for the object.
 */
function newObjectInstance($obj_name, $obj_config = array())
{
    static $donors = array();

    $obj_iname = getObjectName($obj_name, 'instance');

    if (!array_key_exists($obj_iname, $donors))
    {
        $donors[$obj_iname] = &_createObject($obj_iname, $obj_config); 
    }

    return clone $donors[$obj_iname];
}


/**
 * Creates an object. This is an internal kernel function. You should not use it. Use loadObject/newObjectInstance instead.
 *
 * @return ref object Created object. Triggers E_USER_ERROR on failure. 
 * @param string $obj_iname Object instance name.
 * @param array $obj_config Additional configuration for the object.
 */
function &_createObject($obj_iname, $obj_config)
{
    // get object config:
    $obj_config = getObjectConfig($obj_iname, $obj_config);

    /* Call order is important: load config first, and loading info after that.
        Such order allows overwriting loading info in module/object config. */
    
    // get loading info:
    $loadInfo = getObjectLoadingInfo($obj_iname);
  
    // get to the real object if we receive a proxy:
    while ($loadInfo['type'] == 'object')
    {
        $loadInfo = getObjectLoadingInfo($loadInfo['l_name']);
    }
   
    //--------------------------------------------------------------------

    switch ($loadInfo['type'])
    {
        case 'function':
            $func_name = $loadInfo['l_name'];
            if (function_exists($func_name))
                $object = call_user_func($func_name, $obj_iname, $obj_config);
            else
            {
                $mod_name = substr($loadInfo['l_name'], 0, -7); // strip _loader from function name;
                $file_name = MODULES_DIR . "/$mod_name/$func_name.php";
                if (!is_readable($file_name))
                    trigger_error("KERNEL ERROR: file `$file_name` does not exist for object $obj_iname.", E_USER_ERROR);
                else
                    include_once $file_name;
                
                if (!function_exists($func_name))
                    trigger_error("KERNEL ERROR: function `$func_name` does not exists for object $obj_iname.", E_USER_ERROR);
                else
                    $object = call_user_func($func_name, $obj_iname, $obj_config);
            }
            break;

        case 'class':
            $class = $loadInfo['l_name'];

            if (class_exists($class))
                $object = &new $class($obj_iname, $obj_config);
            else
            {
                $mod_name = getModuleName($obj_iname);
                $file_name = MODULES_DIR . "/$mod_name/$class.class.php";
                if (!is_readable($file_name))
                    trigger_error("KERNEL ERROR: file `$file_name` does not exist for object $obj_iname.", E_USER_ERROR);
                else
                    include_once $file_name;
                
                if (!class_exists($class))
                    trigger_error("KERNEL ERROR: class `$class` does not exists for object $obj_iname.", E_USER_ERROR);
                else
                    $object = &new $class($obj_iname, $obj_config);
            }
            break;

        case 'call':
            // call format is: Module.Object:method
            // Note: loader object is always loaded into $_OBJECTS.
            list($loader_name, $method) = explode(':', $loadInfo['l_name']);
            $object = &loadObject($loader_name)->$method($obj_iname, $obj_config);
            break;

        default:
            // Note: loadinfo may fail due to broken loadInfo[type]=='object' chain.
            trigger_error("KERNEL ERROR: failed to get loading type (loadInfo[type]) for object $obj_iname.", E_USER_ERROR);
    }

    // check if we got an object:
    if (!is_object($object))
        trigger_error("KERNEL ERROR: failed to create object $obj_iname.", E_USER_ERROR);

    return $object;
}

/**
 * Unloads an object.
 *
 * @return boolean TRUE on success, FALSE on error.
 * @param string $obj_name Any form of object name.
 * @param ref object $object Object to unload.
 * @use $_OBJECTS 
 */ 
function unloadObject($obj_name, &$object)
{
    global $_OBJECTS;
    
    $loadInfo = getObjectLoadingInfo($obj_name);
    $obj_iname = getObjectName($obj_name, 'instance');

    
    // unload Object:
    if ($loadInfo['type'] == 'function')
    {
        $function_name = str_replace('_loader', '_unloader', $loadInfo['l_name']);
        if (function_exists($function_name))
        {
            if (call_user_func($function_name, $obj_iname, $object))
            {
                // object unloaded successfully:
                unset($_OBJECTS[$obj_iname]);
                return TRUE;
            }
            else
            {
                // object unloaded unsuccessfully
                trigger_error("CTLF unloadObject($obj_name): object unload function returned FALSE.", E_USER_WARNING);
                return FALSE;
            }
        }
        else
        {
            // unloader function does not exist (that means the module does not need it).';
            unset($_OBJECTS[$obj_iname]);
            return TRUE;
        }
    } // end if ($load_type == function)
    else if ($loadInfo['type'] == 'class')
    {
        if (method_exists($object, 'unload'))
        {
            if (call_user_func(array($object, 'unload'), $obj_iname))
            {
                // object unloaded successfully:';
                unset($_OBJECTS[$obj_iname]);
                return TRUE;
            }
            else
            {
                // object unloaded unsuccessfully';
                trigger_error("CTLF unloadObject($obj_name): object unload method returned FALSE.", E_USER_WARNING);
                return FALSE;
            }
        }
        else
        {
            // unloader method does not exist (that means the module does not need it).';
            unset($_OBJECTS[$obj_iname]);
            return TRUE;
        }
    } // end if ($load_type == class)
}

?>
