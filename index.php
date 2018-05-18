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

$errors = array();
$settings = parse_ini_file("data/config/script.ini", TRUE);

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
	$errors[] = "The file <code>data/config/script.ini</code> is writeable. This is a security flaw. The program will not work until this issue is solved. Please check the file permissions of the file with your FTP client.";
	$page['Title'] = "Error: can't work with a writeable script.ini";
}

if (empty($errors)) {
	$cid = dBase_Connect($settings['db']);
	if (is_array($cid) and $cid[0] === false) {
		$errors[] = $cid[1];
	}
}
if (empty($errors)) {
}

?>
