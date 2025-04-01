<?php

require_once('apps/batch/lib/util.php');

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('batch');
if($_REQUEST['action']!='get_file'){
	OCP\JSON::callCheck();
}

$user = OCP\USER::getUser();
$util = new OC_Batch_Util($user);

if($_REQUEST['action']=='submit') {
	if(empty($_POST['job_template']) && empty($_POST['job_template_text'])){
		OCP\JSON::error(array('data' => array('message'=>'No template.')));
		exit;
	}
	$res = true;
	if(empty($_POST['input_files'])){
		$res = $util->submitJob(empty($_POST['job_template'])?null:$_POST['job_template'],
				empty($_POST['job_template_text'])?null:$_POST['job_template_text'], null);
	}
	else{
		$inputFiles = json_decode($_POST['input_files']);
		\OCP\Util::writeLog('batch', 'Received input files '.serialize($inputFiles), \OC_Log::WARN);
		foreach($inputFiles as $input_file){
			$res = $res && $util->submitJob(empty($_POST['job_template'])?null:$_POST['job_template'],
					empty($_POST['job_template_text'])?null:$_POST['job_template_text'],
					$input_file);
		}
	}
	if(!empty($res)){
		OCP\JSON::success();
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem submitting script '.$_POST['job_script'])));
	}
}
elseif($_REQUEST['action']=='delete_jobs') {
	if(empty($_REQUEST['identifiers'])){
		OCP\JSON::error(array('data' => array('message'=>'No job specified')));
		exit;
	}
	$identifiers = json_decode($_REQUEST['identifiers']);
	$ret = true;
	foreach($identifiers as $identifier){
		$ret = $ret + $util->deleteJob($identifier);
	}
	if(!empty($ret) && is_int($ret) && ((int)$ret)<count($identifiers)*300){
		OCP\JSON::success();
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem deleting job(s)'.implode(',', $identifiers))));
	}
}
elseif($_REQUEST['action']=='list_jobs') {
	$jobs = $util->getJobs();
	if(!empty($jobs) || $jobs===[]){
		OCP\JSON::success(array('data' => $jobs));
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem listing jobs')));
	}
}
elseif($_REQUEST['action']=='get_job_info') {
	if(empty($_REQUEST['identifier'])){
		OCP\JSON::error(array('data' => array('message'=>'No job ID specified')));
		exit;
	}
	$info = $util->getJobInfo($_REQUEST['identifier']);
	if(!empty($info)){
		OCP\JSON::success(array('data' => $info));
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem getting job info')));
	}
}
elseif($_REQUEST['action']=='get_job_templates_folder') {
	$batch_folder = \OCP\Config::getUserValue($user, 'batch', 'batch_folder');
	OCP\JSON::success(array('data' => ['job_templates_folder' => $batch_folder.'/job_templates']));
}
elseif($_REQUEST['action']=='get_script'){
	if(empty($_REQUEST['job_script'])){
		OCP\JSON::error(array('data' => array('message'=>'No script file specified')));
		exit;
	}
	$script = $util->getJobScript($_REQUEST['job_script']);
	if(!empty($script)){
		OCP\JSON::success(array('data' => $script));
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem reading script '.$_REQUEST['job_script'])));
	}
}
elseif($_REQUEST['action']=='get_file'){
	if(empty($_REQUEST['url'])){
		OCP\JSON::error(array('data' => array('message'=>'No url specified')));
		exit;
	}
	if(empty($_REQUEST['identifier'])){
		OCP\JSON::error(array('data' => array('message'=>'No job ID specified')));
		exit;
	}
	$filename = $_REQUEST['filename'];
	$url = $_REQUEST['url'];
	if(!empty($_REQUEST['download']) && ($_REQUEST['download']=='true'||$_REQUEST['download']===true)){
		// This is loaded by setting window.location. Sending the headers below triggers download w/o change of URL
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=\"".$filename."\"");
	}
	if($filename=='stdout' || $filename=='stderr'){
		if(!empty($_REQUEST['status']) && substr($_REQUEST['status'], 0, 7)=='running'){
			// Request output
			$util->requestJobOutput($_REQUEST['identifier']);
		}
	}
	// This prints on stdout
	$res = $util->getContent($url, true);
	if(empty($res)){
		OCP\JSON::error(array('data' => array('message'=>'Problem reading file '.$_REQUEST['url'])));
	}
}
elseif($_REQUEST['action']=='save_script'){
	if(empty($_REQUEST['job_script'])){
		OCP\JSON::error(array('data' => array('message'=>'No script file specified')));
		exit;
	}
	$res = $util->saveJobScript($_REQUEST['job_script'], $_REQUEST['job_script_text']);
	if(!empty($res)){
		OCP\JSON::success();
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem saving script '.$_REQUEST['job_script'].' '.$res)));
	}
}
elseif($_REQUEST['action']=='copy_over_job_templates'){
	$util->getTemplates();
	OCP\JSON::success();
}
else{
	OCP\JSON::error(array('message'=>'No action specified'));
}
