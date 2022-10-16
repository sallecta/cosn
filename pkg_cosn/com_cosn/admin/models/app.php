<?php
/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Version;
use \Joomla\CMS\Language\Text;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Associations;

/**
 * Cosn model.
 *
 * @since  1.6
 */
class CosnModelApp extends \Joomla\CMS\MVC\Model\AdminModel
{

    protected $text_prefix = 'COM_COSN';

    public $typeAlias = 'com_cosn.app';

    protected $item = null;

    public function getTable ($type = 'App', $prefix = 'CosnTable', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm ($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_cosn.app', 'app', array(
                'control' => 'jform',
                'load_data' => $loadData
        ));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }
    
    public function setError ($error_string)
    {
        $app = Factory::getApplication();
        $app->enqueueMessage($error_string);
    }

    function save ($data = array())
    {
        $app = Factory::getApplication();
        $input = Factory::getApplication()->input;
        $formData = $input->getVar('jform', array(), 'post', 'array');
        $source = $formData['source'];
        $my_id = $formData['id'];
        $title = $formData['title'];
        $access = $formData['access'];
        $published = $formData['published'];

        $upload_file = JPATH_ROOT . '/' . $source;
        if (file_exists($upload_file))
        {
            $handle = fopen($upload_file, "r");
            $code = fread($handle, filesize($upload_file));
            fclose($handle);
        }
        else
        {
            $app->enqueueMessage(Text::sprintf('COSN_ERROR_FILE', $upload_file), 'error');
            return false;
        }

        $joomla_version = new Version();
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
            $db = Factory::getContainer()->get('DatabaseDriver');
        else
            $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        if ($my_id > 0)
        {
            $fields = array(
                    $db->quoteName('title') . ' = ' . $db->quote($title),
                    $db->quoteName('source') . ' = ' . $db->quote($source),
                    $db->quoteName('custom_code') . ' = ' . $db->quote($code),
                    $db->quoteName('access') . ' = ' . $access,
                    $db->quoteName('published') . ' = ' . $published
            );

            $conditions = array(
                    $db->quoteName('id') . ' = ' . $my_id
            );

            $query->update($db->quoteName('#__cosn'))
                ->set($fields)
                ->where($conditions);
            $result = $db->setQuery($query);
            $db->execute();
        }

        // $dispatcher = JEventDispatcher::getInstance();
        $table = $this->getTable();
        $context = $this->option . '.' . $this->name;

        if (! empty($data['tags']) && $data['tags'][0] != '')
        {
            $table->newTags = $data['tags'];
        }

        $key = $table->getKeyName();
        $pk = (! empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        // Allow an exception to be thrown.
        try
        {
            // Load the row if saving an existing record.
            if ($pk > 0)
            {
                $table->load($pk);
                $isNew = false;
            }

            // Bind the data.
            if (! $table->bind($data))
            {
                $this->setError("bind data failed");

                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (! $table->check())
            {
                $this->setError("table check failed");

                return false;
            }

            // Trigger the before save event.
            $result = Factory::getApplication()->triggerEvent($this->event_before_save, array(
                    $context,
                    $table,
                    $isNew,
                    $data
            ));
            // $result = $dispatcher->trigger($this->event_before_save,
            // array($context, $table, $isNew, $data));

            if (in_array(false, $result, true))
            {
                $this->setError("wrong data");

                return false;
            }

            // Store the data.
            if (! $table->store())
            {
                $this->setError("store data failed");

                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the after save event.
            Factory::getApplication()->triggerEvent($this->event_after_save, array(
                    $context,
                    $table,
                    $isNew,
                    $data
            ));
            // $dispatcher->trigger($this->event_after_save, array($context,
            // $table, $isNew, $data));
        }
        catch (\Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        if (isset($table->$key))
        {
            $this->setState($this->getName() . '.id', $table->$key);
        }

        $this->setState($this->getName() . '.new', $isNew);

        if ($this->associationsContext && Associations::isEnabled() && ! empty($data['associations']))
        {
            $associations = $data['associations'];

            // Unset any invalid associations
            $associations = ArrayHelper::toInteger($associations);

            // Unset any invalid associations
            foreach ($associations as $tag => $id)
            {
                if (! $id)
                {
                    unset($associations[$tag]);
                }
            }

            // Show a warning if the item isn't assigned to a language but we
            // have associations.
            if ($associations && $table->language === '*')
            {
                Factory::getApplication()->enqueueMessage(Text::_(strtoupper($this->option) . '_ERROR_ALL_LANGUAGE_ASSOCIATED'), 'warning');
            }

            // Get associationskey for edited item
            $joomla_version = new Version();
            if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
                $db = Factory::getContainer()->get('DatabaseDriver');
             else
                $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select($db->qn('key'))
                ->from($db->qn('#__associations'))
                ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext))
                ->where($db->qn('id') . ' = ' . (int) $table->$key);
            $db->setQuery($query);
            $old_key = $db->loadResult();

            // Deleting old associations for the associated items
            $query = $db->getQuery(true)
                ->delete($db->qn('#__associations'))
                ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext));

            if ($associations)
            {
                $query->where('(' . $db->qn('id') . ' IN (' . implode(',', $associations) . ') OR ' . $db->qn('key') . ' = ' . $db->q($old_key) . ')');
            }
            else
            {
                $query->where($db->qn('key') . ' = ' . $db->q($old_key));
            }

            $db->setQuery($query);
            $db->execute();

            // Adding self to the association
            if ($table->language !== '*')
            {
                $associations[$table->language] = (int) $table->$key;
            }

            if (count($associations) > 1)
            {
                // Adding new association for these items
                $key = md5(json_encode($associations));
                $query = $db->getQuery(true)->insert('#__associations');

                foreach ($associations as $id)
                {
                    $query->values(((int) $id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
                }

                $db->setQuery($query);
                $db->execute();
            }
        }

        if ($my_id == '')
        {
            $sql = "select max(id) from #__cosn";
            $result = $db->setQuery($sql);
            $row = $db->loadRow();
            $id = $row[0];

            $fields = array(
                    $db->quoteName('custom_code') . ' = ' . $db->quote($code)
            );
            $conditions = array(
                    $db->quoteName('id') . ' = ' . $id
            );

            $query->update($db->quoteName('#__cosn'))
                ->set($fields)
                ->where($conditions);
            $result = $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    protected function loadFormData ()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_cosn.edit.app.data', array());

        if (empty($data))
        {
            if ($this->item === null)
            {
                $this->item = $this->getItem();
            }

            $data = $this->item;
        }

        return $data;
    }

    public function getItem ($pk = null)
    {
        if ($item = parent::getItem($pk))
        {
            if (isset($item->params))
            {
                $item->params = json_encode($item->params);
            }
            // Do any procesing on fields here if needed
        }

        return $item;
    }

    public function duplicate (&$pks)
    {
        
        $joomla_version = new Version();
        if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
            $user = Factory::getApplication()->getIdentity();
        else
            $user = Factory::getUser();

        // Access checks.
        if (! $user->authorise('core.create', 'com_cosn'))
        {
            throw new Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
        }

        $context = $this->option . '.' . $this->name;

        $table = $this->getTable();

        foreach ($pks as $pk)
        {

            if ($table->load($pk, true))
            {
                // Reset the id to create a new record.
                $table->id = 0;

                if (! $table->check())
                {
                    throw new Exception("table check failed");
                }

                // Trigger the before save event.
                $result = Factory::getApplication()->triggerEvent($this->event_before_save, array(
                        $context,
                        &$table,
                        true
                ));

                if (in_array(false, $result, true) || ! $table->store())
                {
                    throw new Exception("table store failed");
                }

                // Trigger the after save event.
                Factory::getApplication()->triggerEvent($this->event_after_save, array(
                        $context,
                        &$table,
                        true
                ));
            }
            else
            {
                throw new Exception("table load failed");
            }
        }

        // Clean cache
        $this->cleanCache();

        return true;
    }

    protected function prepareTable ($table)
    {
        jimport('joomla.filter.output');

        if (empty($table->id))
        {
            // Set ordering to the last item if not set
            if (@$table->ordering === '')
            {
                
                $joomla_version = new Version();
                if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
                    $db = Factory::getContainer()->get('DatabaseDriver');
                else
                    $db = Factory::getDbo();
                
                $db->setQuery('SELECT MAX(ordering) FROM #__cosn');
                $max = $db->loadResult();
                $table->ordering = $max + 1;
            }
        }
    }
}
