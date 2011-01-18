<?php
/*
    Abstract class for MVC controllers that output data lists (e.g. rows from a database table).

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

class ctlCommonListApp extends ctlCommonSiteApp
{
    function getListFilters($defaultOrder = false)
    {
        // get paging+sorting+filters from session:
        if (!isset($_REQUEST['new_list']) || !$_REQUEST['new_list'])
        {
            $page_size = $this->getFlash('page_size');
            $page = $this->getFlash('page');
            $sorting = $this->getFlash('sorting');
            $filters = $this->getFlash('filters');
        }
        // override paging+sorting+filters from request:
        if (isset($_REQUEST['page_size'])) $page_size = @$_REQUEST['page_size'];
        if (isset($_REQUEST['page'])) $page = @$_REQUEST['page'];
        if (isset($_REQUEST['sorting'])) $sorting = @$_REQUEST['sorting'];
        if (isset($_REQUEST['filters'])) $filters = @$_REQUEST['filters'];
        if (isset($_REQUEST['sorting']) || isset($_REQUEST['filters']))
        {
            $page_size = NULL;
            $page = 0;
        }
        // fix paging+sorting+filters values if necessary:
        if (!isset($page_size)) $page_size = NULL;
        if (!isset($page)) $page = 0;
        if (!isset($sorting)) $sorting = array();
        if (!isset($filters)) $filters = array();
        
        // save paging+sorting+filters back in session:
        $this->setFlash('page_size', $page_size);
        $this->setFlash('page', $page);
        $this->setFlash('sorting', $sorting);
        $this->setFlash('filters', $filters);
        
        // get sorting:
        if (is_array($sorting) && count($sorting))
            $order = $sorting;
        else if ($defaultOrder)
            $order = $defaultOrder;
        
        return array($filters, $sorting, $order, $page, $page_size);
    }
    
    function showList($tpl = 'showList', $tpl_vars = FALSE)
    {
        $list = &$this->getNewList();
        
        list($filters, $sorting, $order, $page, $page_size) = 
            $this->getListFilters();

        if (method_exists($list, 'setActivePage'))
        {
            $list->setActivePage($page);
            if ($page_size)
                $list->setPageSize($page_size);
        }

        $list->filter($filters, $order);
        
        if (!$tpl_vars || !is_array($tpl_vars))
            $tpl_vars = array();
        $tpl_vars['list'] = &$list;
        $tpl_vars['list_sorting'] = $sorting;
        $tpl_vars['list_filters'] = $filters;
        
        return $this->showContent($tpl, $tpl_vars);
    }
}

?>
