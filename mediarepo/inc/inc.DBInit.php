<?php
//    MyREPO. Document Management System
//    Copyright (C) 2002-2005 Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010-2013 Uwe Steinmann
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

if(isset($GLOBALS['mediarepo_HOOKS']['initDB'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['initDB'] as $hookObj) {
		if (method_exists($hookObj, 'pretInitDB')) {
			$hookObj->preInitDB(array('settings'=>$settings));
		}
	}
}

$db = new mediarepo_Core_DatabaseAccess($settings->_dbDriver, $settings->_dbHostname, $settings->_dbUser, $settings->_dbPass, $settings->_dbDatabase);
$db->connect() or die ("Could not connect to db-server \"" . $settings->_dbHostname . "\"");

if(isset($GLOBALS['mediarepo_HOOKS']['initDB'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['initDB'] as $hookObj) {
		if (method_exists($hookObj, 'postInitDB')) {
			$hookObj->postInitDB(array('db'=>$db, 'settings'=>$settings));
		}
	}
}

if(isset($GLOBALS['mediarepo_HOOKS']['initREPO'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['initREPO'] as $hookObj) {
		if (method_exists($hookObj, 'pretInitREPO')) {
			$hookObj->preInitREPO(array('db'=>$db, 'settings'=>$settings));
		}
	}
}

$repo = new mediarepo_Core_REPO($db, $settings->_contentDir.$settings->_contentOffsetDir);

if(!$settings->_doNotCheckDBVersion && !$repo->checkVersion()) {
	echo "Database update needed.";
	exit;
}

$repo->setRootFolderID($settings->_rootFolderID);
$repo->setMaxDirID($settings->_maxDirID);
$repo->setEnableConverting($settings->_enableConverting);
$repo->setViewOnlineFileTypes($settings->_viewOnlineFileTypes);

if(isset($GLOBALS['mediarepo_HOOKS']['initREPO'])) {
	foreach($GLOBALS['mediarepo_HOOKS']['initREPO'] as $hookObj) {
		if (method_exists($hookObj, 'postInitREPO')) {
			$hookObj->postInitREPO(array('repo'=>$repo, 'settings'=>$settings));
		}
	}
}

?>
