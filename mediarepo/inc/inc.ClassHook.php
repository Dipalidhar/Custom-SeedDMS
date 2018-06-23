<?php
/**
 * Implementation of hook response class
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Parent class for all hook response classes
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_Hook_Response {
	protected $data;

	protected $error;

	public function __construct($error = false, $data = null) {
		$this->data = $data;
		$this->error = $error;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}
}

