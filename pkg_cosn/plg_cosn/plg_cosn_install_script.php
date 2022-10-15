<?php
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

class plgsystemCosnInstallerScript
{

    function postflight ($parent, $type)
    {
                 
        // Enable plugin
        $db = Factory::getDbo();
        $sql = " UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='cosn' AND folder='system' LIMIT 1;";
        $db->setQuery($sql);
        $db->execute();
   
    }
}

?>
