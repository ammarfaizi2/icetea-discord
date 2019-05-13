<?php

namespace TeaDiscord;

use Discord\Discord;
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
		$id = $this->message->id;
		$guild_id = $this->message->channel->guild_id;
		$channel_id = $this->message->channel_id;
		$text = $this->message->content;
		$type = $this->message->type;
		$user = $this->message->author->user;

		var_dump($id, $guild_id, $channel_id, $text, $type, $user);
	}
}
