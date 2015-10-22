<?php
namespace ImageTool;

use Exception;

class Log {
	
	const DEFAULT_LOG = 'log';

	private static $loggers = [];

	private $name;

	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Get logger instance
	 *
	 * @param string $name
	 * @return \ImageTool\Log
	 */
	public static function get($name = self::DEFAULT_LOG) {
		if (!isset(self::$loggers[$name])) {
			self::$loggers[$name] = new Log($name);
		}
		return self::$loggers[$name];
	}

	public function debug($message, Exception $e = null) {
		$this->writeLog('DEBUG', $message, $e);
	}

	public function warn($message, Exception $e = null) {
		$this->writeLog('WARN', $message, $e);
	}

	public function error($message, Exception $e = null) {
		$this->writeLog('ERROR', $message, $e);
	}

	private function writeLog($logType, $message, Exception $e = null) {
		if (!empty($e)) {
			$message .= "\t". $e;
		}

		\file_put_contents(\ROOT. 'Log' . \DIRECTORY_SEPARATOR . $this->name,
			"[". \gmdate('r') ."]\t[". $logType ."]\t". $message ."\n",
			\FILE_APPEND);
	}

}
