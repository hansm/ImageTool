<?php
namespace ImageTool;

use PDO;

/**
 * Initiate some resources
 */
class Resources {

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var \PDO
	 */
	private $db;

	public function __construct(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Get database connection
	 *
	 * @return \PDO
	 * @throws \PDOException
	 */
	public function getDb() {
		if (!isset($this->db)) {
			$this->db = new PDO('mysql:host='. $this->settings['db_host'] .';dbname='. $this->settings['db_name'] .';charset=utf8',
							$this->settings['db_user'], $this->settings['db_pass']);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return $this->db;
	}

}
