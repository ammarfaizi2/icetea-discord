<?php

namespace TeaDiscord;

use Error;
use Exception;
use Discord\Discord;
use Discord\Voice\VoiceClient;
use Discord\Parts\Channel\Message;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaDiscord
 * @version 0.0.1
 */
final class TeaStream
{
	/**
	 * @param \Discord\Discord
	 */
	private $discord;

	/**
	 * @var string
	 */
	private $guild_id;

	/**
	 * @var string
	 */
	private $channel_id;

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @var string
	 */
	private $curChannel;

	/**
	 * @param string $guild_id
	 * @param string $channel_id
	 * @param string $file
	 * @param string $curChannel
	 * @throws \Exception
	 *
	 * Constructor.
	 */
	public function __construct(string $guild_id, string $channel_id, string $file, string $curChannel)
	{
		global $cfg;
		$this->discord = new Discord(["token" => $cfg["discord_bot_token"]]);
		$this->guild_id = $guild_id;
		$this->channel_id = $channel_id;
		$this->file = $file;
		$this->curChannel = $curChannel;

		if (!file_exists($file)) {
			throw new Exception("File: {$file} does not exists");
		}
	}

	/**
	 *
	 *
	 */
	private function handleMessage()
	{

	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		global $cfg;
		try {

			$this->discord->on("ready", function ($discord) {

				$guild = $this->discord->guilds->get("id", $this->guild_id);
				$channel = $guild->channels->get("id", $this->channel_id);
				$this->discord->joinVoiceChannel($channel, false, false, null)
					->then(function (VoiceClient $vc) use () {
							$this->discord->on("message", function ($message) use ($vc) {
								$this->handleMessage($message, $vc);
							});
							$vc->setBitrate(128000)->then(
								function () use ($vc) {
									$vc->playFile($this->file)->then(function () {
										global $cfg;
										shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
									})->otherwise(function ($e) {
										printf("Error: %s\n", $e->getMessage());
										shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
									});
								}
							)->otherwise(function($e){ 
								printf("Error: %s\n", $e->getMessage());
							});
						}
					)
					->otherwise(function ($e) {
						printf("Error: %s\n", $e->getMessage());
					});

			});

			$this->discord->run();

		} catch (Error $e) {
			printf("\n\nAn error occured!\n");
			var_dump($e->getMessage(), $e->getFile(), $e->getLine());
		}
		return;
	}
}
