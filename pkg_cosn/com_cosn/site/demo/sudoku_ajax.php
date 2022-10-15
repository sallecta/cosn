<?php
/**
 # sudoku_ajax.php
 #
 # Sudoku ajax backend:  responds to ajax request and sends a sudoku puzzle
 #
 # @author Alexander Gribkov, Joachim Schmidt - sallecta@yahoo.com
 # @copyright Copyright (C) 2021 Alexander Gribkov, Joachim Schmidt. All rights reserved.
 # @license	 http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 #
 # change activity:
 */
defined('_JEXEC') or die('can only run under Joomla with extension &quot;cosn&quot;!');

$ini_file = JPATH_ROOT . "/components/com_cosn/demo/sudoku.ini";
$settings = parse_ini_file($ini_file, true);

$valid_languages = $settings['languages'];

$lang = "en";
if (isset($_REQUEST['lang']))
{
    $lang = $_REQUEST['lang'];
    if (strpos($valid_languages, $lang) === false)
        $lang = "en";
}
$text = $settings[$lang];

if (isset($_REQUEST['level']) && is_numeric($_REQUEST['level']))
    $level = $_REQUEST['level'];
else
    $level = "0";

$puzzles = JPATH_ROOT . "/components/com_cosn/demo/puzzles.l" . $level;
$max = getAnzSudokus($level, $puzzles);

if (isset($_REQUEST['sudoku']) && is_numeric($_REQUEST['sudoku']))
    $sudokuid = intval($_REQUEST['sudoku']);
else
    $sudokuid = rand(0, $max);

if ($sudokuid < 0 || $sudokuid > $max)
    $sudokuid = rand(0, $max);

echo getSudokuPuzzle($sudokuid, $level, $puzzles, $text);
exit();

function getSudokuPuzzle ($sudokuid, $level, $puzzles, $text)
{
    $result = array();
    $string = "level" . $level;
    $result['puzzle_id'] = $sudokuid . " (" . $text[$string] . ")";
    $result['puzzle'] = getSudoku($sudokuid, $puzzles);
    return json_encode($result);
    // return $result;
}

function getSudoku ($line, $puzzles)
{
    $ls = 82;

    $size = filesize($puzzles);
    $lines = $size / $ls;
    if ($line > $lines)
    {
        $line = $lines - 1;
    }

    $handle = fopen($puzzles, "r");
    $pos = $ls * $line;
    fseek($handle, $pos, SEEK_SET);
    $contents = fread($handle, $ls - 1);
    fclose($handle);
    return $contents;
}

function getAnzSudokus ($level, $puzzles)
{
    $ls = 82;
    $size = filesize($puzzles);
    $lines = $size / $ls;
    return intval($lines) - 1;
}
?>
