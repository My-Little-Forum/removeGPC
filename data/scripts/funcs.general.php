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

?>
