<?php

/**
 * this file contains the functions to access
 * a database and to commit the database requests
 *
 */


/**
 * connects to the database
 *
 * @param array $db database connection informations
 *
 * @return integer connection ID
 * @return array in case of an error
 */
function dBase_Connect($db) {
	$s = false;
	if (function_exists('mysqli_connect')) {
		$s = @mysqli_connect($db['server'], $db['user'], $db['pass'], $db['name']);
		if ($s === false) return array(false, mysqli_connect_error());
		$u = @mysqli_set_charset($s, 'ISO-8859-1');
		if ($u === false) return array(false, mysqli_error($s));
	}
	return $s;
} # End: dBase_Connect


/**
 * sends any query to the database and returns the outcome of the operation
 *
 * @param string $query
 * @param ressource $sql
 *
 * @return array
 * @return boolean false
 * @return boolean true
 */
function dBase_Ask_Database($q, $s) {
	$a = @mysqli_query($s, $q);
	if ($a === false) {
		$return = false;
	} else {
		if ($a === true) {
			# INSERT, UPDATE, ALTER etc. pp.
			$return = true;
		} else {
			# !true, !false, ressource number
			# SELECT, EXPLAIN, SHOW, DESCRIBE
			$b = dbase_Generate_Answer($a, $s);
			mysqli_free_result($a);
			$return = $b;
		}
	}
return $return;
} # Ende: dbase_Ask_Database


/**
 * puts datasets into an associated array
 *
 * @param resource $a
 * @param connection $s
 *
 * @return array $b
 */
function dBase_Generate_Answer($a, $s = NULL) {
	$b = array();
	while ($row = mysqli_fetch_assoc($a)) {
		$b[] = $row;
	}
return $b;
} # Ende: dBase_Generate_Answer

?>
