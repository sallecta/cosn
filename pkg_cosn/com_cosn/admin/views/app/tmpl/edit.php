<?php

/**

 * @package    com_cosn
 * @author     Alexander Gribkov, Joachim Schmidt <sallecta@yahoo.com>
 * @copyright  2021 Alexander Gribkov, Joachim Schmidt
 * @license    GNU General Public License Version 2 or later
 */
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Version;

$joomla_version = new Version();
$version = $joomla_version->getShortVersion();

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

$document = Factory::getApplication()->getDocument();
$css = Uri::root() . 'media/com_cosn/css/form.css';
$confirm_css = Uri::root() . 'media/com_cosn/css/jquery-confirm.min.css';
$confirm_js = Uri::root() . 'media/com_cosn/js/jquery-confirm.min.js';
if (version_compare($version, "4.0.0", '<'))
{
  HTMLHelper::_('behavior.formvalidation');
  $document->addStyleSheet($css);
  $document->addStyleSheet($confirm_css);
  $document->addScript($confirm_js);
  $style = ".container { width: 30% !important;}";
  $document->addStyleDeclaration($style);
}
else
{
  HTMLHelper::_('behavior.formvalidator');
  $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
  $wa->registerAndUseStyle("app", $css);
  $wa->registerAndUseStyle("confirm_css", $confirm_css);
  $wa->registerAndUseScript("confirm_js", $confirm_js);
}

?>

<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		
	});

	Joomla.submitbutton = function (task) {
		if (task == 'app.cancel') {
			Joomla.submitform(task, document.getElementById('app-form'));
		}
		else {

            var title = '<?php echo $this->escape(Text::_('COSN_ERROR')); ?>';
			var val=document.getElementById('jform_access').value;
            if ((val > 1 || val < 0) || isNaN(Number(val)) )
            {
               jQuery.alert({ title: title, type: 'red', theme: 'light', content: '<?php echo $this->escape(Text::_('COM_COSN_ADM_VALIDATION_BOOLEAN')); ?>'});
               return false;
            }
            
    		val=document.getElementById('jform_published').value;
    	    if ((val > 1 || val < 0) || isNaN(Number(val)) )
            {
               jQuery.alert({ title: title, type: 'red', theme: 'light', content: '<?php echo $this->escape(Text::_('COM_COSN_ADM_VALIDATION_BOOLEAN')); ?>'});
               return false;
            }
			
			if (task != 'app.cancel' && document.formvalidator.isValid(document.getElementById('app-form'))) {
				
				Joomla.submitform(task, document.getElementById('app-form'));
			}
			else {
                 jQuery.alert({ title: title, type: 'red', theme: 'light', content: '<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>'});
		   }
		}
	}
</script>

<form
	action="<?php echo Route::_('index.php?option=com_cosn&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="app-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'app')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'app', Text::_('COM_COSN_ADM_TAB_APP', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_COSN_ADM_FIELDSET_APP'); ?></legend>
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('source'); ?>
				<?php echo $this->form->renderField('access'); ?>
				<?php echo $this->form->renderField('published'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		

	<?php $this->ignore_fieldsets = array('general', 'info', 'detail', 'jmetadata', 'item_associations', 'accesscontrol'); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
