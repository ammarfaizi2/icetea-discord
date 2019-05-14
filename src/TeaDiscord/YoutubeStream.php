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
final class YoutubeStream
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
	 * @return bool
	 */
	private function isPlaying(): bool
	{
		global $cfg;
		return file_exists($cfg["storage_path"]."/guild/{$this->guild->id}/stream_playing.lock");
	}

	/**
	 * @param string $youtubeId
	 * @return void
	 */
	public function run(string $youtubeId): void
	{
		if ($this->hasSelectedChannel()) {
			if (!filter_var($youtubeId, FILTER_VALIDATE_URL)) {
				$youtubeId = "https://www.youtube.com/watch?v={$youtubeId}";
			}
			$queue = new Queue($this->guild->id);
			$queue->enqueue($youtubeId);
			unset($queue);
			if (!$this->isPlaying()) {
				$this->doStream();
			}
		} else {
			$this->channel->sendMessage("You haven't selected the stream channel!")->then(
				function () {
					$this->sendChannelOption();
				}
			);
			
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
	 * @param string $id
	 * @return string
	 */
	private function getFile(string $id): string
	{
		global $cfg;
		$cacheFile = $cfg["storage_path"]."/storage/stream/mp3/cache.json";

		$cached = [];
		if (file_exists($cacheFile)) {
			$cached = json_decode(file_get_contents($cacheFile), true);
		}

		if (!is_array($cached)) {
			$cached = [];
		}

		if (isset($cached[$id])) {
			return $cfg["storage_path"]."/stream/mp3/".$cached[$id];
		}

		is_dir("/var/cache/youtube-dl") or mkdir("/var/cache/youtube-dl");
		$fd = [
			["pipe", "r"],
			["pipe", "w"],
			["file", "php://stdout", "w"]
		];
		$ytdl = trim(shell_exec("which youtube-dl"));
		$py = trim(shell_exec("which python"));
		$id = escapeshellarg($id);
		$proxy = "127.0.0.1:".rand(49050, 49090);
		$me = proc_open(
			"exec {$py} {$ytdl} -f best --proxy \"socks5://{$proxy}\" --extract-audio --audio-format mp3 {$id} --cache-dir /var/cache/youtube-dl",
			$fd,
			$pipes,
			$cfg["storage_path"]."/stream/mp3"
		);
		if (preg_match("/\[ffmpeg\] Destination: (.*.mp3)/Usi", stream_get_contents($pipes[1]), $m)) {
			$cached[$id] = $m[1];
			file_put_contents($cacheFile, json_encode($cached, JSON_UNESCAPED_SLASHES));
			return $cached[$id];
		}
		proc_close($me);
		return false;
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
			cli_set_process_title("stream-worker --exec-json --queue");
			$queue = new Queue($this->guild->id);
			while (($r = $queue->dequeue()) !== NULL) {
				shell_exec(
					"exec ".
					escapeshellarg(PHP_BINARY)." ".
					escapeshellarg($cfg["basepath"]."/bin/stream.php")." ".
					escapeshellarg(json_encode(
						[
							"guild_id" => $this->guild->id,
							"channel_id" => file_get_contents($cfg["storage_path"]."/guild/{$this->guild->id}/stream_channel"),
							"file" => $this->getFile($r),
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
			}
			exit;
		}

		return $pid;
	}

	/**
	 * @return mixed
	 */
	public function sendChannelOption()
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

		return $this->channel->sendMessage($reply);
	}
}
