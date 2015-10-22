<?php
namespace ImageTool\Model;

use Exception;

class ModelException extends Exception {

	public function __construct($message, Exception $e = null) {
		parent::__construct($message, 0, $e);
	}

}
