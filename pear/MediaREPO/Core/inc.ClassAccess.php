<?php
/**
 * Implementation of user and group access object
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal, 2006-2008 Malcolm Cowe,
 *             2010 Uwe Steinmann
 * @version    Release: 5.1.7
 */

/**
 * Class to represent a user access right.
 * This class cannot be used to modify access rights.
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal, 2006-2008 Malcolm Cowe,
 *             2010 Uwe Steinmann
 * @version    Release: 5.1.7
 */
class mediarepo_Core_UserAccess { /* {{{ */

    /**
     * @var mediarepo_Core_User
     */
	var $_user;

    /**
     * @var
     */
	var $_mode;

    /**
     * mediarepo_Core_UserAccess constructor.
     * @param $user
     * @param $mode
     */
	function __construct($user, $mode) {
		$this->_user = $user;
		$this->_mode = $mode;
	}

    /**
     * @return int
     */
	function getUserID() { return $this->_user->getID(); }

    /**
     * @return mixed
     */
	function getMode() { return $this->_mode; }

    /**
     * @return bool
     */
	function isAdmin() {
		return ($this->_mode == mediarepo_Core_User::role_admin);
	}

    /**
     * @return mediarepo_Core_User
     */
	function getUser() {
		return $this->_user;
	}
} /* }}} */


/**
 * Class to represent a group access right.
 * This class cannot be used to modify access rights.
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal, 2006-2008 Malcolm Cowe, 2010 Uwe Steinmann
 * @version    Release: 5.1.7
 */
class mediarepo_Core_GroupAccess { /* {{{ */

    /**
     * @var mediarepo_Core_Group
     */
	var $_group;

    /**
     * @var
     */
	var $_mode;

    /**
     * mediarepo_Core_GroupAccess constructor.
     * @param $group
     * @param $mode
     */
	function __construct($group, $mode) {
		$this->_group = $group;
		$this->_mode = $mode;
	}

    /**
     * @return int
     */
	function getGroupID() { return $this->_group->getID(); }

    /**
     * @return mixed
     */
	function getMode() { return $this->_mode; }

    /**
     * @return mediarepo_Core_Group
     */
	function getGroup() {
		return $this->_group;
	}
} /* }}} */