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

require_once "inc.ClassAuthentication.php";

/**
 * Abstract class to authenticate user against ѕeedrepo database
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010-2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_DbAuthentication extends mediarepo_Authentication {
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
	 * @param string $username
	 * @param string $password
	 * @return object|boolean user object if authentication was successful otherwise false
	 */
	public function authenticate($username, $password) { /* {{{ */
		$settings = $this->settings;
		$repo = $this->repo;

		// Try to find user with given login.
		if($user = $repo->getUserByLogin($username)) {
			$userid = $user->getID();

			// Check if password matches (if not a guest user)
			// Assume that the password has been sent via HTTP POST. It would be careless
			// (and dangerous) for passwords to be sent via GET.
			if (md5($password) != $user->getPwd()) {
				/* if counting of login failures is turned on, then increment its value */
				if($settings->_loginFailure) {
					$failures = $user->addLoginFailure();
					if($failures >= $settings->_loginFailure)
						$user->setDisabled(true);
				}
				$user = false;
			}
		}

		return $user;
	} /* }}} */
}
