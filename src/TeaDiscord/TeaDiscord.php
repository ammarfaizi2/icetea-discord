<?php

namespace TeaDiscord;

use Discord\Discord;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaDiscord
 * @version 0.0.1
 */
final class TeaDiscord
{
	/**
	 * @param \Discord\Discord
	 */

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		global $cfg;
		$this->discord = new Discord(["token" => $cfg["discord_bot_token"]]);
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		
	}
}
