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

$cid = dBase_Connect($settings['db']);

if (is_array($cid) and $cid[0] === false) {
	$errors[] = $cid[1];
} else {
}

?>
