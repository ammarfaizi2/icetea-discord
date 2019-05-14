<?php

namespace TeaDiscord;

use Discord\Discord;
use Discord\Voice\VoiceClient;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;

/**
* @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
* @license MIT
* @package \TeaDiscord
* @version 0.0.1
*/
final class Music
{
	/**
	 * @var \Discord\Discord
	 */
	private $discord;

	/**
	 * @var \Discord\Parts\Guild\Guild
	 */
	private $guild;

	/**
	 * @var \Discord\Parts\Channel\Channel
	 */
	private $channel;

	/**
	 * @var array
	 */
	private $optChannel = [];

	/**
	 * @param \Discord\Discord $discord
	 *
	 * Constructor.
	 */
	public function __construct(Discord $discord, Guild $guild, Channel $channel)
	{
		$this->discord = $discord;
		$this->guild = $guild;
		$this->channel = $channel;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		if ($this->hasSelectedChannel()) {
			$this->doStream();
		} else {
			$this->sendChannelOption();
		}
	}

	/**
	 * @return bool
	 */
	private function hasSelectedChannel(): bool
	{
		global $cfg;
		return file_exists($cfg["storage_path"]."/guild/{$this->guild->id}/stream_channel");
	}

	/**
	 * @return int
	 */
	private function doStream(): int
	{	
		global $cfg;


		$this->channel->sendMessage("Initializing streamer...")->then(function () {
			dlog("Message sent!");
		})->otherwise(function($e) {
			dlog("There was an error sending the message: %s\n", $e->getMessage());
			dlog("%s\n", $e->getTraceAsString());
		});

		if (!($pid = pcntl_fork())) {
			cli_set_process_title("stream-worker --exec-json");
			shell_exec(
				"exec ".
				escapeshellarg(PHP_BINARY)." ".
				escapeshellarg($cfg["basepath"]."/bin/stream.php")." ".
				escapeshellarg(json_encode(
					[
						"guild_id" => $this->guild->id,
						"channel_id" => file_get_contents($cfg["storage_path"]."/guild/{$this->guild->id}/stream_channel"),
						"file" => $cfg["storage_path"]."/stream/mp3/me.mp3",
						"volume" => (
							file_exists($cfg["storage_path"]."/guild/{$this->guild->id}/stream_volume") ?
								(int)file_get_contents($cfg["storage_path"]."/guild/{$this->guild->id}/stream_volume") :
								80
						),
						"cur_channel" => $this->channel->id
					],
					JSON_UNESCAPED_SLASHES
				))
			);

			exit;
		}

		return $pid;
	}

	/**
	 * @return void
	 */
	private function sendChannelOption(): void
	{
		$this->optChannel = [];
		$reply = "**Please select a channel to stream! (in 30 seconds, reply with the number)**\n```";
		$i = 1;
		foreach($this->guild->channels->getAll("type", Channel::TYPE_VOICE) as $channel) {
			$this->optChannel[] = $channel->id;
			$reply .= "{$i}. {$channel->name}\n";
			$i++;
		}
		$reply .= "```";

		$shm_id = shmop_open(getMKey($this->guild->id, ShmKeyId::SHM_ACT), "c", 0644, 72);
		shmop_write($shm_id, ShmAct::SELECT_STREAM_CHANNEL."|{$this->channel->id}|".time(), 0);
		shmop_close($shm_id);

		$shm_id = shmop_open(getMKey($this->guild->id, ShmKeyId::SELECT_STREAM_CHANNEL), "c", 0644, 1000);
		shmop_write($shm_id, json_encode($this->optChannel, JSON_UNESCAPED_SLASHES), 0);
		shmop_close($shm_id);

		$this->channel->sendMessage($reply)->then(function ($message) {
			
		})->otherwise(function ($e) {
			dlog("There was an error sending the message: %s\n", $e->getMessage());
			dlog("%s\n", $e->getTraceAsString());
		});
	}
}
