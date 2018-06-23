<?php
//    MyREPO. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
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

require_once('inc.ClassSettings.php');
if(defined("mediarepo_CONFIG_FILE"))
	$settings = new Settings(mediarepo_CONFIG_FILE);
else
	$settings = new Settings();
if(!defined("mediarepo_INSTALL") && file_exists(dirname($settings->_configFilePath)."/ENABLE_INSTALL_TOOL")) {
	die("MediaREPO won't run unless your remove the file ENABLE_INSTALL_TOOL from your configuration directory.");
}

if(isset($settings->_extraPath))
	ini_set('include_path', $settings->_extraPath. PATH_SEPARATOR .ini_get('include_path'));

if(isset($settings->_maxExecutionTime))
	ini_set('max_execution_time', $settings->_maxExecutionTime);

if (get_magic_quotes_gpc()) {
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process)) {
		foreach ($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

$indexconf = null;
if($settings->_enableFullSearch) {
	if($settings->_fullSearchEngine == 'sqlitefts') {
		$indexconf = array(
			'Indexer' => 'mediarepo_SQLiteFTS_Indexer',
			'Search' => 'mediarepo_SQLiteFTS_Search',
			'IndexedDocument' => 'mediarepo_SQLiteFTS_IndexedDocument'
		);

		require_once('MediaREPO/SQLiteFTS.php');
	} else {
		$indexconf = array(
			'Indexer' => 'mediarepo_Lucene_Indexer',
			'Search' => 'mediarepo_Lucene_Search',
			'IndexedDocument' => 'mediarepo_Lucene_IndexedDocument'
		);

		if(!empty($settings->_luceneClassDir))
			require_once($settings->_luceneClassDir.'/Lucene.php');
		else
			require_once('MediaREPO/Lucene.php');
	}
}

/* Add root Dir. Needed because the view classes are included
 * relative to it.
 */
ini_set('include_path', $settings->_rootDir. PATH_SEPARATOR .ini_get('include_path'));

?>
