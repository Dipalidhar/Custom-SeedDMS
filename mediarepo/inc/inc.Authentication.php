<?php
/**
 * Do authentication of users and session management
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Uwe Steinmann
 * @version    Release: @package_version@
 */

require_once("inc.Utils.php");
require_once("inc.ClassNotificationService.php");
require_once("inc.ClassEmailNotify.php");
require_once("inc.ClassSession.php");

$refer = $_SERVER["REQUEST_URI"];
if (!strncmp("/op", $refer, 3)) {
	$refer="";
} else {
	$refer = urlencode($refer);
}
if (!isset($_COOKIE["myrepo_session"])) {
	if($settings->_enableGuestLogin && $settings->_enableGuestAutoLogin) {
		require_once("../inc/inc.ClassSession.php");
		$session = new mediarepo_Session($db);
		if(!$repo_session = $session->create(array('userid'=>$settings->_guestID, 'theme'=>$settings->_theme, 'lang'=>$settings->_language))) {
			header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
			exit;
		}
		$resArr = $session->load($repo_session);
	}	elseif($settings->_autoLoginUser) {
		require_once("../inc/inc.ClassSession.php");
		if(!($user = $repo->getUser($settings->_autoLoginUser))/* || !$user->isGuest()*/) {
			header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
			exit;
		}
		$theme = $user->getTheme();
		if (strlen($theme)==0) {
			$theme = $settings->_theme;
			$user->setTheme($theme);
		}
		$lang = $user->getLanguage();
		if (strlen($lang)==0) {
			$lang = $settings->_language;
			$user->setLanguage($lang);
		}
		$session = new mediarepo_Session($db);
		if(!$repo_session = $session->create(array('userid'=>$user->getID(), 'theme'=>$theme, 'lang'=>$lang))) {
			header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
			exit;
		}
		$resArr = $session->load($repo_session);
	} else {
		header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
		exit;
	}
} else {
	/* Load session */
	$repo_session = $_COOKIE["myrepo_session"];
	$session = new mediarepo_Session($db);
	if(!$resArr = $session->load($repo_session)) {
		setcookie("myrepo_session", $repo_session, time()-3600, $settings->_httpRoot); //delete cookie
		header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
		exit;
	}
}

/* Update last access time */
if((int)$resArr['lastAccess']+60 < time())
	$session->updateAccess($repo_session);

/* Load user data */
$user = $repo->getUser($resArr["userID"]);
if (!is_object($user)) {
	setcookie("myrepo_session", $repo_session, time()-3600, $settings->_httpRoot); //delete cookie
	header("Location: " . $settings->_httpRoot . "out/out.Login.php?referuri=".$refer);
	exit;
}

if($user->isAdmin()) {
	if($resArr["su"]) {
		$user = $repo->getUser($resArr["su"]);
	} else {
	//	$session->resetSu();
	}
}
$theme = $resArr["theme"];
$lang = $resArr["language"];

$repo->setUser($user);

$notifier = new mediarepo_NotificationService();

if(isset($GLOBALS['mediarepo_HOOKS']['notification'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['notification'] as $notificationObj) {
		if(method_exists($notificationObj, 'preAddService')) {
			$notificationObj->preAddService($repo, $notifier);
		}
	}
}

if($settings->_enableEmail) {
	$notifier->addService(new mediarepo_EmailNotify($repo, $settings->_smtpSendFrom, $settings->_smtpServer, $settings->_smtpPort, $settings->_smtpUser, $settings->_smtpPassword));
}

if(isset($GLOBALS['mediarepo_HOOKS']['notification'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['notification'] as $notificationObj) {
		if(method_exists($notificationObj, 'postAddService')) {
			$notificationObj->postAddService($repo, $notifier);
		}
	}
}

/* Include additional language file for view
 * This file must set $LANG[xx][]
 */
if(file_exists($settings->_rootDir . "view/".$theme."/languages/" . $lang . "/lang.inc")) {
	include $settings->_rootDir . "view/".$theme."/languages/" . $lang . "/lang.inc";
}

/* Check if password needs to be changed because it expired. If it needs
 * to be changed redirect to out/out.ForcePasswordChange.php. Do this
 * check only if password expiration is turned on, we are not on the
 * page to change the password or the page that changes the password, and
 * it is not admin */

if (!$user->isAdmin()) {
	if($settings->_passwordExpiration > 0) {
		if(basename($_SERVER['SCRIPT_NAME']) != 'out.ForcePasswordChange.php' && basename($_SERVER['SCRIPT_NAME']) != 'op.EditUserData.php') {
			$pwdexp = $user->getPwdExpiration();
			if(substr($pwdexp, 0, 10) != '0000-00-00') {
				$pwdexpts = strtotime($pwdexp); // + $pwdexp*86400;
				if($pwdexpts > 0 && $pwdexpts < time()) {
					header("Location: ../out/out.ForcePasswordChange.php");
					exit;
				}
			}
		}
	}
}

/* Update cookie lifetime */
if($settings->_cookieLifetime) {
	$lifetime = time() + intval($settings->_cookieLifetime);
	/* Turn off http only cookies if jumploader is enabled */
	setcookie("myrepo_session", $repo_session, $lifetime, $settings->_httpRoot, null, null, !$settings->_enableLargeFileUpload);
}
?>
