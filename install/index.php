<?php

/**
 * Installation script for removeGPC
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
			$usr_name = NULL;
			$usr_pass = NULL;
			# data for connecting with the database
			$db_server = NULL;
			$db_name = NULL;
			$db_user = NULL;
			$db_pass = NULL;
			# data for the data presentation layout
			$op_entries_pp = 25;
		}
	} else {
		# no taken steps registered, start installation script from the beginning
		$insteps['step1'] = false;
		$insteps['step2'] = false;
		$usr_name = NULL;
		$usr_pass = NULL;
		# data for connecting with the database
		$db_server = NULL;
		$db_name = NULL;
		$db_user = NULL;
		$db_pass = NULL;
		# data for the data presentation layout
		$op_entries_pp = 25;
	}
}

# proceed the form data, when given
if ($insteps['step1'] === false and isset($_POST['send_step1'])) {
	# data for the acting user
	$usr_name = (isset($_POST['usr_name']) and !empty($_POST['usr_name'])) ? $_POST['usr_name'] : NULL;
	$usr_pass = (isset($_POST['usr_pass']) and !empty($_POST['usr_pass'])) ? $_POST['usr_pass'] : NULL;
	# data for connecting with the database
	$db_server = (isset($_POST['db_server']) and !empty($_POST['db_server'])) ? $_POST['db_server'] : NULL;
	$db_name = (isset($_POST['db_name']) and !empty($_POST['db_name'])) ? $_POST['db_name'] : NULL;
	$db_user = (isset($_POST['db_user']) and !empty($_POST['db_user'])) ? $_POST['db_user'] : NULL;
	$db_pass = (isset($_POST['db_pass']) and !empty($_POST['db_pass'])) ? $_POST['db_pass'] : NULL;
	# data for the data presentation layout
	$op_entries_pp = (isset($_POST['op_entries_per_page']) and in_array($_POST['op_entries_per_page'], array(10, 15, 20, 25, 30, 35, 40, 45, 50))) ? $_POST['op_entries_per_page'] : 25;
	if ($usr_name === NULL or $usr_pass === NULL) {
		# user credentials incomplete
		$errors[] = 'The user credentials are incomplete. Either the user name or the password is missing.';
	}
	if ($db_server === NULL or $db_name === NULL or $db_user === NULL or $db_pass === NULL) {
		# database credentials incomplete
		$errors[] = 'A ful set of server name, database name, database user name and password is necessary to access the database. One or more of these informations are absent.';
	}
	if (empty($errors)) {
		# data complete, test the database connection
		$conn = dBase_connect(array('server' => $db_server, 'name' => $db_name, 'user' => $db_user, 'pass' => $db_pass));
		if (is_array($conn) and $conn[0] === false) {
			$errors[] = 'Could not connect to the database with the given credentials.';
			$errors[] = $conn[1];
		}
	}
	if (empty($errors)) {
		# test of the database connection was successful, write the informations to the ini-file.
		$iniContent  = "; script.ini\n";
		$iniContent .= ";\n\n[db]\n";
		$iniContent .= "server = '". $db_server ."'\n";
		$iniContent .= "user = '". $db_user ."'\n";
		$iniContent .= "pass = '". $db_pass ."'\n";
		$iniContent .= "name = '". $db_name ."'\n";
		$iniContent .= "\n[install]\n";
		$iniContent .= "step1 = true\n";
		$iniWritten = file_put_contents($settingsfile, $iniContent, LOCK_EX);
		if ($iniWritten === false) {
			$errors[] = 'Could not write the settings to the INI-file.';
		}
	}
	if (empty($errors)) {
		# INI-file was written
		# reread the settings to ensure the use of the stored values
		$settings = parse_ini_file($settingsfile, TRUE);
		if (array_key_exists('db', $settings) and array_key_exists('server', $settings['db']) and array_key_exists('name', $settings['db']) and array_key_exists('user', $settings['db']) and array_key_exists('pass', $settings['db'])) {
			$db_server = $settings['db']['server'];
			$db_name = $settings['db']['name'];
			$db_user = $settings['db']['user'];
			$db_pass = $settings['db']['pass'];
		} else {
			$errors[] = 'Could not read the values of the database connection from the INI even the installation script stated to have them written to the file. Plese check the file data/config/script.ini to be writeable. Please report the error to the project maintainer otherwise.';
		}
	}
	if (empty($errors)) {
		# create the database tables and store the additional settings to the database
		$qSettingsTable = "CREATE TABLE remGPC_Settings (name varchar(50) NOT NULL, val varchar(30) DEFAULT NULL, type varchar(30) DEFAULT NULL, PRIMARY KEY (name)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci";
		$rSettingsTable = dBase_Ask_Database($qSettingsTable, $conn);
		if ($rSettingsTable === false) {
			$errors[] = 'It was impossible to create the settings table. Please report the error to the project maintainer.';
			$errors[] = mysqli_error($conn);
		} else {
			$qPutSettings = "INSERT INTO remGPC_Settings VALUES ('datasets_per_page', ". intval($op_entries_pp) .", 'number'), ('user', '". mysqli_real_escape_string($conn, $usr_name) ."', 'text'), ('pass', '". mysqli_real_escape_string($conn, $usr_pass) ."', 'password'), ('textarea_x', 45, 'number'), ('textarea_y', 14, 'number')";
			$rPutSettings = dBase_Ask_Database($qPutSettings, $conn);
			if ($rPutSettings === false) {
				$errors[] = 'Could not write the settings to the database table.';
				$errors[] = mysqli_error($conn);
			}
		}
	}
	if (empty($errors)) {
		# INI-file was written, create the database tables and store the additional settings to the database
		$qCorrectionTables = "CREATE TABLE remGPC_Tables (dsID int AUTO_INCREMENT, nameTable varchar(255), checkTable set('0', '1') DEFAULT '0', PRIMARY KEY (dsID), KEY `nameTable` (`nameTable`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci";
		$rCorrectionTables = dBase_Ask_Database($qCorrectionTables, $conn);
		if ($rCorrectionTables === false) {
			$errors[] = 'It was impossible to create the table for the tables to correct. Please report the error to the project maintainer.';
			$errors[] = mysqli_error($conn);
		} else {
			$qTableNames = "INSERT INTO remGPC_Tables (nameTable) SELECT table_name FROM information_schema.tables WHERE table_schema = '". mysqli_real_escape_string($conn, $db_name) ."' AND table_type = 'BASE TABLE'";
			$rTableNames = dBase_Ask_Database($qTableNames, $conn);
			if ($rPutSettings === false) {
				$errors[] = 'Could not write the table names to the database table.';
				$errors[] = mysqli_error($conn);
			}
		}
	}
	if (empty($errors)) {
		# INI-file was written, create the database tables and store the additional settings to the database
		$qCorrectionFields = "CREATE TABLE remGPC_Fields (dsID int AUTO_INCREMENT, tableID int, nameField varchar(255), checkField set('0', '1') DEFAULT '0', PRIMARY KEY (dsID), KEY `nameField` (`nameField`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci";
		$rCorrectionFields = dBase_Ask_Database($qCorrectionFields, $conn);
		if ($rCorrectionFields === false) {
			$errors[] = 'It was impossible to create the table for the fields to correct. Please report the error to the project maintainer.';
			$errors[] = mysqli_error($conn);
		}
	}
	if (empty($errors)) {
		$insteps['step1'] = true;
	}
}
if ($insteps['step1'] === true and $insteps['step2'] === false and isset($_POST['send_step2'])) {
	# the user selected the tables to check
	if (array_key_exists('db', $settings) and array_key_exists('server', $settings['db']) and array_key_exists('name', $settings['db']) and array_key_exists('user', $settings['db']) and array_key_exists('pass', $settings['db'])) {
		$db_server = $settings['db']['server'];
		$db_name = $settings['db']['name'];
		$db_user = $settings['db']['user'];
		$db_pass = $settings['db']['pass'];
	} else {
		$errors[] = 'Could not read the values of the database connection from the INI even the installation script stated to have them written to the file. Plese check the file data/config/script.ini to be writeable. Please report the error to the project maintainer otherwise.';
	}
	if (empty($errors) and !empty($_POST['tables']) and is_array($_POST['tables'])) {
		# data complete, test the database connection
		$conn = dBase_connect(array('server' => $db_server, 'name' => $db_name, 'user' => $db_user, 'pass' => $db_pass));
		if (is_array($conn) and $conn[0] === false) {
			$errors[] = 'Could not connect to the database with the given credentials.';
			$errors[] = $conn[1];
		}
		if (empty($errors)) {
			$qResetTables = "UPDATE remGPC_Tables SET checkTable = '0'";
			$rResetTables = dBase_Ask_Database($qResetTables, $conn);
			if ($rResetTables === false) {
				$errors[] = 'It was impossible to reset the information about the tables in the database.';
				$errors[] = mysqli_error($conn);
			}
		}
		if (empty($errors)) {
			foreach ($_POST['tables'] as $table) {
				$qChooseTable = NULL;
				$qChooseTable = "UPDATE remGPC_Tables SET checkTable = '1' WHERE dsID = ". intval($table);
				$rChooseTable = dBase_Ask_Database($qChooseTable, $conn);
				if ($rChooseTable === false) {
					$errors[] = 'It was impossible to store the information about the choosed table in the database.';
					$errors[] = mysqli_error($conn);
				}
			}
		}
	} else {
		$errors[] = 'You have not selected any table name for checking of its content.';
	}
	if (empty($errors)) {
		# test of the database connection was successful, write the informations to the ini-file.
		$iniContent = file_get_contents($settingsfile);
		$iniContent = $iniContent . "step2 = true\n";
		$iniWritten = file_put_contents($settingsfile, $iniContent, LOCK_EX);
		if ($iniWritten === false) {
			$errors[] = 'Could not write the settings to the INI-file.';
		}
	}
	if (empty($errors)) {
		$insteps['step1'] = true;
		$insteps['step2'] = true;
	}
}

# generate the output for the installation process
if ($insteps['step1'] === true and $insteps['step2'] === true) {
	$errors[] = 'The installation is already complete. Please <a href="../index.php">run the script</a> and check your database content.';
	$page['Title'] = 'Installation is already complete';
} else if ($insteps['step1'] === true and $insteps['step2'] === false) {
	$page['Content'] = file_get_contents('../data/install.step2.tpl');
	# the ini was rewritten, proceed with configuring the script, select tables to check
	$conn = dBase_connect($settings['db']);
	if (is_array($conn) and $conn[0] === false) {
		$errors[] = 'The database could not be opened. Please report the error to the project maintainer.';
		$errors[] = $conn[1];
	} else {
		# read table data from the config table
		$qTableList = "SELECT dsID, nameTable FROM remGPC_Tables ORDER BY nameTable ASC";
		$rTableList = dBase_Ask_Database($qTableList, $conn);
		if ($rTableList === false) {
			$errors[] = 'The list of Tables could not be read from the database. Please report the error to the project maintainer.';
			$page['Content'] = str_replace('[%InstTableList%]', '', $page['Content']);
		} else {
			$t = 0;
			$tListList = array();
			$tListTempl = '      <li><input id="table_[%InstTNS%]" name="tables[]" value="[%InstTNS%]" type="checkbox"><label for="table_[%InstTNS%]">[%InstTableListName%]</label></li>';
			foreach ($rTableList as $row) {
				$tListList[$t] = $tListTempl;
				$tListList[$t] = str_replace('[%InstTNS%]', htmlspecialchars($row['dsID']), $tListList[$t]);
				$tListList[$t] = str_replace('[%InstTableListName%]', htmlspecialchars($row['nameTable']), $tListList[$t]);
				$t++;
			}
			$page['Title'] = 'Installation, step 2: database tables';
			$tListList = join("\n", $tListList);
			$page['Content'] = str_replace('[%InstTableList%]', $tListList, $page['Content']);
		}
	}
	if (!empty($errors)) {
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
	$page['Content'] = str_replace('[%InstErrors%]', $errorMess, $page['Content']);
} else if ($insteps['step1'] === false) {
	# take the first step and let the user input the general settings
	$page['Title'] = 'Installation, step 1: database credentials and program settings';
	$page['Content'] = file_get_contents('../data/install.step1.tpl');
	$page['Content'] = str_replace('[%InstUserName%]', htmlspecialchars($usr_name), $page['Content']);
	$page['Content'] = str_replace('[%InstUserPass%]', htmlspecialchars($usr_pass), $page['Content']);
	$page['Content'] = str_replace('[%InstDBServer%]', htmlspecialchars($db_server), $page['Content']);
	$page['Content'] = str_replace('[%InstDBName%]', htmlspecialchars($db_name), $page['Content']);
	$page['Content'] = str_replace('[%InstDBUser%]', htmlspecialchars($db_user), $page['Content']);
	$page['Content'] = str_replace('[%InstDBPass%]', htmlspecialchars($db_pass), $page['Content']);
	$page['Content'] = str_replace('[%InstEntriesPage%]', htmlspecialchars($op_entries_pp), $page['Content']);
	if (!empty($errors)) {
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
	$page['Content'] = str_replace('[%InstErrors%]', $errorMess, $page['Content']);
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
