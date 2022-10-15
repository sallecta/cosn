<?php
/*
 *  @package   cosn Joomla! Plugin
 *  @version   Version 0.9.0
 *  @author    Alexander Gribkov, Joachim Schmidt - sallecta@yahoo.com
 *  @copyright (C) 2021 Alexander Gribkov, Joachim Schmidt. All rights reserved.
 *  @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * change activity:
 */
defined('_JEXEC') or die('Restricted access');
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Version;

jimport('joomla.application.component.view');

class CosnViewApplication extends \Joomla\CMS\MVC\View\HtmlView
{

    function display ($tpl = null)
    {
        $fileid = Factory::getApplication()->input->post->get('fileid');
        $document = Factory::getApplication()->getDocument();
        $joomla_version = new Version();
       
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
        {
            $database = Factory::getContainer()->get('DatabaseDriver');
            $user = Factory::getApplication()->getIdentity();
        }
        else
        {
            $database = Factory::getDBO();
            $user = Factory::getUser();
        }

        if ($user->id == 0)
            $database->setQuery("select source,title from #__cosn where id = '{$fileid}' and published = 1 and access = 0");
        else
            $database->setQuery("select source,title from #__cosn where id = '{$fileid}' and published = 1");

        $appl = $database->loadObject();

        if (! is_object($appl))
        {
            echo '<div style="color:red;">&nbsp;' . Text::_("COM_ERROR_APP") . '</div>';
            return false;
        }

        $document->setTitle($appl->title);

        if (! empty($appl->path))
        {
            $filepath = JPATH_BASE . '/' . $appl->path;
            if (is_file($appl->path))
            {
                require ($appl->path);
            }
            elseif (is_file($filepath))
                require $filepath;
            else
                echo '<div style="color:red;">&nbsp;' . Text::sprintf('COM_ERROR_FILE', $filepath) . '</div>';
        }
	
			parent::display($tpl);
		}
}
