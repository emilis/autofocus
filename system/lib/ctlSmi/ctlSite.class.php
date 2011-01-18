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
 * Site.Site object class.
 */
class ctlSite
{
    /**
     * Object full name.
     * @attribute private string $name
     */
    protected $name;
    
    /**
     * Configuration array.
     * @attribute private array $config
     */
    protected $config;
    
    /**
     * Directory where page HTML files are kept.
     * @attribute private string $page_dir
     */
    protected $page_dir;
    
    /**
     * Directory where script PHP files are kept.
     * @attribute private string $script_dir
     */
    protected $script_dir;
    
    /**
     * Directory where template files are kept.
     * @attribute private string $tpl_dir
     */
    protected $tpl_prefix;
    
    /**
     * Sets default values for attributes {@link $this->tpl_dir}, {@link $this->page_dir}, {@link $this->script_dir}
     * 
     * @param string $obj_fullname Object full name (as called from {@link loadObject()} function
     * @param array $config Configuration variables
     */
    function __construct($obj_name, $config)
    {
        // general config
        $this->name = $obj_name;
        $this->config = $config;

        $mod_dir = getModuleDir($this->name);
        
        // tpl_prefix setting
        if (@$config['tpl_prefix'])
            $this->tpl_prefix = $config['tpl_prefix'];
        else
            $this->tpl_prefix = "$mod_dir/tpl/" . getObjectName($this->name) . '-'; // ".../tpl/Site-"
        
        // page_dir setting
        if (@$config['page_dir'])
            $this->page_dir = $config['page_dir'];
        else
            $this->page_dir = "$mod_dir/pages";
            
        
        // script_dir setting
        if (@$config['script_dir'])
            $this->script_dir = $config['script_dir'];
        else
            $this->script_dir = "$mod_dir/scripts";
    }
    
    
    /**
     * Shows error page.
     *
     * @param mixed $msg Error message or HTML error code (e.g. 404)
     * @return string Error page HTML
     * @note Uses object ctlTemplate
     */
    function showError($msg)
    {
        /*
         * You can pass HTTP error codes directly to showError() their messages 
         * are filled with text here. This is partially based on Apache2 HTTP 
         * error handling and partialy with RFC2616.
         */
        switch ($msg)
        {
            case 400: $msg = 'Your browser (or proxy) sent a request that this server could not understand.'; break;
            case 401:
            case 407:
                $msg = "This server could not verify that you are authorized to access this page.\n You either supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required.\n\nIn case you are allowed to request the document, please check your user-id and password and try again."; break;
            case 402: $msg = 'Payment is required to access this page.'; break;
            case 403: $msg = "You don't have permission to access the requested object. It is either read-protected or not readable by the server."; break;
            case 404: 
                $msg = "The requested URL was not found on this server.\n\n";
                if (@$_SERVER['HTTP_REFERER'])
                    $msg .= 'The link on the <a href="'.$_SERVER['HTTP_REFERER'].'">referring page</a> seems to be wrong or outdated. Please inform the author of <a href="'.$_SERVER['HTTP_REFERER'].'">that page</a> about the error.';
                else
                    $msg .= 'If you entered the URL manually please check your spelling and try again.';
                break;
            case 405: $msg = "The $_SERVER[REQUEST_METHOD] method is not allowed for the requested URL."; break;
            case 406: $msg = "An appropriate representation of the requested resource could not be found on this server."; break;
            // case 407: see case 401
            // case 408: Request Timeout: should be implemented by web server.
            case 409: $msg = "The request could not be completed due to a conflict with the current state of the resource."; break;
            case 410:
                $msg = "The requested URL is no longer available on this server and there is no forwarding address.\n\n";
                
                if (@$_SERVER['HTTP_REFERER'])
                    $msg .= "Please inform the author of the <a href=\"$_SERVER[HTTP_REFERER]\">referring page</a> that the link is outdated.";
                else
                    $msg .= "If you followed a link from a foreign page, please contact the author of this page.";
                break;
            case 411: $msg = "A request with the $_SERVER[REQUEST_METHOD] method requires a valid <code>Content-Length</code> header."; break;
            case 412: $msg = "The precondition on the request for the URL failed positive evaluation."; break;
            case 413: $msg = "The $_SERVER[REQUEST_METHOD] method does not allow the data transmitted, or the data volume exceeds the capacity limit."; break;
            case 414: $msg = "The length of the requested URL exceeds the capacity limit for this server. The request cannot be processed."; break;
            case 415: $msg = "The server does not support the media type transmitted in the request."; break;
            // case 416: $msg = "Requested Range Not Satisfiable"; break;
            // case 417: Expectation Failed: should be implemented by web server.
            
            case 500: $msg = "The server encountered an internal error and was unable to complete your request."; break;
            case 501: $msg = "The server does not support the action requested by the browser."; break;
            // case 502: Bad Gateway: should be implemented by web server.
            case 503: $msg = "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later."; break;
            // case 504: Gateway Timeout: should be implemented by web server.
            // case 505: HTTP Version Not Supported: should be implemented by web server.
        }
        
        // Now let's just return the error page:
        $tpl = &loadObject('ctlTemplate');
        $tpl->assign('message', $msg);
        return $tpl->fetch($this->tpl_prefix . 'showError.php');
    }
    
    
    /**
     * Returns HTML content wrapped in main website template.
     *
     * @param mixed $content Content HTML string or hash containing HTML parts of the page.
     * @return string Page HTML.
     * @note Uses object ctlTemplate
     */
    function showContent($content)
    {
        $tpl = &loadObject('ctlTemplate');
        
        if (!is_array($content))
            $content = array('html' => $content);

        $content['site_config'] = $this->config;
        
        $tpl->assign($content);
        
        return $tpl->fetch($this->tpl_prefix . 'showContent.php');
    }
    
    
    /**
     * Returns website index page HTML (from script 'index.php' in script dir).
     *
     * @return Index page HTML.
     */
    function showIndex()
    {
        return $this->showScript('index');
    }
    
    
    /**
     * Returns page HTML (from page file in page directory).
     *
     * @method public showPage
     * @param optional string $page_name Page file name (relative to {@link $this->page_dir} directory.
     * @return string HTML of the page.
     */
    function showPage($page_name = NULL)
    {
        if ($page_name === NULL)
            $page_name = @$_REQUEST['page'];
        
        $file_name = $this->page_dir.'/'.$page_name.'.html';
        
        if (!file_exists($file_name))
        {
            return $this->showError(404);
        }
        else
        {
            // 1. get contents
            $handle = fopen ($file_name, 'rb'); 
            $contents = fread ($handle, filesize ($file_name)); 
            fclose ($handle);

            // 2. strip everything outside body tags
            if ($body1_start = stripos($contents, '<body'))
            {
                $body1_end = stripos($contents, '>', $body1_start) + 1;
                $body2_start = stripos($contents, '</body>');
                
                $contents = substr($contents, $body1_end, ($body2_start - $body1_end) );
            }
            
            if (preg_match('/<h1>([^<]+)<\/h1>/im', $contents, $matches))
                $contents = array('title' => $matches[1], 'html' => $contents);
            
            // 3. return contents
            return $this->showContent($contents);
        }
    }
    
    
    /**
     * Returns script HTML (from script file in script directory).
     *
     * @param optional string $script_name Script file name (relative to {@link $this->script_dir} directory.
     * @return string HTML of the generated page.
     */
    function showScript($script_name = NULL)
    {
        if ($script_name === NULL)
            $script_name = @$_REQUEST['script'];
        
        $file_name = $this->script_dir.'/'.$script_name.'.php';
        
        if (!file_exists($file_name))
        {
            return $this->showError(404);
        }
        else
        {
            $e = error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
            ob_start();
            include($file_name);
            $content = ob_get_contents();
            ob_end_clean();
            error_reporting($e);
            
            return $this->showContent($content);
        }
    }
}

?>
