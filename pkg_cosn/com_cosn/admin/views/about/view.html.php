<?php

/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Toolbar\ToolbarHelper;

class CosnViewAbout extends \Joomla\CMS\MVC\View\HtmlView
{

 function display($tpl = null) 
 {
  parent::display($tpl);
 }
}
