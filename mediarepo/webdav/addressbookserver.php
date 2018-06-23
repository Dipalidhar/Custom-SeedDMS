<?php
include("../inc/inc.Settings.php");
if(!empty($settings->_coreDir))
	require_once($settings->_coreDir.'/Core.php');
else
	require_once('MediaREPO/Core.php');

/*

Addressbook/CardDAV server example

This server features CardDAV support

*/

// settings
date_default_timezone_set('Europe/Berlin');

// Make sure this setting is turned on and reflect the root url for your WebDAV server.
// This can be for example the root / or a complete path to your server script
$baseUri = $settings->_httpRoot.'webdav/addressbookserver.php/';//$settings->_httpRoot.'addressbook/';

$db = new mediarepo_Core_DatabaseAccess($settings->_dbDriver, $settings->_dbHostname, $settings->_dbUser, $settings->_dbPass, $settings->_dbDatabase);
$db->connect() or die ("Could not connect to db-server \"" . $settings->_dbHostname . "\"");
$db->getResult("set names 'utf8'");

$repo = new mediarepo_Core_REPO($db, $settings->_contentDir.$settings->_contentOffsetDir);
$user = null;

function checkuser($username, $pass) { /* {{{ */
	global $repo, $settings, $user, $server, $baseUri;
	$userobj = $repo->getUserByLogin($username);
	if(!$userobj)
		return false;
	if(md5($pass) != $userobj->getPwd())
		return false;

	$user = $userobj;
	return true;
} /* }}} */

//Mapping PHP errors to exceptions
function exception_error_handler($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
//set_error_handler("exception_error_handler");

// Autoloader
require_once 'vendor/autoload.php';
require_once 'PrincipalBackend/MediaREPO.php';
require_once 'CardDAVBackend/MediaREPO.php';
require_once 'CalDAVBackend/MediaREPO.php';

// Backends
$authBackend = new \Sabre\DAV\Auth\Backend\BasicCallBack('checkuser');
$principalBackend = new MediaREPO\PrincipalBackend\MediaREPO($repo);
$carddavBackend = new MediaREPO\CardDAV\Backend\MediaREPO($repo);
$caldavBackend    = new MediaREPO\CalDAV\Backend\MediaREPO($repo);

// Setting up the directory tree //
$nodes = [
    new Sabre\DAVACL\PrincipalCollection($principalBackend),
    new Sabre\CalDAV\CalendarRoot($principalBackend, $caldavBackend),
    new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
];

// The object tree needs in turn to be passed to the server class
$server = new Sabre\DAV\Server($nodes);
$server->setBaseUri($baseUri);

// Plugins
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend));
$server->addPlugin(new Sabre\DAV\Browser\Plugin());
$server->addPlugin(new Sabre\CalDAV\Plugin());
$server->addPlugin(new Sabre\CardDAV\Plugin());
$server->addPlugin(new Sabre\DAVACL\Plugin());
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

// And off we go!
$server->exec();
