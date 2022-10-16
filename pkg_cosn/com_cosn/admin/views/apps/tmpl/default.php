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
use \Joomla\CMS\Version;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;

$joomla_version = new Version();

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Import CSS
$confirm_css = Uri::root() . 'media/com_cosn/css/jquery-confirm.min.css';
$confirm_js = Uri::root() . 'media/com_cosn/js/jquery-confirm.min.js';
$document = Factory::getApplication()->getDocument();
if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
{
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->registerAndUseStyle("cosn-codes1", Uri::root() . 'administrator/components/com_cosn/assets/css/cosn.css');
    $wa->registerAndUseStyle("cosn-codes2", Uri::root() . 'media/com_cosn/css/list.css');
    $wa->registerAndUseStyle("confirm_css", $confirm_css);
    $wa->registerAndUseScript("confirm_js", $confirm_js);
    $user = Factory::getApplication()->getIdentity();
}
else
{
    $document->addStyleSheet(Uri::root() . 'administrator/components/com_cosn/assets/css/cosn.css');
    $document->addStyleSheet(Uri::root() . 'media/com_cosn/css/list.css');
    $document->addStyleSheet($confirm_css);
    $document->addScript($confirm_js);
    $user = Factory::getUser();
    $style = ".container { width: 30% !important;}";
    $document->addStyleDeclaration($style);
}
$userId = $user->id;

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_cosn');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_cosn&task=apps.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'appList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<form action="<?php echo Route::_('index.php?option=com_cosn&view=apps'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>

			<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
			<table class="table table-striped" id="appList">
				<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<?php endif; ?>
					<th width="1%" >
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_COSN_ADM_APPS_SOURCE', 'a.source', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'JFIELD_ACCESS_LABEL', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_COSN_ADM_APPS_PUBLISHED', 'a.published', $listDirn, $listOrder); ?>
					</th>
					
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
                <p><img src='<?php echo  Uri::root(true) ?>/media/com_cosn/images/cosn_logo.png' alt='jphp logo' style='margin-right:8px;float: left;' />
                <?php echo  Text::_('COM_COSN_ADM_INFO'); ?>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_cosn');
					$canEdit    = $user->authorise('core.edit', 'com_cosn');
					$canCheckin = $user->authorise('core.manage', 'com_cosn');
					$canChange  = $user->authorise('core.edit.state', 'com_cosn');
					?>
					<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder) :
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
										  title="<?php echo $disabledLabel ?>">
							<i class="icon-menu"></i>
						</span>
									<input type="text" style="display:none" name="order[]" size="5"
										   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive">
							<i class="icon-menu"></i>
						</span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td >
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>

						
						<td>
							<?php echo $item->id; ?>
						</td>
						<td>
							<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'apps.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_cosn&task=app.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->source; ?>
						</td>
						<td>
							<?php if ($item->access == 1) echo Text::_('COM_COSN_ADM_ACCESS_LABEL'); else echo "Public"; ?>
						</td>
						<td>
							<?php if ($item->published == 1)  echo Text::_('COM_COSN_ADM_PUBLISHED_LABEL'); else echo Text::_('COM_COSN_ADM_HIDDEN_LABEL'); ?>
						</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
<script>
Joomla.submitbutton = function (task) {

	if (task == 'apps.delete') {
  
	  var title = '<?php echo $this->escape(Text::_('COM_COSN_ADM_CONFIRM')); ?>';
      var content = '<?php echo Text::_('COM_COSN_ADM_CONFIRM_DELETE'); ?>';
	  jQuery.confirm({
		    title: title, content: content, type: 'red', theme: 'bootstrap',
		    buttons: {
		    	<?php echo Text::_('COM_COSN_ADM_DELETE'); ?>: function () {
 			        Joomla.submitform(task);
		        },
		        <?php echo Text::_('COM_COSN_ADM_CANCEL'); ?>: function () {
		             return;
                }
		    }
	    });
	 }
	else
	  Joomla.submitform(task);
 }
 
    window.toggleField = function (id, task, field) {
            	
        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        Joomla.submitform(task);

        return false;
    };
</script>