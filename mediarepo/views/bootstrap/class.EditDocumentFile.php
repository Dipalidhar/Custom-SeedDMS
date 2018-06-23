<?php
/**
 * Implementation of EditDocumentFile view
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
 * Class which outputs the html page for EditDocumentFile view
 *
 * @category   REPO
 * @package    MediaREPO
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class mediarepo_View_EditDocumentFile extends mediarepo_Bootstrap_Style {

	function show() { /* {{{ */
		$repo = $this->params['repo'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$document = $this->params['document'];
		$file = $this->params['file'];

		$this->htmlStartPage(getMLText("document_title", array("documentname" => htmlspecialchars($document->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageNavigation($this->getFolderPathHTML($folder, true, $document), "view_document", $document);
		$this->contentHeading(getMLText("edit"));
		$this->contentContainerStart();

?>
<form action="../op/op.EditDocumentFile.php" class="form-horizontal" name="form1" method="post">
  <?php echo createHiddenFieldWithKey('editdocumentfile'); ?>
	<input type="hidden" name="documentid" value="<?php echo $document->getID()?>">
	<input type="hidden" name="fileid" value="<?php echo $file->getID()?>">
	<div class="control-group">
		<label class="control-label"><?php printMLText("version");?>:</label>
		<div class="controls"><select name="version" id="version">
			<option value=""><?= getMLText('document') ?></option>
<?php
		$versions = $document->getContent();
		foreach($versions as $version)
			echo "<option value=\"".$version->getVersion()."\"".($version->getVersion() == $file->getVersion() ? " selected" : "").">".getMLText('version')." ".$version->getVersion()."</option>";
?>
		</select></div>
	</div>
	<div class="control-group">
			<label class="control-label"><?php printMLText("name");?>:</label>
			<div class="controls">
				<input name="name" type="text" value="<?php print htmlspecialchars($file->getName());?>" />
			</div>
	</div>
	<div class="control-group">
			<label class="control-label"><?php printMLText("comment");?>:</label>
			<div class="controls">
				<textarea name="comment" rows="4" cols="80"><?php print htmlspecialchars($file->getComment());?></textarea>
			</div>
	</div>
	<div class="control-group">
			<label class="control-label"><?php printMLText("document_link_public");?>:</label>
			<div class="controls">
				<input type="checkbox" name="public" value="true"<?php echo ($file->isPublic() ? " checked" : "");?> />
			</div>
	</div>
	<div class="controls">
		<button type="submit" class="btn"><i class="icon-save"></i> <?php printMLText("save") ?></button>
	</div>
</form>
<?php
		$this->contentContainerEnd();
		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
