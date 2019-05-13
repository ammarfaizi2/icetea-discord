<?php

declare(ticks=1);

require __DIR__."/vendor/autoload.php";

$writePid = file_put_contents($cfg["pid_file"], getmypid());

if (!$writePid) {
	print "Couldn't create a pid file!\n";
	exit(1);
}

/**
 * @return void
 */
function dcShutDown(): void
{
	global $cfg;
	@unlink($cfg["pid_file"]);
}

register_shutdown_function("dcShutDown");

/**
 * @return void
 */
function killChild(): void
{
	global $cfg;

	if (isset($cfg["child_pid"])) {
		print "Stopping child {$cfg["child_pid"]}...\n";
		posix_kill($cfg["child_pid"], SIGKILL);
	}
}

/**
 * @param signal int
 * @param array  $sga
 * @return void
 */
function signalHandler($signal, $sga): void
{
	global $cfg;
	switch ($signal) {
		case SIGTERM:
			killChild();
			exit;
			break;

		case SIGINT:
			print "Interupt signal!\n";
			killChild();
			exit;
			break;
		
		case SIGHUP:
			require __DIR__."/config.php";
			$cfg["got_sighup"] = true;
			killChild();
			break;

		default:
			print "Not handled signal!\n";
			exit(1);
			break;
	}
}

/**
 * It returns child pid.
 *
 * @param callable $callback
 * @return int
 */
function doFork(callable $callback): int
{
	if (!($pid = pcntl_fork())) {
		$callback();
		exit;
	}

	return $pid;
}

init:
$cfg["child_pid"] = doFork(function () {
	$st = new TeaDiscord\TeaDiscord;
	$st->run();
});

pcntl_signal(SIGINT, "signalHandler");
pcntl_signal(SIGHUP, "signalHandler");
pcntl_signal(SIGCHLD, SIG_IGN);

while (true) {
	sleep(10000);
	if (isset($cfg["got_sighup"])) {
		print "Reloading...\n";
		unset($cfg["got_sighup"]);
		goto init;
	}
}
