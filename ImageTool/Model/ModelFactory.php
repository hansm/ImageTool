<?php
namespace ImageTool\Model;

use ImageTool\Resources;

class ModelFactory {

	/**
	 * @var \ImageTool\Resources 
	 */
	private $resources;

	private $imageModel;

	public function __construct(Resources $resources) {
		$this->resources = $resources;
	}

	/**
	 * Get image model instance
	 *
	 * @return \ImageTool\Model\ImageModel
	 */
	public function getImageModel() {
		if (!isset($this->imageModel)) {
			$this->imageModel = new ImageModel($this->resources);
		}
		return $this->imageModel;
	}

}