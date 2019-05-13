<?php

namespace TeaDiscord;

use Discord\Discord;
use Discord\Voice\VoiceClient;
use Discord\Parts\Channel\Message;

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

		$guild = $this->discord->guilds->get("id", $guild_id);
		$channel = $guild->channels->get("id", $channel_id);

		// Shell exec.
		if (preg_match("/^(?:\!|\/|\.|\~)(?:cx(?:[\s\n]+))(.+)$/USsi", $text, $m)) {
			if (in_array("{$user->id}@{$user->username}", $cfg["sudoers"])) {
				$cmd = trim(shell_exec("/bin/bash -c ".escapeshellarg($m[1])." 2>&1"));
				$reply = [];
				foreach (str_split(str_replace("`", "\\`", $cmd), 2000 - 9) as $r) {
					$reply[] = "```{$r}```";
				}
			} else {
				$reply = "@{$user->username}#{$user->discriminator} is not in the sudoers files. This incident will be reported.";
			}
			goto reply;
		}


		if (preg_match("/zcc/", $text, $m)) {
			$channel = $guild->channels->get("id", "446634690015657987");
			$this->discord->joinVoiceChannel($channel, false, false, null)->then(
				function (VoiceClient $vc) {
					$vc->setBitrate(128000)->then(
						function () use ($vc) {
							$vc->playFile("/root/server/app/discord_/storage/stream/mp3
♫ Glow In The Darkness - Nightcore ♫ ( ^∇^ )-edEWLMWoEsc.mp3");
						}
					)->otherwise(function($e){ 
						printf("Error: %s\n", $e->getMessage());
					});
				}
			);




			return;
		}












reply:
		if (isset($reply)) {
			if (is_array($reply)) {
				foreach ($reply as $r) {
					$channel->sendMessage($r)->then(function ($message) {
						dlog("Message sent!");
					})->otherwise(function ($e) {
						dlog("There was an error sending the message: %s\n", $e->getMessage());
						dlog("%s\n", $e->getTraceAsString());
					});
				}
			} else {
				$channel->sendMessage($reply)->then(function ($message) {
					dlog("Message sent!");
				})->otherwise(function ($e) {
					dlog("There was an error sending the message: %s\n", $e->getMessage());
					dlog("%s\n", $e->getTraceAsString());
				});
			}
		}
	}
}
