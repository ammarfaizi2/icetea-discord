<?php

namespace TeaDiscord;

use Error;
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
	private $discord;

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
		try {

			$this->discord->on("ready", function ($discord) {
				printf("Bot is ready\n");
				$discord->on("message", function ($message) use ($discord) {
					try {
						(new Response($discord, $message))->run();
					} catch (Error $e) {
						printf("\n\nAn error occured!\n");
						var_dump($e->getMessage(), $e->getFile(), $e->getLine());		
					}
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
