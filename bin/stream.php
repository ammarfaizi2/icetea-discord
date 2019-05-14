<?php

require __DIR__."/../config.php";
require __DIR__."/../init.php";

if (isset($argv[1])) {
	$argv = json_decode($argv[1], true);
	if (isset(
		$argv["guild_id"],
		$argv["channel_id"],
		$argv["file"]
	)) {
		(new TeaStream\TeaStream(
			$argv["guild_id"],
			$argv["channel_id"],
			$argv["file"]
		))->run();
		exit(0);
	} else {
		print "Invalid argument!\n";
		exit(1);
	}
} else {
	print "argv[1] is required!\n";
	exit(1);
}
