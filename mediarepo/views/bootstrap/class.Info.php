<?php
/**
 * Implementation of Info view
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for Info view
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_View_Info extends mediarepo_Bootstrap_Style {

	function show() { /* {{{ */
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$version = $this->params['version'];
		$availversions = $this->params['availversions'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
		if($availversions) {
			$newversion = '';
			foreach($availversions as $availversion) {
				if($availversion[0] == 'stable')
					$newversion = $availversion[1];
			}
			if($newversion > $version->_number) {
				$this->warningMsg(getMLText('no_current_version', array('latestversion'=>$newversion)));
			}
		} else {
			$this->warningMsg(getMLText('no_version_check'));
		}
		$this->contentContainerStart();
		echo $version->banner();
		$this->contentContainerEnd();
//		$this->contentContainerStart();
//		phpinfo();
//		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
