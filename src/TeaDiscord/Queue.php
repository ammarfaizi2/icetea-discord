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
final class Queue
{
	/**
	 * @var array
	 */
	private $queue = [];

	/**
	 * @var string
	 */
	private $queueFile;

	/**
	 * @param string $guild_id
	 *
	 * Constructor.
	 */
	public function __construct(string $guild_id)
	{
		global $cfg;
		$this->queueFile = $cfg["storage_path"]."/guild/{$guild_id}/stream_queue.json";
		$this->loadState();
	}

	/**
	 * @param string $youtubeId
	 * @return void
	 */
	public function enqueue(string $youtubeId): void
	{
		array_push($this->queue, $youtubeId);
		$this->writeState();
		$this->loadState();
	}

	/**
	 * @return mixed
	 */
	public function dequeue()
	{
		$this->loadState();
		$peak = array_shift($this->queue);
		$this->writeState();
		return $peak;
	}

	/**
	 * @return void
	 */
	public function writeState(): void
	{
		file_put_contents($this->queueFile, json_encode($this->queue, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}

	/**
	 * @return void
	 */
	public function loadState(): void
	{
		if (file_exists($this->queueFile)) {
			$this->queue = json_decode(file_get_contents($this->queueFile), true);
		}

		if (!is_array($this->queue)) {
			$this->queue = [];
		}
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->writeState();
	}
}
