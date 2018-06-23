<?php
/**
 * Implementation of ApproveDocument view
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
 * Class which outputs the html page for ApproveDocument view
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_View_ApproveDocument extends mediarepo_Bootstrap_Style {

	function js() { /* {{{ */
		header('Content-Type: application/javascript; charset=UTF-8');
?>
function checkIndForm()
{
	msg = new Array();
	if (document.formind.approvalStatus.value == "") msg.push("<?php printMLText("js_no_approval_status");?>");
	if (document.formind.comment.value == "") msg.push("<?php printMLText("js_no_comment");?>");
	if (msg != "") {
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}
function checkGrpForm()
{
	msg = new Array();
//	if (document.formgrp.approvalGroup.value == "") msg.push("<?php printMLText("js_no_approval_group");?>");
	if (document.formgrp.approvalStatus.value == "") msg.push("<?php printMLText("js_no_approval_status");?>");
	if (document.formgrp.comment.value == "") msg.push("<?php printMLText("js_no_comment");?>");
	if (msg != "")
	{
  	noty({
  		text: msg.join('<br />'),
  		type: 'error',
      dismissQueue: true,
  		layout: 'topRight',
  		theme: 'defaultTheme',
			_timeout: 1500,
  	});
		return false;
	}
	else
		return true;
}
$(document).ready(function() {
	$('body').on('submit', '#formind', function(ev){
		if(checkIndForm()) return;
		ev.preventDefault();
	});
	$('body').on('submit', '#formgrp', function(ev){
		if(checkGrpForm()) return;
		ev.preventDefault();
	});
});
<?php
	} /* }}} */

	function show() { /* {{{ */
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];

		$latestContent = $document->getLatestContent();
		$approvals = $latestContent->getApprovalStatus();

		foreach($approvals as $approval) {
			if($approval['approveID'] == $_GET['approveid']) {
				$approvalStatus = $approval;
				break;
			}
		}

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("add_approval"));

		$this->contentContainerStart();

		// Display the Approval form.
		$approvaltype = ($approvalStatus['type'] == 0) ? 'ind' : 'grp';
		if($approvalStatus["status"]!=0) {

			print "<table class=\"folderView\"><thead><tr>";
			print "<th>".getMLText("status")."</th>";
			print "<th>".getMLText("comment")."</th>";
			print "<th>".getMLText("last_update")."</th>";
			print "</tr></thead><tbody><tr>";
			print "<td>";
			printApprovalStatusText($approvalStatus["status"]);
			print "</td>";
			print "<td>".htmlspecialchars($approvalStatus["comment"])."</td>";
			$indUser = $repo->getUser($approvalStatus["userID"]);
			print "<td>".$approvalStatus["date"]." - ". htmlspecialchars($indUser->getFullname()) ."</td>";
			print "</tr></tbody></table><br>\n";
		}
?>
	<form method="POST" action="../op/op.ApproveDocument.php" id="form<?= $approvaltype ?>" name="form<?= $approvaltype ?>" enctype="multipart/form-data">
	<?php echo createHiddenFieldWithKey('approvedocument'); ?>
	<table>
		<tr>
			<td><?php printMLText("comment")?>:</td>
			<td><textarea name="comment" cols="80" rows="4"></textarea></td>
		</tr>
		<tr>
			<td><?php printMLText("approval_file")?>:</td>
			<td>
<?php
	$this->printFileChooser('approvalfile', false);
?>
			</td>
		</tr>
	<tr><td><?php printMLText("approval_status")?>:</td>
	<td>
	<select name="approvalStatus">
<?php if($approvalStatus['status'] != 1) { ?>
	<option value='1'><?php printMLText("status_approved")?></option>
<?php } ?>
<?php if($approvalStatus['status'] != -1) { ?>
	<option value='-1'><?php printMLText("rejected")?></option>
<?php } ?>
	</select>
	</td></tr>
	<tr><td></td><td>
	<input type='submit' class="btn" name='<?= $approvaltype ?>Approval' value='<?php printMLText("submit_approval")?>'/></td></tr>
	</table>
	<input type='hidden' name='approvalType' value='<?= $approvaltype ?>'/>
	<?php if($approvaltype == 'grp'): ?>
	<input type='hidden' name='approvalGroup' value="<?php echo $approvalStatus['required']; ?>" />
	<?php endif; ?>
	<input type='hidden' name='documentid' value='<?php echo $document->getId() ?>'/>
	<input type='hidden' name='version' value='<?php echo $latestContent->getVersion(); ?>'/>
	</form>
<?php

		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
