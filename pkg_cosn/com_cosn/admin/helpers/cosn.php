<?php
/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Version;

class cosnActions
{
    
    public function __construct ($properties = null)
    {
        if ($properties !== null)
            $this->setProperties($properties);
    }
    
    public function get ($property, $default = null)
    {
        if (isset($this->$property))
            return $this->$property;
            
            return $default;
    }
}

class CosnHelper
{
    
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_COSN_ADM_TITLE_APPS'),
			'index.php?option=com_cosn&view=apps',
			$vName == 'apps'
		);

	}

	public static function getFiles($pk, $table, $field)
	{
	   $joomla_version = new Version();
	   if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
	       $db = Factory::getContainer()->get('DatabaseDriver');
	   else
	       $this->database = Factory::getDbo();
	       
	   $query = $this->db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	public static function getActions()
	{
		$result = new cosnActions;
		$assetName = 'com_cosn';
		
		$joomla_version = new Version();
		if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
		    $user = Factory::getApplication()->getIdentity();
		else
		    $user = Factory::getUser();

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->$action = $user->authorise($action, $assetName);
		}

		return $result;
	}
}

