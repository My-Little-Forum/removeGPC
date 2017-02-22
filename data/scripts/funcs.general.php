<?php

/**
 * this file contains the general functions
 * for the processing of the script
 *
 */


/**
 * writes a string content to a file
 *
 * adapts the code from http://stackoverflow.com/questions/5695145/how-to-read-and-write-to-an-ini-file-with-php#5695202
 *
 * @param string $path
 * @param string $content
 * @return boolean false on error
 * @return integer number of written bytes
 */
function writeTextFile($path, $content) {
	$check = false;
	if ($fp = fopen($path, 'w') {
		$start = microtime(true);
		do {
			$canwrite = flock($fp, LOCK_EX);
			if (!$canwrite) usleep(round(rand(10, 40), * 1000));
		} while ((!$canwrite) and ((microtime(true) - $start) < 5));
		if ($canwrite) {
			$check = fwrite($fp, $content);
			flock($fp, LOCK_UN);
		}
		fclose($fp);
		return $check;
	} else {
		return false;
	}
}


/**
 * prepare the settings array for saving
 *
 * takes the differnence between string and numeric values into account
 *
 * @param string $path
 * @param array $set (source: $settings)
 * @return boolean false on error
 * @return boolean true on success
 */
function preprocessINI($path, $set) {
	$r = false;
	$l = array();
	foreach ($set as $k => $v) {
		if (is_array($v)) {
			$l[] = '[' . $k . ']';
			foreach ($v as $sk => $sv) {
				$l[] = "$sk = " . (is_numeric($sv) ? $sv : '"' . $sv . '"');
			}
		} else {
			$l[] = "$k = " . (is_numeric($v) ? $v : '"' . $v . '"');
		}
	}
	$r = writeTextFile($path, implode("\r\n", $l));
	if ($r !== false) return true;
	return false;
}


/**
 * encapsulated htmlspecialchars with the correct charset
 *
 * @param string $string
 * @param string $charset
 * @return string $string
 */
function htmlsc($string, $charset = 'ISO-8859-1') {
	return htmlspecialchars($string, ENT_QUOTES, $charset, false);
}

?>
