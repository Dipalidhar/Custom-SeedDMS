<?php
/**
 * Implementation of user authentication
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2016 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Abstract class to authenticate user
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
abstract class mediarepo_Authentication {
	/**
	 * @var object $repo object of repo
	 * @access protected
	 */
	private $repo;

	/**
	 * @var object $settings MediaREPO Settings
	 * @access protected
	 */
	private $settings;

	function __construct($repo, $settings) { /* {{{ */
		$this->repo = $repo;
		$this->settings = $settings;
	} /* }}} */

	/**
	 * Do Authentication
	 *
	 * This function must check the username and login. If authentication succeeds
	 * the user object otherwise false must be returned. If authentication fails
	 * the number of failed logins should be incremented and account disabled.
	 *
	 * @param string $username
	 * @param string $password
	 * @return object|boolean user object if authentication was successful otherwise false
	 */
	abstract function authenticate($username, $password);
}
