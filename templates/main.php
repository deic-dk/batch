<div id="app-content">
	<div id="app-content-batch" class="viewcontainer" apiUrl="<?php echo($_['api_url']);?>">
	<div class="info hidden">
	Notice: Currently, Batch is in beta testing. Use at your own risk - jobs may be deleted, terminated or restarted.
	We appreciate <a href="mailto:<?php echo(\OCP\Config::getSystemValue('fromemail', ''));?>">feedback.</a></div>
		<div id="controls">
			<div class="row">
				<div class="text-right">
					<div class="actions creatable">
						<div id="loading">
							<div id="loading-text">
								<?php $l = OC_L10N::get('batch');
								p($l->t("Working...")); ?>
							</div>
							<div class="icon-loading-dark"></div>
						</div>
						<div id="create" title="">
							<a id="job-create" class="btn btn-primary btn-flat" href="#">
								<?php
								p($l->t("New job "));
								?>
							</a>
				 		</div>
					</div>
				</div>
			</div>
			<div id="newjob" class="apanel">
				<span class="spanpanel" >
					<!-- <select id="job_script" title=<?php p($l->t("Job script")); ?>>
						<?php
						echo "<option value=''></option>";
						foreach ($_['scripts'] as $script) {
							echo "<option value='".$script."'".
									(!empty($_REQUEST['job_script'])&&$_REQUEST['job_script']==$script?" selected='selected'":"").
									">".preg_replace('!\.sh$|\.py$!', '', $script)."</option>";
						}
						?>
					</select> -->
					<span><?php p($l->t("Job script"));?>:</span>
					<input id="job_script" batch_folder="<?php p($_['batch_folder']); ?>" placeholder="Start typing script to load" title="Script to load, edit and submit"></input>
					<span id="links"></span>
					<span id="batch_message"></span>
					<span class="newjob-span">	
						<div id="load" class="btn-job" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Load")); ?></a>
						</div>
						<div id="save" class="btn-job" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Save")); ?></a>
						</div>
						<div id="ok" class="btn-job" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Submit")); ?></a>
						</div>
						<div id="cancel" class="btn-job" original-title="">
							<a class="btn btn-default btn-flat" href="#"><?php p($l->t("Cancel")); ?></a>
						</div>
					</span>
				</span>

				<div id="batch_in_out_files">
					<span><?php p($l->t("Input file"));?>:</span>
					<input type="text" id="batch_input_file" title="Input file on ScienceData" value="" placeholder=""/>
					<select id="group_folder">
						<option value="" selected="selected"><?php p($l->t("Home")); ?></option>
						<?php
						foreach($_['member_groups'] as $group){
							echo "<option value=\"".$group['gid']."\">".$group['gid']."</option>";
						}
						?>
					</select>
					<label class="batch_choose_input_file btn btn-flat button"><?php p($l->t("Browse"));?></label>
					<div class="batch_input_file_dialog" display="none">
						<div class="loadFolderTree"></div>
						<div class="file" style="visibility: hidden; display:inline;"></div>
					</div>
				</div>

				<div id="script_container">
					<textarea type="text" id="job_script_text"></textarea>
				</div>

				<div id="ssh">
					<textarea id="public_key" type="text" placeholder="<?php p($l->t("Public SSH key")); ?>"
					title="<?php p($l->t("Paste your public SSH key here")); ?>"></textarea>
						<div class="key_buttons">
							<a id="save_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Save stored SSH key to browser storage")); ?>"><?php p($l->t("Save")); ?></a>
							<br />
							<a id="load_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Load stored SSH key from browser storage")); ?>"><?php p($l->t("Load")); ?></a>
							<br />
							<a id="clear_ssh_public_key" class="btn btn-default btn-flat btn-sg" href="#" title="<?php p($l->t("Clear stored SSH key from browser storage")); ?>"><?php p($l->t("Clear")); ?></a>
					</div>
				</div>
				<div id="storage">
				</div>
				<div id="cvmfs">
				</div>
				<div id="setup">
				</div>
				<div id="file"><span id="file_text"><?php p($l->t("File")); ?>:</span>
					<input id="file_input" type="text" placeholder="<?php p($l->t("Optional input file")); ?>"
					title="<?php p($l->t("Path of file in your ScienceData Home")); ?>"
					value="<?php echo(empty($_REQUEST['file'])?$_REQUEST['file']:''); ?>" />
				</div>
				<div id="peers"><span id="peers_text"><?php p($l->t("Peers")); ?>:</span>
					<input id="peers_input" type="text" placeholder="<?php p($l->t("Optional peers to pass to your job")); ?>"
					title="<?php p($l->t("List of the form hostname1:ip1,hostname2:ip2,...")); ?>"
					value="<?php echo(empty($_REQUEST['peers'])?$_REQUEST['peers']:''); ?>" />
				</div>
			</div>
		</div> 
	</div>
	<h2 class="running_jobs"><?php p($l->t("Jobs")); ?>
	<a id="jobs_refresh" class="btn btn-default" title="<?php p($l->t("Refresh")); ?>">&circlearrowright;</a>
	<div class="deleteJobs"><a id="delete_jobs" class="btn btn-default icon-trash-empty" title="<?php p($l->t("Delete selected jobs")); ?>" disabled="true"></a></div>
	</h2>
	<div id="running_jobs">
	<table id="jobstable" class="panel">
		<thead class="panel-heading" >
			<tr>
				<th id="headerSelectJobs">
					<input id="selectAllJobs" type="checkbox" class="fileselect">
				</th>
				<th id="headerJobID">
					<div class="display sort columntitle" data-sort="public">
						<span>ID</span>
					</div>
				</th>
				<th id="headerJobName">
					<div class="display sort columntitle" data-sort="public">
						<span>Name</span>
					</div>
				</th>
				<th id="headerJobStatus">
					<div class="display sort columntitle" data-sort="public">
						<span>Status</span>
					</div>
				</th>
				<th id="headerJobMore" class="th-button">
					<div class="display sort columntitle" data-sort="public">
						<span>more</span>
					</div>
				</th>
				<th id="headerJobDelete" class="th-button">
					<div class="display sort columntitle" data-sort="public">
						<span>delete</span>
					</div>
				</th>
			</tr>
		</thead>
		<tbody id='fileList'>
		</tbody>
		<tfoot
			<tr class="summary text-sm">
				<td>
					<span class="info" containers="0">
					</span>
				</td>
			</tr>
		</tfoot>
	</table>
	</div>
</div>
<div id='dialogalert' title='<?php p($l->t("Delete confirmation")); ?>'>
</div>
<div id='textLoad'>
</div>

