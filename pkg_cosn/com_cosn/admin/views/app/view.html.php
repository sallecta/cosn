<?php

/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

use \Joomla\CMS\Factory;
use \Joomla\CMS\Version;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Toolbar\ToolbarHelper;

class CosnViewApp extends \Joomla\CMS\MVC\View\HtmlView
{
    protected $state;

    protected $item;

    protected $form;

    public function display ($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar ()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $joomla_version = new Version();
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
            $user = Factory::getApplication()->getIdentity();
         else
            $user = Factory::getUser();
    
        $isNew = ($this->item->id == 0);

        if (isset($this->item->checked_out))
        {
            $checkedOut = ! ($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
        }
        else
        {
            $checkedOut = false;
        }

        $canDo = CosnHelper::getActions();

        ToolBarHelper::title(Text::_('COM_COSN_ADM_TITLE_APP'), 'app.png');

        // If not checked out, can save the item.
        if (! $checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
        {
            ToolBarHelper::apply('app.apply', 'JTOOLBAR_APPLY');
            ToolBarHelper::save('app.save', 'JTOOLBAR_SAVE');
        }

        if (! $checkedOut && ($canDo->get('core.create')))
        {
            ToolBarHelper::custom('app.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        }

        // If an existing item, can save to a copy.
        if (! $isNew && $canDo->get('core.create'))
        {
            ToolBarHelper::custom('app.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
        }

        if (empty($this->item->id))
        {
            ToolBarHelper::cancel('app.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            ToolBarHelper::cancel('app.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
