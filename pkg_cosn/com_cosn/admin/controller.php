<?php
/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
defined('_JEXEC') or die();

use \Joomla\CMS\Factory;

class CosnController extends \Joomla\CMS\MVC\Controller\BaseController
{

    public function display ($cachable = false, $urlparams = false)
    {
        $view = Factory::getApplication()->input->getCmd('view', 'cosn_codes');
        Factory::getApplication()->input->set('view', $view);

        parent::display($cachable, $urlparams);

        return $this;
    }
}
