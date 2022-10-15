<?php
defined('_JEXEC') or die;

?>

<h1>About Cosn</h1>
<p>Cosn is Joomla! package that provides creating, managing and running custom apps whithin your CMS installation.</p>
<p>Cosn consists of the following extensions
<ul>
	<li>Cosn component (com_cosn) - apps manager;</li>
	<li>Cosn system plugin (plugins/system/cosn) - apps runner;</li>
</ul>
</p>
<p>The Cosn package allows to include custom code (stored in a file or retrieved from a database) into any Joomla! content.</p>
<h2>Usage</h2>
<p> 
<pre>{cosn [app] [arg1] [arg2] ... [argN]}</pre>
</p>
<h3>app</h3>
<p> app can be one of following
<ul>
<li>path to cosn app file to run;</li>
<li>app ID in database;</li>
</ul>
</p>
<p>The path to cosn app file is a relative one with respect to Default Absolute Path that can be set in the plugin parameters, if not set then is equivalent to Joomla! root.</p>
<p>The record ID must be preceeded by an asterrisk (e.g. *4).</p>
<h3>arg</h3>
<p>Arguments [arg1] ... [argN] are optional parameters to be passed to cosn app.</p>
<h3>Examples</h3>
<h4>Execute cosn app from file with arguments</h4>
<p>
<pre>{cosn [plugins/system/cosn/cosn/cosn_demo.php][first][second]}</pre>
</p>
<h4>Execute cosn app from database</h4>
<p>
<pre>{cosn [*1]}</pre>
</p>
