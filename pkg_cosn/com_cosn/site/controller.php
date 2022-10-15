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
jimport('joomla.application.component.controller');

use \Joomla\CMS\Factory;
jimport('joomla.application.component.controller');

class CosnController extends \Joomla\CMS\MVC\Controller\BaseController
{

    public function display ($cachable = false, $urlparams = false)
    {
        Factory::getApplication()->input->post->get('appliation');

        parent::display();

        return $this;
    }
}

