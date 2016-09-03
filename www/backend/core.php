<?php
class Core {

	private $_errors;
	private $_domain;

	function __construct() {
		$this->_domain = $_SERVER['HTTP_HOST'];
	}

	/**
	 * Noce method to get the codument root
	 */
	public function getDocumentRoot() {
		return $_SERVER['DOCUMENT_ROOT'];
	}

	/**
	 * Used for redirection
	 */
	public function redirect($url = '/') {

		ob_clean();

		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://' . $this->_domain . $url);

		session_write_close();

		exit;
	}


	/**
	 * Get the global  $_GET value passed on form submit
	 *
	 * @param string $key the key used in form get
	 * @param string $default default value when form field is not set
	 */
	public function getValue($key, $default = '') {

		if(empty($key))
			return false;

		$value = isset($_GET[$key]) ? filter_var($_GET[$key]) : $default;

		return $value;
	}


	/**
	 * Get the global  $_POST value passed on form submit
	 *
	 * @param string $key the key used in form post
	 * @param string $default default value when form field is not set
	 */
	public function postValue($key, $default = '') {

		if(empty($key))
			return false;

		$value = isset($_POST[$key]) ? $_POST[$key] : $default;

		return $value;
	}


	/**
	 * Used in debuging
	 * @param string $value
	 */
	public function output($value) {
		echo '<code>';
		print_r($value);
		echo '</code>';
	}

	/**
	 * Returns the url parameters at a position or form a position to end
	 *
	 * @param integer $position   the position from which you need to extract the url part
	 * @param string  $seperator  optional seperator which is used when we need to extract part of string seperated by -
	 * @param boolean $full 	  if true it will extract rest of url part from the position
	 */
	public function getUrlParameters($position = 1, $separator = '/', $full = false) {

		// Default return
		$return = '';

		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		$params = explode($separator, $path);
		array_shift($params);

		// Get the default return value
		$return = isset($params[$position])? $params[$position]: null;

		if($full) {
			$return = implode($separator, array_slice($params, $position));
		}

		return $return;
	}


	/**
	 * Save a value in session against a key
	 * This can be extended to use memcached or redis
	 *
	 * @param string $key the key name
	 * @param string $value the value to be stored
	 */
	public function setCache($key, $value = null) {

		// Use session for now
		$_SESSION[$key] = $value;
	}


	/**
	 * Get a saved alue from session against a key
	 * This can be extended to use memcached or redis
	 *
	 * @param string $key the key name for which the value should be returned
	 */
	public function getCache($key) {

		if(!isset($_SESSION[$key]))
			return null;

		return $_SESSION[$key];
	}

	/**
	 * Accumulate the error in errors array.
	 * Which will then be used in geterrors()
	 * @param  string $error
	 * @return boolean
	 */
	public function setError($error) {

		if(empty($error))
			return false;

		$this->_errors[] = $error;

		return true;
	}


	/**
	 * Returns the error accumulated by seterror()
	 * There are 2 ways this method can return data
	 * ARRAY => As the array of errors
	 * PLAIN => The error array will be imploded using PHP_EOL
	 * @param  string $type
	 * @return mixed
	 */
	public function getErrors($type = 'ARRAY') {

		if(empty($this->_errors))
			return false;

		switch($type) {

			case 'ARRAY':
				return $this->_errors;

			case 'PLAIN':
				return implode(PHP_EOL, $this->_errors);

		}

		return;
	}


	/**
	 * Checks if any error is set and returns true on success
	 * @return boolean
	 */
	public function hasErrors() {
		return !empty($this->_errors);
	}


	/**
	 * Set a session based message available for only one request
	 * @param  string $message
	 * @param  string $type  type can be INFO, DANGER, WARNING
	 * @return boolean
	 */
	public function setFlashMessage($message, $type = 'DANGER') {

		if (empty($message))
			return false;

		$message = !is_array($message) ? array($message) : $message;

		$messageObj = new stdClass;
		$messageObj->content = implode(PHP_EOL, $message);
		$messageObj->type = $type;

		$_SESSION['SITE_FLASHMESSAGE'] = $messageObj;

		return true;
	}


	/**
	 * Once this method is called the message is erased
	 * @return string
	 */
	public function getFlashMessage($type = '') {

		$messageObj = isset($_SESSION['SITE_FLASHMESSAGE']) ? $_SESSION['SITE_FLASHMESSAGE'] : '';

		if(!is_object($messageObj))
			return;

		unset($_SESSION['SITE_FLASHMESSAGE']);

		switch ($type){

			case 'PLAIN':
				return $messageObj->content;

			case 'OBJECT':
				return $messageObj;

			default:
				return '<div class="alert alert-' . strtolower($messageObj->type) . '">' . nl2br($messageObj->content) . '</div>';

		}

	}

}