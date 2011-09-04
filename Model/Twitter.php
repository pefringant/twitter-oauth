<?php
/**
 * Twitter model class
 * Uses a TwitterOAuth DataSource adapted from the great work of :
 *
 * Daniel Hofstetter
 * @link http://code.42dh.com/oauth/
 *
 * Michael "MiDri" Riddle
 * @link http://bakery.cakephp.org/articles/view/twitter-datasource
 *
 * Alexandru Ciobanu
 * @link http://github.com/ics/twitter_datasource
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */
class Twitter extends TwitterAppModel {

	var $name = 'Twitter';

	var $useDbConfig = 'twitter';

/**
 * Constructor
 */
	function __construct() {
		// Datasource config
		App::import(array(
			'type' => 'File',
			'name' => 'Twitter.TWITTER_CONFIG',
			'file' => App::pluginPath('Twitter').'Config'.DS.'twitter.php'
		));

		$config =& new TWITTER_CONFIG();
		ConnectionManager::create('twitter', $config->twitter);
	}

/**
 * Posts a new status on Twitter
 *
 * @param string $status
 */
	function postStatus($status = '') {
		$ds = ConnectionManager::getDataSource('twitter');

		$ds->statuses_update(array('status' => $status));
	}

/**
 * Calls a URL shortening service
 * http://is.gd
 *
 * @param string $url
 * @return string Shortened URL
 */
	function shorten($url) {
		App::import('Core', 'HttpSocket');

		$socket =& new HttpSocket();

		$request = sprintf('http://is.gd/api.php?longurl=%s', rawurlencode($url));

		return $socket->get($request);
	}


/**
 * Formats a Twitter status
 *
 * @param string $message
 * @param string $url Optionnal URL to read the full story
 * @param string $ending If the status is over 140 chars, it will be cut and followed by the $ending string.
 * @return string Twitter compatible status
 */
	function formatStatus($message, $url = null, $ending = '...') {
		App::import('Core', 'Multibyte');

		$max = 140;

		if ($url) {
			$url = $this->shorten($url);

			$max -= mb_strlen($url) + 1;
		}

		if (mb_strlen($message) > $max) {
			$message  = mb_substr($message, 0, $max - mb_strlen($ending));
			$message .= $ending;
		}

		if (!$url) {
			return $message;
		}

		return sprintf('%s %s', $message, $url);
	}
}