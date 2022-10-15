<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;

class plgSystemCosn extends CMSPlugin
{
    protected $_lang;
    protected $database;
    protected $user;

	function __construct (&$subject, $params)
	{
		parent::__construct($subject, $params);
		$this->_plugin = PluginHelper::getPlugin('system', 'cosn');
		
		$joomla_version = new Version();
		$this->database = Factory::getContainer()->get('DatabaseDriver');
		$this->user = Factory::getApplication()->getIdentity();
		
		$this->_lang = Factory::getApplication()->getLanguage();
		$rc = $this->_lang->load('plg_system_cosn', JPATH_ADMINISTRATOR, null, true, true);
		if (! $rc)
		$this->_lang->load('plg_system_cosn', JPATH_ADMINISTRATOR, "en-GB");
	}

    function onAjaxCosn ()
    {
        $plugin = PluginHelper::getPlugin('system', 'cosn');
        $pluginParams = json_decode($plugin->params);

        $app = Factory::getApplication();
        $input = $app->input;

        $id = $input->get('id');

        if (empty($id) || ! is_numeric($id))
            return false;

        if ($pluginParams->secure_parm == 1 || $input->get('secure_parm') == '1')
        {
            foreach ($_GET as $key => $value)
            {
                $check_json = json_decode($_GET[$key]);
                if (json_last_error() !== 0)
                    $_GET[$key] = $input->get($key);
                else
                    $_GET[$key] = strip_tags($_GET[$key]);
                // echo "<br>" . $_GET[$key];
            }

            foreach ($_POST as $key => $value)
            {
                $check_json = json_decode($_POST[$key]);
                if (json_last_error() !== 0)
                    $_POST[$key] = $input->get($key);
                else
                    $_POST[$key] = strip_tags($_POST[$key]);
            }

            foreach ($_REQUEST as $key => $value)
            {
                $check_json = json_decode($_REQUEST[$key]);
                if (json_last_error() !== 0)
                    $_REQUEST[$key] = $input->get($key);
                else
                    $_REQUEST[$key] = strip_tags($_REQUEST[$key]);
            }
        }

        if ($this->user->id == 0)
            $this->database->setQuery("select source from #__cosn where id = $id and published = 1 and access=0");
        else
            $this->database->setQuery("select source from #__cosn where id = $id and published = 1");

        $result = $this->database->loadRow();
        if (empty($result[0]))
        {
            echo "<div style='color:red;'>&nbsp;" . Text::sprintf('PLG_ERROR_RECORD_URL', $id) . "</div>";
            return false;
        }

        $file = JPATH_ROOT . "/" . $result[0];
        if (is_file($file))
            require_once $file;
        else
        {
            echo "<div style='color:red;'>&nbsp;" . Text::sprintf('PLG_ERROR_FILE', $file) . "</div>";
            return false;
        }
    }

    function onContentPrepare ($context, &$row, &$params, $page = 0)
    {
        if (StringHelper::strpos($row->text, '{cosn') === false)
            return true;
        else
        {
            $matches = array();
            $regex = '/{(cosn)\s*(.*?)}/i';
            preg_match_all($regex, $row->text, $matches);
            $count = count($matches[0]);
            if ($count)
            {
                $row->text = $this->replaceContent($row->text);
                return true;
            }
            else
            {
                return true;
            }
        }
    }

    function replaceContent ($content)
    {
        $plugin = PluginHelper::getPlugin('system', 'cosn');
        $pluginParams = json_decode($plugin->params);

        $regex = '/{(cosn)\s*(.*?)}/i';
        $continuesearching = true;
        while ($continuesearching)
        { // Nesting loop
          // find all instances of $regex (i.e. cosn) in an article and put
          // them
          // in $matches
            $matches = array();
            $matches_found = preg_match_all($regex, $content, $matches, PREG_SET_ORDER);
            if ($matches_found)
            {
                // cycle through all cosn instancies. Put text into $dummy[2]
                foreach ($matches as $dummy)
                {
                    // read arguments contained in [] from $dummy[2] and put
                    // them into the array $cosn
                    $mms = array();
                    $cosn = "";
                    preg_match_all('/\[.*?\]/', $dummy[2], $mms);
                    if ($mms)
                    { // at the least one argument found
                        foreach ($mms as $i => $mm)
                        {
                            $cosn = preg_replace("/\[|]/", "", $mm);
                        }
                    }

                    // Following syntax {cosn [storage_source][arg1]...[argN]}
                    $storage_source = $this->getStorageSource(trim(array_shift($cosn)), $pluginParams->default_path_prefix);
                    $output = '';

                    if ($storage_source == '')
                    { // if nothing to show
                        $output = '<div style="color:red;">&nbsp:' . Text::_('PLG_ERROR_CONTENT') . '</div>';
                    }
                    else
                    { // buffer output
                        ob_start();
                        if (is_int($storage_source))
                        { // it is record id
                            $code_stored = $this->getCodeStored($storage_source);
                            if ($code_stored != null)
                            { // include custom script written
                                eval('?>' . $code_stored);
                            }
                            else
                            {
                                $output = '<div style="color:red;">&nbsp;' . Text::sprintf('PLG_ERROR_RECORD', $storage_source) . '</div>';
                            }
                        }
                        else
                        { // it is file
                            if ($pluginParams->hide_code == 1)
                            {
                                $output = '<div style="color:red;">&nbsp;' . Text::sprintf('PLG_ERROR_DBONLY', $storage_source) . '</div>';
                                // return true;
                            }
                            elseif (is_readable($storage_source))
                            {
                                include ($storage_source); // include file
                            }
                            else
                            {
                                echo "</br> heyy";
                                $output = '<div style="color:red;">&nbsp;' . Text::sprintf('PLG_ERROR_FILE', $storage_source) . '</div>';
                            }
                        }
                        if ($output == '')
                        { // if there are no errors
                          // $output = str_replace( '$' , '\$' ,
                          // ob_get_contents()); fixed joomla bug
                            $output = ob_get_contents();
                        }
                        ob_end_clean();
                    }

                    // final replacement of $regex (i.e. {cosn [][]}) in
                    // $article->text by $output
                    $content = preg_replace($regex, $output, $content, 1);
                }
            }
            else
            {
                $continuesearching = false;
            }
        }

        return $content;
    }

    function onAfterRender ()
    {
        $app = Factory::getApplication();

        if ($app->getDocument()->getType() !== 'html')
            return true;

        if ($app->isClient('administrator'))
            return true;

        if ($app->input->get('layout') == 'edit')
            return true;

        $content = $app->getBody();
        $new_content = $this->replaceContent($content);
        $app->setBody($new_content);
    }

    function getCodeStored ($source)
    { // returns code stored in the database or null.
        try
        {
            if ($this->user->id == 0)
                $this->database->setQuery("select custom_code from #__cosn where id = $source and published = 1 and access=0");
            else
                $this->database->setQuery("select custom_code from #__cosn where id = $source and published = 1");
        }
        catch (mysqli_sql_exception $e)
        {
            echo '<div style="color:red;">&nbsp;db- error </div>';
            return null;
        }
        return $this->database->loadResult();
    }

    function getStorageSource ($source, $pathprefix)
    { // returns filepathname or a record id or ""
        $storage = trim($source);
        if ($storage != "")
        {
            if ($id = substr(strchr($storage, "*"), 1))
            { // if record id return it
                return (int) $id;
            }
            else
            { // else return filepathname
                if ($pathprefix == '')
                    return $storage;
                else
                {
                    // echo "<br>" . $pathprefix . " code = " . $storage;
                    return $pathprefix . $storage;
                }
            }
        }
        else
        { // else return ""
            return '';
        }
    }
}
