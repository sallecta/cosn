<?php
/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
defined('_JEXEC') or die();
use \Joomla\CMS\Factory;
use \Joomla\CMS\Version;

$joomla_version = new Version();
if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
{
    class FieldsHelper extends \Joomla\Component\Fields\Administrator\Helper\FieldsHelper
    {}
}
else
    JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

class CosnModelApps extends \Joomla\CMS\MVC\Model\ListModel
{

    public function __construct ($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                    'id',
                    'a.id',
                    'title',
                    'a.title',
                    'source',
                    'a.source',
                    'custom_code',
                    'a.custom_code',
                    'access',
                    'a.access',
                    'published',
                    'a.published'
            );
        }

        parent::__construct($config);
    }

    protected function populateState ($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState("a.id", "ASC");

        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
   
        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts)
        {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }

    protected function getStoreId ($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    protected function getListQuery ()
    {
        // Create a new query object.
        $joomla_version = new Version();
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
            $db = Factory::getContainer()->get('DatabaseDriver');
         else
            $db = Factory::getDbo();
        
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'DISTINCT a.*'));
        $query->from('#__cosn AS a');

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (! empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            else
            {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.source LIKE ' . $search . ' )');
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', "a.id");
        $orderDirn = $this->state->get('list.direction', "ASC");

        if ($orderCol && $orderDirn)
        {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems ()
    {
        $items = parent::getItems();

        return $items;
    }
}
