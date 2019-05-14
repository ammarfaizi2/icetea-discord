<?php

namespace TeaDiscord;

use Error;
use Exception;
use Discord\Discord;
use Discord\Voice\VoiceClient;
use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;

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
		file_put_contents($cfg["storage_path"]."/guild/{$guild_id}/stream_playing.lock", time());
		register_shutdown_function(function () use ($guild_id) {
			global $cfg;
			@unlink($cfg["storage_path"]."/guild/{$guild_id}/stream_playing.lock");
		});

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
	 * @param \Discord\Parts\Channel\Message $message
	 * @param \Discord\Voice\VoiceClient     $vc
	 * @return void
	 */
	private function handleMessage(Message $message, VoiceClient $vc): void
	{
		global $cfg;

		$id = $message->id;
		$guild_id = $message->channel->guild_id;
		$channel_id = $message->channel_id;
		$text = $message->content;
		$type = $message->type;
		$user = $message->author->user;

		if (($guild_id === $this->guild_id) && ($channel_id === $this->curChannel)) {

			$guild = $this->discord->guilds->get("id", $this->guild_id);
			$channel = $guild->channels->get("id", $this->curChannel);

			if (preg_match("/^(?:\!|\/|\.|\~)(?:stop)$/USsi", $text, $m)) {
				$vc->stop()->then(
					function () use ($channel, $vc) {
						$vc->unpause();
						dlog("Stream is stopped!");
						$channel->sendMessage("Stream is stopped!")->then(function () {
							dlog("Message sent!");
						})->otherwise(function ($e) {
							dlog("There was an error sending the message: %s\n", $e->getMessage());
							dlog("%s\n", $e->getTraceAsString());
						});
					}
				)->otherwise(
					function ($e) {
						dlog("Resume stream error: %s", $e->getMessage());
					}
				);
				return;
			}

			if (preg_match("/^(?:\!|\/|\.|\~)(?:pause)$/USsi", $text, $m)) {
				$vc->pause()->then(
					function () use ($channel) {
						dlog("Stream is paused!");
						$channel->sendMessage("Stream is paused!")->then(function () {
							dlog("Message sent!");
						})->otherwise(function ($e) {
							dlog("There was an error sending the message: %s\n", $e->getMessage());
							dlog("%s\n", $e->getTraceAsString());
						});
					}
				)->otherwise(
					function ($e) {
						dlog("Resume stream error: %s", $e->getMessage());
					}
				);
				return;
			}

			if (preg_match("/^(?:\!|\/|\.|\~)(?:unpause|resume|play)$/USsi", $text, $m)) {
				$vc->unpause()->then(
					function () use ($channel) {
						dlog("Stream is resumed!");
						$channel->sendMessage("Stream is resumed!")->then(function () {
							dlog("Message sent!");
						})->otherwise(function ($e) {
							dlog("There was an error sending the message: %s\n", $e->getMessage());
							dlog("%s\n", $e->getTraceAsString());
						});
					}
				)->otherwise(
					function ($e) {
						dlog("Resume stream error: %s", $e->getMessage());
					}
				);
				return;
			}
		}

	}

	/**
	 * 
	 * @return void
	 */
	public function run(): void
	{
		global $cfg;
		try {

			$this->discord->on("ready", function ($discord) {

				$guild = $this->discord->guilds->get("id", $this->guild_id);
				$channel = $guild->channels->get("id", $this->channel_id);
				$curChannel = $guild->channels->get("id", $this->curChannel);

				$this->discord->joinVoiceChannel($channel, false, false, null)
					->then(function (VoiceClient $vc) use ($curChannel) {

							$curChannel->sendMessage("Streamer has been initialized!")->then(function () use ($curChannel) {
								dlog("Message sent!");
								$curChannel->sendMessage("Streaming `{$this->file}`...");
							})->otherwise(function ($e) {
								dlog("There was an error sending the message: %s\n", $e->getMessage());
								dlog("%s\n", $e->getTraceAsString());
							});

							$this->discord->on("message", function ($message) use ($vc) {
								$this->handleMessage($message, $vc);
							});

							$vc->setBitrate(128000)->then(
								function () use ($vc, $curChannel) {
									$vc->playFile($this->file)->then(
										function () use ($vc, $curChannel) {
											$vc->close()->then(
												function () use ($curChannel) {
													$curChannel->sendMessage("Stream has finished!")->then(function () {
														dlog("Message sent!");
														global $cfg;
														shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
														@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
														shell_exec("/bin/kill -9 ".getmypid());
														exit;
													})->otherwise(function ($e) {
														dlog("There was an error sending the message: %s\n", $e->getMessage());
														dlog("%s\n", $e->getTraceAsString());
														global $cfg;
														shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
														@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
														shell_exec("/bin/kill -9 ".getmypid());
														exit;
													});
												}
											)->otherwise(
												function ($e) {
													dlog("There was an error closing stream the message: %s\n", $e->getMessage());
													dlog("%s\n", $e->getTraceAsString());
													global $cfg;
													shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
													@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
													shell_exec("/bin/kill -9 ".getmypid());
													exit;
												}
											);
										}
									)->otherwise(function ($e) {
										global $cfg;
										printf("Error: %s\n", $e->getMessage());
										shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
										@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
										shell_exec("/bin/kill -9 ".getmypid());
										exit;
									});
								}
							)->otherwise(
								function($e) {
									dlog("Error: %s\n", $e->getMessage());
									$vc->close()->then(
										function () use ($curChannel) {
											$curChannel->sendMessage("Stream has finished!")->then(function () {
												dlog("Message sent!");
												global $cfg;
												shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
												@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
												shell_exec("/bin/kill -9 ".getmypid());
												exit;
											})->otherwise(function ($e) {
												dlog("There was an error sending the message: %s\n", $e->getMessage());
												dlog("%s\n", $e->getTraceAsString());
												global $cfg;
												shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
												@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
												shell_exec("/bin/kill -9 ".getmypid());
												exit;
											});
										}
									)->otherwise(
										function ($e) {
											dlog("There was an error closing stream the message: %s\n", $e->getMessage());
											dlog("%s\n", $e->getTraceAsString());
											global $cfg;
											shell_exec("/bin/sh ".escapeshellarg($cfg["basepath"]."/bin/kill_dca.sh"));
											@unlink($cfg["storage_path"]."/guild/{$this->guild_id}/stream_playing.lock");
											shell_exec("/bin/kill -9 ".getmypid());
											exit;
										}
									);
								}
							);
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
