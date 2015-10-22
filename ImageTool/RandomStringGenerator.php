<?php
namespace ImageTool;

class RandomStringGenerator {

	private static $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';

	public static function get($length) {
		$length = (int) $length;
		if ($length <= 0) {
			throw new \InvalidArgumentException('Invalid length for random string.');
		}

		$maxCharNo = strlen(self::$characters) - 1;
		$randomString = '';
		for ($i = 0; $i < $length; ++$i) {
			$randomString .= self::$characters[mt_rand(0, $maxCharNo)];
		}
		return $randomString;
	}

}
