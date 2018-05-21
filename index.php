<?php

/**
 * Script to correct with PHP magic quoted text in a MySQL database.
 *
 * @package removeGPC
 * @author H. August <post@auge8472.de>
 * @copy 2017
 * @version 0
 * @license https://opensource.org/licenses/GPL-2.0  GNU Public License version 2
 */

require_once "data/scripts/funcs.db.php";
$settingsfile = "data/config/script.ini";

$errors = array();
$settings = array();
$page = array('Title' => '', 'Content' => '', 'CSS' => 'data/style.css', 'JS' => 'data/pages.js');
$template = '';
session_start();

$settings = parse_ini_file($settingsfile, TRUE);

if (!array_key_exists('install', $settings) or (array_key_exists('install', $settings) and(!array_key_exists('step1', $settings['install'])
or !array_key_exists('step2', $settings['install'])))) {
	#the installation was not performed yet or is not finished
	header('Location: install/index.php');
	exit;
}

if (file_exists('install/index.php')) {
	$errors[] = 'Please remove the installation script from the server. You find the mentioned file "index.php" in the directory "install". The program will not work until the script was removed from the server.';
}
if (is_writable($settingsfile)) {
	$errors[] = 'The file "data/config/script.ini" is writeable. This is a security flaw. The program will not work until this issue is solved. Please check the file permissions of the file with your FTP client.';
	$page['Title'] = "Error: can't work with a writeable script.ini";
}

if (empty($errors)) {
	$cid = dBase_Connect($settings['db']);
	if (is_array($cid) and $cid[0] === false) {
		$errors[] = $cid[1];
	}
}

if (empty($errors)) {
	$qReadSettings = "SELECT name, val FROM remGPC_Settings";
	$rSettings = dBase_Ask_Database($qReadSettings, $cid);
	if (is_array($cid) and $cid[0] === false) {
		$errors[] = $cid[1];
	} else {
		foreach ($rSettings as $line) {
			$settings[$line['name']] = $line['value'];
		}
	}
}

if (empty($errors)) {
	if (!isset($_SESSION['user_name'])) {
		$page['Content'] = file_get_contents('data/run.login.tpl');
		$page['title'] = 'Login for removeGPC';
	}
}

if (!empty($errors)) {
	$page['Content'] = file_get_contents('data/run.errors.tpl');
	$errorMess  = '   <section id="errormessages">'."\n";
	$errorMess .= '    <h2>Errors</h2>'."\n";
	$errorMess .= '    <ul>'."\n";
	foreach ($errors as $error) {
		$errorMess .= '     <li>'. htmlspecialchars($error) .'</li>'."\n";
	}
	$errorMess .= '    </ul>'."\n";
	$errorMess .= '   </section>'."\n";
} else {
	$errorMess = '';
}
$page['Content'] = str_replace('[%RunErrors%]', $errorMess, $page['Content']);

$template = file_get_contents('data/main.tpl');
$template = str_replace('[%URL2CSS%]', htmlspecialchars($page['CSS']), $template);
$template = str_replace('[%URL2JS%]', htmlspecialchars($page['JS']), $template);
$template = str_replace('[%PageTitle%]', htmlspecialchars($page['Title']), $template);
$template = str_replace('[%PageContent%]', $page['Content'], $template);
echo $template;

?>
