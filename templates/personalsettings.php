<?php
OCP\Util::addStyle('batch', 'personalsettings');
$l = OC_L10N::get('batch');
?>

<fieldset class="section" id="batchSettings">
	<a id="batch-info">What's this?</a>
	<h2><?php p($l->t('Batch data processing')); ?><img class="batchSettingsIcon" src="/apps/batch/img/stack.svg" /></h2>

	<br />

	<?php p($l->t("Script folder"));?>:

	<input type="text" id="batch_script_folder"
	value="<?php p(isset($_['script_folder'])?$_['script_folder']:''); ?>" placeholder=""/>
	<label class="batch_choose_script_folder btn btn-flat button"><?php p($l->t("Browse"));?></label>
	<div id="script_folder" style="visibility:hidden;display:none;"></div>
	<div class="script_folder_dialog" display="none">
		<div class="loadFolderTree"></div>
		<div class="file" style="visibility: hidden; display:inline;"></div>
	</div>
	<label id="batch_settings_submit" class="button"><?php p($l->t('Save'));?></label>
	<label id="batch_msg"></label>
	<br />
	<br />
	<?php p($l->t("Get default scripts"));?>:
	<label id="get_default_batch_scripts" class="button"><?php p($l->t('Get'));?>
</fieldset>
