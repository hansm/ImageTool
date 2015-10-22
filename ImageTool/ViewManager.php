<?php
namespace ImageTool;

class ViewManager {

	const CONTENT_TYPE_JSON = 'json';
	const CONTENT_TYPE_HTML = 'html';

	const CODE_OK = 200;
	const CODE_NOT_FOUND = 404;
	const CODE_INTERNAL_ERROR = 500;

	/**
	 * @var string
	 */
	private $contentType;

	/**
	 * @var int
	 */
	private $responseCode;

	/**
	 * Variables for view
	 *
	 * @var array
	 */
	private $var;

	/**
	 * View to load
	 *
	 * @var string
	 */
	private $viewName;

	public function __construct() {
		$this->contentType = self::CONTENT_TYPE_HTML;
		$this->var = [];
	}

	public function setJson() {
		$this->contentType = self::CONTENT_TYPE_JSON;
	}

	public function setHtml() {
		$this->contentType = self::CONTENT_TYPE_HTML;
	}

	/**
	 * @param string $view
	 */
	public function setViewName($view) {
		$this->viewName = $view;
	}

	/**
	 * @param int $code
	 */
	public function setResponseCode($code) {
		$this->responseCode = $code;
	}

	/**
	 * Set view variables
	 *
	 * @param type $var
	 * @param type $val
	 */
	public function set($var, $val = null) {
		if ($val === null && is_array($var)) {
			foreach ($var as $variable => $value) {
				$this->var[$variable] = $value;
			}
		} else {
			$this->var[$var] = $val;
		}
	}

	public function error($message, $responseCode = null) {
		$this->set([
			'error'		=>	true,
			'message'	=>	$message
		]);
		$this->setResponseCode($responseCode);
		$this->setViewName('Error');
	}

	/**
	 * Output view
	 *
	 * @throws ViewException
	 */
	public function output() {
		if (!empty($this->responseCode)) {
			\http_response_code($this->responseCode);
		}

		if ($this->contentType === self::CONTENT_TYPE_HTML) {
			$this->outputHtml();
		} else if ($this->contentType === self::CONTENT_TYPE_JSON) {
			$this->outputJson();
		} else {
			throw new ViewException('Invalid content type.');
		}
	}

	private function outputHtml() {
		$viewFileName = ROOT
			. 'ImageTool'
			. DIRECTORY_SEPARATOR
			. 'View'
			. DIRECTORY_SEPARATOR
			. $this->viewName
			. '.php';

		extract($this->var);
		if (!file_exists($viewFileName)) {
			throw new ViewException('View file not found.');
		}

		require $viewFileName;
	}

	private function outputJson() {
		header('Content-type: application/json; charset=utf-8');
		echo \json_encode($this->var);
	}

	public static function errorPage($message, $responseCode = null) {
		$viewManager = new ViewManager();
		$viewManager->error($message, $responseCode);
		$viewManager->output();
	}

}
