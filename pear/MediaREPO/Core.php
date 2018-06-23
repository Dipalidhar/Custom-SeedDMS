<?php
/**
 * Implementation of a document in the document management system
 *
 * @category   REPO
 * @package    mediarepo_Core
 * @license    GPL2
 * @author     Markus Westphal, Malcolm Cowe, Matteo Lucarelli,
 *             Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal, 2006-2008 Malcolm Cowe,
 *             2010 Matteo Lucarelli, 2010-2012 Uwe Steinmann
 * @version    Release: 5.1.7
 */

/**
 * @uses mediarepo_DatabaseAccess
 */
define('USE_PDO', 1);
if(defined('USE_PDO'))
	require_once('Core/inc.DBAccessPDO.php');
else
	require_once('Core/inc.DBAccess.php');

/**
 * @uses mediarepo_REPO
 */
require_once('Core/inc.ClassREPO.php');

/**
 * @uses mediarepo_Object
 */
require_once('Core/inc.ClassObject.php');

/**
 * @uses mediarepo_Folder
 */
require_once('Core/inc.ClassFolder.php');

/**
 * @uses mediarepo_Document
 */
require_once('Core/inc.ClassDocument.php');

/**
 * @uses mediarepo_Attribute
 */
require_once('Core/inc.ClassAttribute.php');

/**
 * @uses mediarepo_Group
 */
require_once('Core/inc.ClassGroup.php');

/**
 * @uses mediarepo_User
 */
require_once('Core/inc.ClassUser.php');

/**
 * @uses mediarepo_KeywordCategory
 */
require_once('Core/inc.ClassKeywords.php');

/**
 * @uses mediarepo_DocumentCategory
 */
require_once('Core/inc.ClassDocumentCategory.php');

/**
 * @uses mediarepo_Notification
 */
require_once('Core/inc.ClassNotification.php');

/**
 * @uses mediarepo_UserAccess
 * @uses mediarepo_GroupAccess
 */
require_once('Core/inc.ClassAccess.php');

/**
 * @uses mediarepo_Workflow
 */
require_once('Core/inc.ClassWorkflow.php');

/**
 */
require_once('Core/inc.AccessUtils.php');

/**
 * @uses mediarepo_File
 */
require_once('Core/inc.FileUtils.php');
