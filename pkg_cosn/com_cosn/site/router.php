<?php
/*
 *  @package   com_cosn
 *  @version   Version 0.9.0
 *  @author    Alexander Gribkov, Joachim Schmidt - sallecta@yahoo.com
 *  @copyright (C) 2021 Alexander Gribkov, Joachim Schmidt. All rights reserved.
 *  @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * change activity:
 */
defined('_JEXEC') or die('Restricted access');
use \Joomla\CMS\Factory;
use \Joomla\CMS\Version;

function CosniBuildRoute (&$query)
{
    $joomla_version = new Version();
    if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
        $db =  Factory::getContainer()->get('DatabaseDriver');
    else
        $db = Factory::getDBO();
   
     $segments = array();

    if (isset($query['fileid']))
    {
        $db->setQuery('select title from #__cosn where id = ' . $query['fileid']);
        $segments[] = $db->loadResult();
        unset($query['fileid']);
    }

    return $segments;
}

function CosniParseRoute ($segments)
{
    $joomla_version = new Version();
    if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
        $db =  Factory::getContainer()->get('DatabaseDriver');
     else
        $db = Factory::getDBO();
     
    $vars = array();

    $db->setQuery('select id from #__cosn where title = "' . $segments[0] . '"');
    $vars['fileid'] = $db->loadResult();

    return $vars;
}