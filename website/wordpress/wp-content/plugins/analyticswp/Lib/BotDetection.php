<?php

namespace AnalyticsWP\Lib;

use DeviceDetector\Parser\Bot as BotParser;


class BotDetection
{
    /**
     * Checks if the current request should be blocked based on various criteria.
     *
     * @param array $request The request array, typically $_POST or similar.
     * @return bool Returns true if the request should be blocked, false otherwise.
     */
    public static function should_block_current_request(array $request): bool
    {
        return self::is_bot();
    }

    public static function is_bot(): bool
    {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        return self::is_bot_by_user_agent($user_agent);
    }

    /**
     * Determines if the provided user agent belongs to a known bot.
     *
     * @param string|null $user_agent The user agent to check. If not provided, the current user agent will be used.
     * @return bool Returns true if the user agent is a known bot, false otherwise.
     */
    public static function is_bot_by_user_agent($user_agent): bool
    {
        //////////////////////////////////////////////////
        // Using handwritten logic as the first gatekeeper
        if (is_null($user_agent)) {
            return false;
        }

        ///////////////////////////////////////////////////////
        // Using DeviceDetector library as the final gatekeeper
        $botParser = new BotParser();
        $botParser->setUserAgent($user_agent);
        $botParser->discardDetails();
        $result = $botParser->parse();

        return (!is_null($result));
    }
}
