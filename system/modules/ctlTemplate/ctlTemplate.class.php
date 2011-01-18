<?php
/*
    Copyright (c) 2006,2009 Emilis Dambauskas

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
 */

/** 
 * ctlTemplate template engine.
 */
class ctlTemplate
{
    /**
     * Object instance name
     * @attribute private string name
     */
    var $name;
    
    /**
     * Object configuration
     * @attribute private array config
     */
    var $config;
    
    /**
	 * Template variable storage
	 * @attribute private array assignedVars
	 */
    var $assignedVars;
    
    /**
     * Template output variable storage
     * @attribute private array return
     */
    var $returnVars;
    
    /**
	 * Stores PHP error reporting level while parsing
	 * @attribute private int e
	 */
	var $e;
    
    /**
	 * Template filename
	 * @attribute private string file
	 */
	var $file;
    
    
    function ctlTemplate($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;
        
        $this->assignedVars = array();
        $this->returnVars = array();
        $this->e = 0;
        $this->file = '';
    }
    
    /**
     *
     */
    function assign($varName, $varValue = NULL)
    {
        if (is_array($varName))
        {
            $this->assignedVars = array_merge($this->assignedVars, $varName);
        }
        else if (is_string($varName))
        {
            $this->assignedVars[$varName] = $varValue;
        }
    }
    
    
    /**
     *
     */
    function assignRef($varName, &$varValue)
    {
        $this->assignedVars[$varName] = &$varValue;
    }
    
    
    /**
     *
     */
    function clearAssigned($varName)
    {
        unset($this->assignedVars[$varName]);
    }
    
    
    /**
     *
     */
    function clearAllAssigned()
    {
        $this->assignedVars = array();
    }
    
    
    /**
     *
     */
    function returnThis($varName, $varValue = NULL)
    {
        if (is_array($varName))
        {
            $this->returnVars = array_merge($this->returnVars, $varName);
        }
        else if (is_string($varName))
        {
            $this->returnVars[$varName] = $varValue;
        }
    }
    
    
    /**
     *
     */
    function clearReturn($varName)
    {
        unset($this->returnVars[$varName]);
    }
    
    
    /**
     *
     */
    function clearAllReturn()
    {
        $this->returnVars = array();
    }
    
    
    /**
     *
     */
    function getOneReturnOthers($array, $varName = 'html')
    {
        $result = $array[$varName];
        unset($array[$varName]);
        
        $this->returnVars = array_merge($this->returnVars, $array);
        
        return  $result;
    }
    
    
    /**
     *
     */
    function fetch($template)
    {
        if (!file_exists($template) || !is_file($template))
			return '<p class="error"><strong>ctlTemplate error: '.$template.'</strong> does not exist!<p>';
        
        $this->file = $template;
		unset($template);
					
		// create vars from data array
		extract($this->assignedVars, EXTR_OVERWRITE);
		
		$this->e = error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE); // quiet down warnings and notices:
		ob_start(); // start output buffering:
		require $this->file; // parse the file. Produces fatal error if file not found.
		error_reporting($this->e); // restore previous error reporting level
		$r = ob_get_contents(); // get parsed text
		ob_end_clean(); // quietly end output buffering
		
		return $r; // uh-oh what does this do? ;)
    }
    
    
    /**
     *
     */
    function display($template)
    {
        echo $this->fetch($template);
    }
    
    
    /**
     *
     */
    function fetchArray($template, $stdoutVarName = 'html')
    {
        $this->returnVars[$stdoutVarName] = $this->fetch($template);
        return $this->returnVars;
    }
    
    
    /**
     *
     */
    function helper()
    {
        $args = func_get_args();
        $helper = &loadObject('ctlTemplate.ctlTemplateHelper');
        return call_user_func_array(array($helper, 'show'), $args);
    }
}

?>
