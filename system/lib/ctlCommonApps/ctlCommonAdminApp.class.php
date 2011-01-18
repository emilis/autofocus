<?php
/*
    Abstract class for MVC controllers that output pages through Site.Admin.

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

require_once('ctlCommonSiteApp.class.php');

/**
 *
 */
class ctlCommonAdminApp extends ctlCommonSiteApp
{
    /**
     *
     */
    function showContent($template, $vars = array())
    {
        $error = $this->getFlash('error');
        $message = $this->getFlash('message');
        
        $tpl = &loadObject('ctlTemplate');
        
        $tpl->assign(array(
            'error' => $error,
            'message' => $message,
            'page_name' => $this->name,
            'page_config' => $this->config,
            ));
        
        $tpl->assign($vars);
        
        $content = $tpl->fetchArray($this->tpl_prefix . $template . '.php');
        $content['error'] = $error;
        $content['message'] = $message;
        
        $admin = &loadObject('Site.Admin');
        return $admin->showContent($content);
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
        
        $admin = &loadObject('Site.Admin');
        return $admin->showContent($content);
    }
    function showAdminContent($content) { return $this->showSiteContent($content); }
    
    /**
     *
     */
    function checkPermission($permission = NULL)
    {
        $admin = &loadObject('Site.Admin');
        if ($error = $admin->checkLogin())
            return $error;
        
        if ($permission === NULL)
            $permission = $this->name;
        
        if ($permission)
        {
            $security = &loadObject('Security');
            if (!$security->isAllowed($permission))
                return $this->showError(403);
        }
    }
}

?>
