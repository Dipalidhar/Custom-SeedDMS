<?php
/**
 * Implementation of TransferDocument view
 *
 * @category   REPO
 * @package    MediaREPO
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for TransferDocument view
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2017 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_View_TransferDocument extends mediarepo_Bootstrap_Style {

	function show() { /* {{{ */
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$allusers = $this->params['allusers'];
		$document = $this->params['document'];
		$folder = $this->params['folder'];

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("transfer_document"));
		$this->contentContainerStart();
?>
<form class="form-horizontal" action="../op/op.TransferDocument.php" name="form1" method="post">
<input type="hidden" name="documentid" value="<?php print $document->getID();?>">
<?php echo createHiddenFieldWithKey('transferdocument'); ?>

<div class="control-group">
	<label class="control-label" for="assignTo">
<?php printMLText("transfer_to_user"); ?>:
	</label>
	<div class="controls">
<select name="userid" class="chzn-select">
<?php
		$owner = $document->getOwner();
		foreach ($allusers as $currUser) {
			if ($currUser->isGuest() || ($currUser->getID() == $owner->getID()))
				continue;

			print "<option value=\"".$currUser->getID()."\"";
			if($folder->getAccessMode($currUser) < M_READ)
				print " disabled data-warning=\"".getMLText('transfer_no_read_access')."\"";
			elseif($folder->getAccessMode($currUser) < M_READWRITE)
				print " data-warning=\"".getMLText('transfer_no_write_access')."\"";
			print ">" . htmlspecialchars($currUser->getLogin()." - ".$currUser->getFullName());
		}
?>
</select>
	</div>
</div>

<div class="control-group">
	<div class="controls">
		<button type="submit" class="btn"><i class="icon-exchange"></i> <?php printMLText("transfer_document");?></button>
	</div>
</div>

</form>
<?php
		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
