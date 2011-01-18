<?php
/*
    Abstract class for MVC controllers that output pages through Site.

    Copyright 2006,2007,2008,2009 Emilis Dambauskas

    This file is part of ctlCommonApps library.

    ctlCommonApps library is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ctlCommonApps library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ctlCommonApps library.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *
 */
class ctlCommonSiteApp
{
    var $name;
    var $config;

    var $session;


    /**
     *
     */
    function ctlCommonSiteApp($obj_name = NULL, $obj_config = NULL)
    {
        $this->name = $obj_name;
        $this->config = $obj_config;

        $this->session = &loadObject('Session');

        $this->tpl_prefix = getModuleDir($this->name) . '/tpl/' . getObjectName($this->name) . '-';
    }

    //------------------------------------------------------------------------

    /**
     *
     */
    function setSessionVar($name, $value)
    {
        return $this->session->setVar($this->name.'-'.$name, serialize($value));
    }
    
    
    /**
     *
     */
    function setFlash($name, $value)
    {
        return $this->setSessionVar($name, $value);
    }
    
    
    /**
     *
     */
    function getSessionVar($name)
    {
        $value = $this->session->getVar($this->name.'-'.$name);
        if ($value)
            return unserialize($value);
        else
            return $value;
    }
    
    
    /**
     *
     */
    function getFlash($name)
    {
        $value = $this->session->getAndUnsetVar($this->name.'-'.$name);
        if ($value)
            return unserialize($value);
        else
            return $value;
    }
    
    
    
    
    /**
     *
     */
    function flashRedirect($method, $vars = array(), $mapper_vars = NULL)
    {
        if (is_array($vars))
        {
            foreach ($vars as $name => $value)
                $this->setFlash($name, $value);
        }

        $mapper = &loadObject('WebMapper');
        return $mapper->redirect("$this->name:$method", $mapper_vars);
    }
    
    
    //------------------------------------------------------------------------
    
    
    /**
     *
     */
    function hasMessages($content = array())
    {
        $result = (
            @$content['error']
            || @$content['message']
            || $this->getSessionVar('error')
            || $this->getSessionVar('message'));
        
        return $result;
    }


    /**
     *
     */
    function getHtml($template, $vars = array())
    {
        $tpl = &loadObject('ctlTemplate');
        
        $tpl->assign(array(
            'page_name' => $this->name,
            'page_config' => $this->config,
            ));
        
        $tpl->assign($vars);
        
        return $tpl->fetch($this->tpl_prefix . $template . '.php');
    }
    
    
    /**
     *
     */
    function getTemplateArray($template, $vars = array())
    {
        $tpl = &loadObject('ctlTemplate');
        
        $tpl->assign(array(
            'page_name' => $this->name,
            'page_config' => $this->config,
            ));
        
        $tpl->assign($vars);
        
        return $tpl->fetchArray($this->tpl_prefix . $template . '.php');
    }
    
    
    /**
     *
     */
    function showSiteContent($content)
    {
        if (!is_array($content))
            $content = array('html' => $content);
        
        $content['error'] = isset($content['error']) ? $content['error'] : $this->getFlash('error');
        $content['message'] = isset($content['message']) ? $content['message'] : $this->getFlash('message');
        
        $site = &loadObject('Site');
        return $site->showContent($content);
    }
    
    
    /**
     *
     */
    function showContent($template, $vars = array())
    {
        return $this->showSiteContent($this->getTemplateArray($template, $vars));
    }


    /**
     *
     */
    function showError($error)
    {
        $site = &loadObject('Site');
        return $site->showError($error);
    }
    
    
    /**
     *
     */
    function createEvent($name, $data = NULL)
    {
        return loadObject('Events')->create("$this->name:$name", $data);
    }
}

?>
