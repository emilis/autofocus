<?php
/*
    Copyright 2007,2008,2009 Emilis Dambauskas

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

/**
 * Interactive PHP console.
 */
class ctlConsole
{
    var $name;
    var $config;
    
    var $fp;
    
    var $history;
    var $commands;
    var $cmdids;
    
    function __construct($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;
        
        $this->history = array();
        
        if (isset($obj_config['commands']))
            $this->commands = $obj_config['commands'];
        else
        {
            $this->commands = array(
                'q' => '$console->close();',
                'quit' => array('Exits console.','$console->close();' ),
                
                // special keys:
                '1b5b41' => '$input = $console->getHistory();echo "History: $input\n";eval($input);', // UP
                );
        }
        
        $this->cmdids = array_keys($this->commands);
    }
    
    
    /**
     *
     */
    function open($file_name = 'php://stdin', $mode = 'r')
    {
        $this->fp = fopen($file_name, $mode);
    }
    
    
    /**
     *
     */
    function close()
    {
        return fclose($this->fp);
    }
    
    
    /**
     *
     */
    function eof()
    {
        return !is_resource($this->fp) || feof($this->fp);
    }
    
    
    /**
     *
     */
    function setFile(&$fp)
    {
        $this->fp = &$fp;
    }
    
    
    /**
     *
     */
    function read()
    {
        return fgets($this->fp, 10240);
    }
    
    
    /**
     *
     */
    function debug($data)
    {
        if (is_string($data))
        $data = strlen($data).": $data\n".implode(' ', unpack('H*', $data));
        if (!$data)
            var_dump($data);
        else
            print_r($data);
    }
    
    
    /**
     *
     */
    function processInput($input)
    {
        // remove newlines:
        $input = str_replace("\n", '', $input);
        
        // esc commands (special keys):
        if (strlen($input) && ord($input{0}) == 27)
            $input = $this->processSpecialKey($input);
        
        /* debug one-char input:
        if (strlen($input) == 1)
            debug($input);
        //*/
        
        // commands:
        if (in_array($input, $this->cmdids))
            $input = $this->getCommand($input);
        
        // history:
        array_push($this->history, $input);
        
        return $input;
    }
    
    
    /**
     *
     */
    function getCommand($input)
    {
        return $this->commands[$input];
    }
    
    
    /**
     * 
     */
    function processSpecialKey($input)
    {
        return implode('', unpack('H*', $input));
    }
    
    
    /**
     *
     */
    function getHistory($entry = false)
    {
        if ($entry === false)
            return array_pop($this->history);
        else
            return $this->history[$entry];
    }
}

?>
