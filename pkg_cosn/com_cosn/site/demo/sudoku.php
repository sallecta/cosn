	<?php
/**
 # jumiSudoku.php
 #
 # Run Sudoku app with Joomla's extension "cosn"
 #
 # @author Alexander Gribkov, Joachim Schmidt - sallecta@yahoo.com
 # @copyright Copyright (C) 2021 Alexander Gribkov, Joachim Schmidt. All rights reserved.
 # @license	 http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # @formatter:off
 #
 # change activity:
 */
    defined ( '_JEXEC' ) or die ( 'can only run under Joomla with extension &quot;cosn&quot;!' );

    use \Joomla\CMS\Factory;
    use \Joomla\CMS\HTML\HTMLHelper;
    use \Joomla\CMS\Uri\Uri;
    use \Joomla\CMS\Version;
    
    HTMLHelper::_('jquery.framework');
    $base = JPATH_ROOT . "/components/com_cosn/demo";
    $check_url =  URI::base () . "/index.php?option=com_ajax&plugin=cosn&group=system&format=raw&id=2";
    $url =  URI::root (true) . "/index.php?option=com_ajax&plugin=cosn&group=system&format=raw&id=2";
    
    $check = file_get_contents($check_url);
    if (strpos($check, "cosn-Ajax"))
    {
      echo "<b>Error: </b> " . $check;
      exit;
    }
    
    $joomla_version = new Version();
    if (version_compare($joomla_version->getShortVersion(), '4.0', '>='))
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle("sudoku", Uri::root(true) . "media/com_cosn/css/style.css",['position' => 'after'], [], ['webcomponent.joomla-alert']);
        $wa->registerAndUseScript("sudoku1", Uri::root(true) . "media/com_cosn/js/sudoku.min.js");
        $wa->registerAndUseScript("sudoku2", Uri::root(true) . "media/com_cosn/js/printThis.js");
    }
    else
    {
        $document = Factory::getApplication()->getDocument();
        $document->addStylesheet(Uri::root(true) . "/media/com_cosn/css/style.css");
        $document->addScript(Uri::root(true) . "/media/com_cosn/js/sudoku.min.js");
        $document->addScript(Uri::root(true) . "/media/com_cosn/js/printThis.js");
    }
     
    $text = parse_ini_file ($base . "/sudoku.ini", true );
    $notes =  parse_ini_file ($base . "/" . $text['notes_ini'], true);
    $valid_languages = $text['languages'];
     
    $lang = "en";
    if (isset ($_REQUEST['lang']))
    {
      $lang = $_REQUEST['lang'];
      if (strpos($valid_languages, $lang) === false)
        $lang = "en";
    }
   
    $text = $text[$lang];
    $notes = $notes[$lang];
      
    if (isset ( $_REQUEST ['sudoku'] ) && is_numeric( $_REQUEST ['sudoku'] ))
	   $parm = "&sudoku=" . intval ( $_REQUEST ['sudoku'] );
	else
	   $parm = "&sudoku=noid";
	   
	if (isset ( $_REQUEST ['level'] ) && is_numeric( $_REQUEST ['level'] ))
	   $parm .= "&level=" . intval ( $_REQUEST ['level'] );
	
?>
<div id="language" style="display: none;"><?php echo $lang; ?></div>
<div id="sudoku-container" class="sudoku-container">
 <div class="bubble">
  <div id="sudokuid" class="rectangle">
   <h2>Sudoku -</h2>
  </div>
  <div class="triangle-l"></div>
  <div class="triangle-r"></div>
  <div class="sudoku-info">
   <div id="sudoku" class="center">
    <div id='msgbox_content' style='display: none'><?php echo $text['success_msg']; ?></div>
<?php
	$html = "\n<table class='sudoku' id='sudoku-grid'>";
	$count = 0;
	$check = "onkeyup='checkValue($count, true, true)'";
	for ($x = 0; $x < 9; $x++)
	{
		$html .= "\n<tr>";
		for ($y = 0; $y < 9; $y++)
		{
			$class = "cell";
			if ($y == 2 || $y == 5)
				$class = "cell cell-right";
			
			if ($x == 2 || $x == 5)
				$class = "cell cell-bottom";
			
			if (($y == 2 && $x == 2) || ($y == 2 && $x == 5))
				$class = "cell cell-both";
			
			if (($y == 5 && $x == 2) || ($y == 5 && $x == 5))
				$class = "cell cell-both";

			$check = "onkeyup='checkValue($count, true, true)'";

			$html .= "\n<td class='" . $class . "'>";
			$html .= "\n <div id='candidates" . $count . "' class='overlay'></div>";
			$html .= "\n <input type='text' id='i" . $count . "' name='i" . $count . "' " .
				  "value='' size='1' maxlength='1' class='cell' $check />\r\n";
			$html .= "</td>";
			$count ++;
		}
		$html .= "</tr>";
	}
	$html .= "\n</table>";
	echo $html;
?>
<br />
    <form id="form" class="sudoku-form" name="form" action="none">
     <input type="hidden" name="sudoku" value="" /> <input
      class="sudokubutton" id="button1" type="button"
      value="<?php echo $text['new_sudoku']; ?>"
      onclick="getPuzzle('<?php echo $url . "','null"; ?>')" /> <input
      class="sudokubutton" id="button2" type="button"
      value="<?php echo $text['solution']; ?>" onclick="showSolution()" />
     <input class="sudokubutton" id="button3" type="button"
      value="<?php echo $text['print']; ?>"
      onclick="jQuery('#sudoku-container').printThis({ loadCSS: '<?php  Uri::root(true); ?>/media/com_cosn/css/print.css' });" />
     <input type="checkbox" id="help" name="help" value="0"
      onclick="checkValues()" />&nbsp;<?php echo $text['help']; ?>&nbsp;
<input type="checkbox" id="check" name="check" value="1"
      checked="checked" onclick="checkValues()" />&nbsp;<?php echo $text['check']; ?>&nbsp;
<br /> <br /> <b style="padding-left: 40px;">Level:</b>&nbsp; <input
      type="radio" id="level0" name="level" value="0" checked="checked" />&nbsp;<?php echo $text['level0']; ?>&nbsp;
<input type="radio" id="level1" name="level" value="1" />&nbsp;<?php echo $text['level1']; ?>&nbsp;
<input type="radio" id="level2" name="level" value="2" />&nbsp;<?php echo $text['level2']; ?>&nbsp;
</form>
    <div id="messages" class="sudoku-messages">&nbsp;</div>
   </div>

<?php
$script = "\njQuery(document).ready(function() {";
$script .= "\n   getPuzzle('" .  $url . "','" .$parm . "');";
$script .= "\n});";
$document->addCustomTag("<script type='text/javascript'>" . $script . "</script>");
?>

</div>
 </div>
</div>
<div id="notes">
<?php echo $notes['notes']; ?>
</div>
