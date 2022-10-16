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
use \Joomla\Registry\Registry;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Table\Table as Table;

class CosnTableapp extends Table
{

    public function __construct (&$db)
    {
        parent::__construct('#__cosn', 'id', $db);

        $this->setColumnAlias('published', 'state');
    }

    public function bind ($array, $ignore = '')
    {
        $joomla_version = new Version();
        $date = Factory::getDate();
        $task = Factory::getApplication()->input->get('task');
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
            $user = Factory::getApplication()->getIdentity();
         else
            $user = Factory::getUser();

        if ($array['id'] == 0 && empty($array['modified_by']))
        {
            $array['modified_by'] = $user->id;
        }

        if ($task == 'apply' || $task == 'save')
        {
            $array['modified_by'] = $user->id;
        }

        if (isset($array['params']) && is_array($array['params']))
        {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        if (isset($array['metadata']) && is_array($array['metadata']))
        {
            $registry = new Registry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
        }

        if (! $user->authorise('core.admin', 'com_cosn.app.' . $array['id']))
        {
            $actions = Access::getActionsFromFile(JPATH_ADMINISTRATOR . '/components/com_cosn/access.xml', "/access/section[@name='app']/");
            $default_actions = Access::getAssetRules('com_cosn.app.' . $array['id'])->getData();
            $array_jaccess = array();

            foreach ($actions as $action)
            {
                if (key_exists($action->name, $default_actions))
                {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
            }

            $array['rules'] = $this->JAccessRulestoArray($array_jaccess);
        }

        // Bind the rules for ACL where supported.
        if (isset($array['rules']) && is_array($array['rules']))
        {
            $this->setRules($array['rules']);
        }

        return parent::bind($array, $ignore);
    }

    private function JAccessRulestoArray ($jaccessrules)
    {
        $rules = array();

        foreach ($jaccessrules as $action => $jaccess)
        {
            $actions = array();

            if ($jaccess)
            {
                foreach ($jaccess->getData() as $group => $allow)
                {
                    $actions[$group] = ((bool) $allow);
                }
            }

            $rules[$action] = $actions;
        }

        return $rules;
    }

    public function check ()
    {
        // If there is an ordering column and this is a new row then get the
        // next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0)
        {
            $this->ordering = self::getNextOrder();
        }

        return parent::check();
    }

    protected function _getAssetName ()
    {
        $k = $this->_tbl_key;

        return 'com_cosn.app.' . (int) $this->$k;
    }

    protected function _getAssetParentId (Table $table = null, $id = null)
    {
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = Table::getInstance('Asset');

        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();

        // The item has the component as asset-parent
        $assetParent->loadByName('com_cosn');

        // Return the found asset-parent-id
        if ($assetParent->id)
        {
            $assetParentId = $assetParent->id;
        }

        return $assetParentId;
    }

    public function delete($pk = null)
    {
        $this->load($pk);
        $result = parent::delete($pk);
        
        return $result;
    }

	
}
