<?php
OCP\Util::addStyle('batch', 'personalsettings');
$l = OC_L10N::get('batch');
?>

<fieldset class="section" id="batchSettings">
	<a id="batch-info">What's this?</a>
	<h2><?php p($l->t('Batch data processing')); ?><img class="batchSettingsIcon" src="/apps/batch/img/stack.svg" /></h2>

	<br />

	<?php p($l->t("Work folder"));?>:
	<input type="text" id="batch_folder" value="<?php p(isset($_['batch_folder'])?$_['batch_folder']:''); ?>" placeholder=""/>
	<label class="batch_choose_batch_folder btn btn-flat button"><?php p($l->t("Browse"));?></label>
	<div class="batch_folder_dialog" display="none">
		<div class="loadFolderTree"></div>
		<div class="file" style="visibility: hidden; display:inline;"></div>
	</div>
	<label id="batch_msg"></label>
	<br />
	<br />
	<?php p($l->t("API URL"));?>:
	<input type="text" id="api_url" value="<?php p(isset($_['api_url'])?$_['api_url']:''); ?>" placeholder=""/>
	<label id="batch_settings_submit" class="button"><?php p($l->t('Save'));?></label>
	<br />
	<br />
	<?php p($l->t("Get job templates"));?>:
	<label id="get_job_templates" class="button"><?php p($l->t('Get'));?></label>
	<label id="batch_scripts_msg"></label>
</fieldset>
