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

require_once JPATH_COMPONENT . '/router.php';
require_once JPATH_COMPONENT . '/controller.php';
   
$controller = CosnController::getInstance('Cosn');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();

