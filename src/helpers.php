<?php

/**
 * @param string $format
 * @param mixed  ...$args
 * @return void
 */
function dlog(string $format, ...$args): void
{
	printf("[%s]: %s\n",
		date("Y-m-d H:i:s"),
		vsprintf($format, $args));
}

/**
 * @param string $key
 * @param int	 $projectId
 * @return int
 */
function getMKey(string $key, int $projectId): int
{
	global $cfg;
	if (file_exists($cfg["storage_path"]."/guild/".$key)) {
		return ftok($cfg["storage_path"]."/guild/".$key, chr($projectId % 256));
	}
	return -2;
}
	
/**
 * @param resource $shmid
 * @param int      $start
 * @param int      $count
 * @return string
 */
function mkread($shmid, int $start, int $count): string
{
	$read = shmop_read($shmid, $start, $count);
	if (($nullpos = strpos($read, "\0")) !== false) {
		return substr($read, 0, $nullpos);
	}
	return is_string($read) ? $read : "";
}
