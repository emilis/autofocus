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

require_once dirname(__FILE__) . '/ctlDbRow.class.php';
require_once dirname(__FILE__) . '/ctlValidatableObject.class.php';

/**
 * ctlDbRow with added validation methods from ctlValidatableObject.
 */
class ctlValidatableDbRow extends ctlDbRow
{
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
}

