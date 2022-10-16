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

class CosnViewApps extends \Joomla\CMS\MVC\View\HtmlView
{

    protected $items;

    protected $pagination;

    protected $state;

    public function display ($tpl = null)
    {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors));
        }

        CosnHelper::addSubmenu('apps');

        $this->addToolbar();

        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    protected function addToolbar ()
    {
        $state = $this->get('State');
        $canDo = CosnHelper::getActions();

        ToolbarHelper::title(Text::_('COM_COSN_ADM_TITLE_APPS'), 'apps.png');

        // Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/cosn_code';

        if (file_exists($formPath))
        {
            if ($canDo->get('core.create'))
            {
                ToolbarHelper::addNew('cosn_code.add', 'JTOOLBAR_NEW');

                if (isset($this->items[0]))
                {
                    ToolbarHelper::custom('apps.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
                }
            }

            if ($canDo->get('core.edit') && isset($this->items[0]))
            {
                ToolbarHelper::editList('cosn_code.edit', 'JTOOLBAR_EDIT');
            }
        }

        if ($canDo->get('core.edit.state'))
        {
            if (isset($this->items[0]->state))
            {
                ToolbarHelper::divider();
                ToolbarHelper::custom('apps.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
                ToolbarHelper::custom('apps.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            }
            elseif (isset($this->items[0]))
            {
                // If this component does not use state then show a direct
                // delete button as we can not trash
                ToolbarHelper::deleteList('', 'apps.delete', 'JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state))
            {
                ToolbarHelper::divider();
                ToolbarHelper::archiveList('apps.archive', 'JTOOLBAR_ARCHIVE');
            }

            if (isset($this->items[0]->checked_out))
            {
                ToolbarHelper::custom('apps.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }

        // Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state))
        {
            if ($state->get('filter.state') == - 2 && $canDo->get('core.delete'))
            {
                ToolbarHelper::deleteList('', 'apps.delete', 'JTOOLBAR_EMPTY_TRASH');
                ToolbarHelper::divider();
            }
            elseif ($canDo->get('core.edit.state'))
            {
                ToolbarHelper::trash('apps.trash', 'JTOOLBAR_TRASH');
                ToolbarHelper::divider();
            }
        }

        if ($canDo->get('core.admin'))
        {
            ToolbarHelper::preferences('com_cosn');
        }

        // Set sidebar action - New in 3.0
        JHtmlSidebar::setAction('index.php?option=com_cosn&view=apps');
    }

    protected function getSortFields ()
    {
        return array(
                'a.id' => Text::_('JGRID_HEADING_ID'),
                'a.title' => Text::_('COM_COSN_ADM_APPS_TITLE'),
                'a.source' => Text::_('COM_COSN_ADM_APPS_SOURCE'),
                'a.access' => Text::_('COM_COSN_ADM_APPS_ACCESS'),
                'a.published' => Text::_('COM_COSN_ADM_APPS_PUBLISHED')
        );
    }

    public function getState ($state)
    {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
    }
}
