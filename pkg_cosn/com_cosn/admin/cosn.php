<?php
/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
//namespace com_cosn\administrator;
defined('_JEXEC') or die();

use \Joomla\CMS\MVC\Controller\AdminController;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Version;

$joomla_version = new Version();
if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
    $user = Factory::getApplication()->getIdentity();
else
    $user = Factory::getUser();

if (! $user->authorise('core.manage', 'com_cosn'))
{
    throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Cosn', JPATH_COMPONENT_ADMINISTRATOR);

$controller = AdminController::getInstance('Cosn');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
