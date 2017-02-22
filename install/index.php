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

if (!is_writable($settingsfile)) {
	$errors[] = "The file <code>data/config/script.ini</code> doesn't exist or is not writeable Check the presence and the file permissions of the file with your FTP client.";
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
} else if ($insteps['step1'] === true and $insteps['step2'] === false) {
	# the ini was rewritten, proceed with creating the database tables
} else if ($insteps['step1'] === false) {
	# take the first step and let the user input the general settings
} else {
	# an error occured, the array $insteps stores invalid values, let the script die
}

?>
