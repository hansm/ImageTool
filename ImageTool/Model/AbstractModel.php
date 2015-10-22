<?php
namespace ImageTool\Model;

use ImageTool\Resources;

class AbstractModel {

	/**
	 * @var \ImageTool\Resources
	 */
	protected $resources;

	public function __construct(Resources $resources) {
		$this->resources = $resources;
	}

}
