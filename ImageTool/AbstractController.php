<?php
namespace ImageTool;

use ImageTool\Model\ModelFactory;

abstract class AbstractController {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \ImageTool\ViewManager
	 */
	protected $view;

	/**
	 * @var \ImageTool\Resources
	 */
	protected $resources;

	/**
	 * @var \ImageTool\Model\ModelFactory
	 */
	protected $modelFactory;

	public function __construct(array $settings) {
		$this->settings = $settings;
		$this->resources = new Resources($settings);
		$this->modelFactory = new ModelFactory($this->resources);
		$this->view = new ViewManager();
	}

	/**
	 * Set view variables
	 *
	 * @param string|array $var
	 * @param string|null $val
	 */
	public function set($var, $val = null) {
		$this->view->set($var, $val);
	}

	public function output() {
		$this->view->output();
	}

}
