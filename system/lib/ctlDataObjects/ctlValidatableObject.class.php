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
 *  An object that validates itself by calling validate_xxx() methods on itself and storing their return values as errors.
 *  Can be used as a mix-in of methods (see code at the bottom of this file.
 */
class ctlValidatableObject
{
    var $objectErrors = array();

    /**
     *
     */
    function getError($field)
    {
        if (array_key_exists($field, $this->objectErrors))
            return $this->objectErrors[$field];
        else
            return '';
    }


    /**
     *
     */
    function getHtmlError($field)
    {
        if ($error = $this->getError($field))
            return '<p class="error">' . $error . '</p>';
        else
            return '';
    }


    /**
     *
     */
    function hasErrors()
    {
        return count($this->objectErrors);
    }
    
    
    /**
     *
     */
    function clearErrors()
    {
        $this->objectErrors = array();
    }
    
    
    /**
     *
     */
    function addError($field, $error)
    {
        $this->objectErrors[$field] = $error;
    }
    
    
    /**
     *
     */
    function clearError($field)
    {
        unset($this->objectErrors[$field]);
    }


    /**
     *
     */
    function validate($fields = false)
    {
        // create method array:
        $methods = array();
        if ($fields && is_array($fields) && count($fields))
        {
            foreach ($fields as $field)
            {
                if (method_exists($this, "validate_$field"))
                    $methods[] = "validate_$field";
            }
        }
        else
        {
            foreach(get_class_methods($this) as $method)
            {
                if (strpos($method, 'validate_') === 0)
                    $methods[] = $method;
            }
        }
        
        // clear errors:
        $this->objectErrors = array();
        
        // call validation methods:
        foreach ($methods as $method)
        {
            if ($error = $this->$method())
                // 9 == strlen('validate_')
                $this->objectErrors[substr($method,9)] = $error;
        }
        
        // return error status:
        return !$this->hasErrors();
    }

    /*
    Mixin code:
    
    // Mixin properties from ctlValidatableObject:
    var $objectErrors = array();
    
    // Mixin functions from ctlValidatableObject:
    function getError($field) { return ctlValidatableObject::getError($field); }
    function getHtmlError($field) { return ctlValidatableObject::getHtmlError($field); }
    function hasErrors() { return ctlValidatableObject::hasErrors(); }
    function clearErrors() { return ctlValidatableObject::clearErrors(); }
    function addError($field, $error) { return ctlValidatableObject::addError($field, $error); }
    function clearError($field) { return ctlValidatableObject::clearError($field); }
    function validate($fields = false) { return ctlValidatableObject::validate($fields); }

    */
}
