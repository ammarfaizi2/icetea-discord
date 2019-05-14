<?php

namespace TeaDiscord;

use Error;
use Discord\Discord;
use Discord\Parts\Guild\Guild;
use Discord\Voice\VoiceClient;
use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;

/**
* @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
* @license MIT
* @package \TeaDiscord
* @version 0.0.1
*/
final class Response
{
	/**
	 * @param \Discord\Discord
	 */
	private $discord;

	/**
	 * @param \Discord\Parts\Channel\Message
	 */
	private $message;

	/**
	 * Constructor.
	 */
	public function __construct(Discord $discord, Message $message)
	{
		$this->discord = $discord;
		$this->message = $message;
	}

	/**
	 *
	 * @return void
	 */
	private function buildGuildDir(Guild $guild): void
	{
		global $cfg;
		$dir = $cfg["storage_path"]."/guild/{$guild->id}";
		is_dir($dir) or mkdir($dir);
		file_exists($dir."/info.json") or file_put_contents($dir."/info.json", 
			json_encode($guild, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}

	/**
	 * @param \Discord\Parts\Guild\Guild $guild
	 * @param array $param
	 * @param string $text
	 * @param mixed &$reply
	 * @return void
	 */
	private function shmAct(Guild $guild, array $param, string $text, &$reply): bool
	{
		global $cfg;

		$param[0] = (int) $param[0];

		switch ($param[0]) {
			case ShmAct::SELECT_STREAM_CHANNEL:
				$shm_id = shmop_open(getMKey($guild->id, ShmKeyId::SELECT_STREAM_CHANNEL), "c", 0644, 1000);

				if ((((int)$param[2])+30) < time()) {
					shmop_delete($shm_id);
					shmop_close($shm_id);
					return false;
				}

				$read = json_decode(mkread($shm_id, 0, 1000), true);
				if (is_numeric($text)) {
					$text = (int) $text;
					if ($text >= 1 && $text <= count($read)) {
						file_put_contents(
							$cfg["storage_path"]."/guild/{$guild->id}/stream_channel",
							$read[$text - 1]
						);
						$reply = "Channel `{$read[$text - 1]}` has been selected as streaming channel!";
						shmop_delete($shm_id);
						shmop_close($shm_id);
						return true;
					}
				}
				shmop_close($shm_id);
				break;
			
			default:
				break;
		}
		return false;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		global $cfg;

		$id = $this->message->id;
		$guild_id = $this->message->channel->guild_id;
		$channel_id = $this->message->channel_id;
		$text = $this->message->content;
		$type = $this->message->type;
		$user = $this->message->author->user;

		if ((!isset($text)) || (empty($text))) {
			return;
		}
		dlog("New message!\n");
		$guild = $this->discord->guilds->get("id", $guild_id);
		$channel = $guild->channels->get("id", $channel_id);
		$this->buildGuildDir($guild);

		$shm_id = shmop_open(getMKey($guild->id, ShmKeyId::SHM_ACT), "c", 0644, 72);
		$read = explode("|", mkread($shm_id, 0, 72));

		$reply = null;

		if (count($read) > 1) {
			if ($this->shmAct($guild, $read, $text, $reply)) {
				goto shm_delete_reply;
			}

			if (is_string($reply)) {
				goto shm_close_no_reply;
			}
		}

		goto shm_close_no_reply;


shm_delete_reply:
		shmop_delete($shm_id);
		shmop_close($shm_id);
		goto reply;


shm_close_no_reply:
		shmop_close($shm_id);


		// Shell exec.
		if (preg_match("/^(?:\!|\/|\.|\~)(?:cx(?:[\s\n]+))(.+)$/USsi", $text, $m)) {
			if (in_array("{$user->id}@{$user->username}", $cfg["sudoers"])) {
				$cmd = trim(shell_exec("/bin/bash -c ".escapeshellarg($m[1])." 2>&1"));
				$reply = [];
				foreach (str_split(str_replace("`", "\\`", $cmd), 2000 - 15) as $r) {
					$reply[] = "```text\n{$r}\n```";
				}
			} else {
				$reply = "@{$user->username}#{$user->discriminator} is not in the sudoers files. This incident will be reported.";
			}
			goto reply;
		}

		// Music
		if (preg_match("/^(?:\!|\/|\.|\~)(?:ytadd(?:[\s\n]+))(.+)$/USsi", $text, $m)) {
			try {
				(new YoutubeStream($this->discord, $guild, $channel))->run(trim($m[1]));
			} catch (Error $e) {
				dlog("%s\n", $e->getMessage());
				dlog("%s\n", $e->getTraceAsString());
			}
			return;
		}

		// Select channel
		if (preg_match("/^(?:\!|\/|\.|\~)(sstr)/USsi", $text, $m)) {
			try {
				(new Music($this->discord, $guild, $channel))->sendChannelOption();
			} catch (Error $e) {
				dlog("%s\n", $e->getMessage());
				dlog("%s\n", $e->getTraceAsString());
			}
			return;
		}

		if (preg_match("/^(?:\!|\/|\.|\~)queue/", $text, $m)) {
			$queues = "";
			foreach ((new Queue($guild->id))->getAll() as $k => $queue) {
				$k++;
				$queues .= "{$k}. {$queue}\n";
			}

			if ($queues === "") {
				$reply = "There is no queue for this guild.";
				goto reply;
			}

			foreach (str_split(str_replace("`", "\\`", $queues), 2000 - 15) as $r) {
				$reply[] = "```text\n{$r}\n```";
			}
			goto reply;
		}





reply:
		if (isset($reply)) {
			if (is_array($reply)) {

				$func = null;
				$i = 0;

				$nextd = function (array &$callbacks) use (&$nextd) {
					if (!isset($callbacks[0])) {
						return;
					}

					$thenCallback = function () use (&$callbacks, &$nextd, &$otherwiseCallback) {
						dlog("Message sent!");
						if (!isset($callbacks[1])) {
							return;
						}
						$callbacks[1]()->then(function () use (&$callbacks, &$nextd) {
							array_shift($callbacks);
							array_shift($callbacks);
							$nextd($callbacks);
						})->otherwise($otherwiseCallback);
					};

					$otherwiseCallback = function ($e) use (&$callbacks, &$thenCallback) {
						dlog("Error: %s", $msg = $e->getMessage());
						if (preg_match("/Connection closed before receiving response/", $msg)) {
							dlog("Retrying...");
							$callbacks[0]()->then($thenCallback)->otherwise(function ($e) {
								dlog("Error: %s", $msg = $e->getMessage());
							});
						}
					};

					usleep(500000);
					$callbacks[0]()->then($thenCallback)->otherwise($otherwiseCallback);
				};


				$callbacks = [];
				foreach ($reply as $r) {
					if ($i === 0) {
						$callback = function (array &$callbacks) use ($channel, $r, &$nextd) {
							$channel->sendMessage($r)->then(function () use (&$callbacks, &$nextd) {
								dlog("Message sent!");
								$nextd($callbacks);
							})->otherwise(function ($e) {
								dlog("Error: %s", $e->getMessage());
							});
						};
						$i++;
					} else {
						$callbacks[] = function() use ($channel, $r) {
							return $channel->sendMessage($r);
						};
						$i++;
					}
				}
				$callback($callbacks);

				// $channel->sendMessage($r)->then(function ($message) {
				// 	dlog("Message sent!");
				// })->otherwise(function ($e) {
				// 	dlog("There was an error sending the message: %s\n", $e->getMessage());
				// 	dlog("%s\n", $e->getTraceAsString());
				// });
			} else {
				$channel->sendMessage($reply)->then(function ($message) {
					dlog("Message sent!");
				})->otherwise(function ($e) {
					dlog("There was an error sending the message: %s", $e->getMessage());
					dlog("%s", $e->getTraceAsString());
				});
			}
		}
	}
}
