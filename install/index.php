<?php

/**
 * Installation script for removeGPC.
 *
 * @package removeGPC
 * @author H. August <post@auge8472.de>
 * @copy 2017
 * @version 0
 * @license https://opensource.org/licenses/GPL-2.0  GNU Public License version 2
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../data/scripts/funcs.db.php";
$settingsfile = "../data/config/script.ini";

$errors = array();
$settings = array();
$insteps = array('step1' => true, 'step2' => true);
$page = array('Title' => '', 'Content' => '', 'CSS' => '../data/style.css', 'JS' => '../data/pages.js');
$template = '';

if (!is_writable($settingsfile)) {
	$errors[] = "The file <code>data/config/script.ini</code> doesn't exist or is not writeable. Check the presence and the file permissions of the file with your FTP client.";
	$page['Title'] = "Error: can't work with script.ini";
}

if (empty($errors)) {
	# check, wich steps to take
	$settings = parse_ini_file($settingsfile, TRUE);
	if (array_key_exists('install', $settings)) {
		# the settings section 'install' exists, the install script ran before
		if (array_key_exists('step1', $settings['install'])
			and array_key_exists('step2', $settings['install'])) {
			# installation is already complete
		} else if (array_key_exists('step1', $settings['install'])
			and !array_key_exists('step2', $settings['install'])) {
			# step one was taken, step 2 is owing
			$insteps['step1'] = true;
			$insteps['step2'] = false;
		} else {
			# no taken steps registered, start installation script from the beginning
			$insteps['step1'] = false;
			$insteps['step2'] = false;
		}
	} else {
		# no taken steps registered, start installation script from the beginning
		$insteps['step1'] = false;
		$insteps['step2'] = false;
	}
}

# generate the output for the installation process
if ($insteps['step1'] === true and $insteps['step2'] === true) {
	$errors[] = 'The installation is already complete. Please <a href="../index.php">run the script</a> and check your database content.';
	$page['Title'] = 'Installation is already complete';
} else if ($insteps['step1'] === true and $insteps['step2'] === false) {
	# the ini was rewritten, proceed with creating the database tables
	$page['Title'] = 'Installation, step 2: database tables';
} else if ($insteps['step1'] === false) {
	# take the first step and let the user input the general settings
	# data for the acting user
	$usr_name = (isset($_POST['usr_name']) and !empty($_POST['usr_name'])) ? $_POST['usr_name'] : NULL;
	$usr_pass = (isset($_POST['usr_pass']) and !empty($_POST['usr_pass'])) ? $_POST['usr_pass'] : NULL;
	# data for connecting with the database
	$db_server = (isset($_POST['db_server']) and !empty($_POST['db_server'])) ? $_POST['db_server'] : NULL;
	$db_name = (isset($_POST['db_name']) and !empty($_POST['db_name'])) ? $_POST['db_name'] : NULL;
	$db_user = (isset($_POST['db_user']) and !empty($_POST['db_user'])) ? $_POST['db_user'] : NULL;
	$db_pass = (isset($_POST['db_pass']) and !empty($_POST['db_pass'])) ? $_POST['db_pass'] : NULL;
	# data for the data presentation layout
	$op_entries_pp = (isset($_POST['op_entries_per_page']) and in_array($_POST['op_entries_per_page'], array(10, 15, 20, 25, 30, 35, 40, 45, 50))) ? $_POST['db_pass'] : 25;
	$page['Title'] = 'Installation, step 1: database credentials and program settings';
	$page['Content'] = file_get_contents('../data/install.step1.tpl');
} else {
	# an error occured, the array $insteps stores invalid values, let the script die
	$errors[] = 'The installation process hangs in an undefined state.';
	$page['Title'] = 'Error: undefined state of the installation process';
}

$template = file_get_contents('../data/main.tpl');
$template = str_replace('[%URL2CSS%]', htmlspecialchars($page['CSS']), $template);
$template = str_replace('[%URL2JS%]', htmlspecialchars($page['JS']), $template);
$template = str_replace('[%PageTitle%]', htmlspecialchars($page['Title']), $template);
$template = str_replace('[%PageContent%]', $page['Content'], $template);
echo $template;

?>
