<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('batch');
OCP\JSON::callCheck();

$util = new OC_Batch_Util();
$user = OCP\USER::getUser();

if($_REQUEST['action']=='submit_job') {
	if(empty($_POST['job_script'])){
		OCP\JSON::error(array('data' => array('message'=>'No script file specified')));
		exit;
	}
	$res = $util->submitJob($_POST['job_script'], $user);
	if(!empty($res)){
		OCP\JSON::success();
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem submitting script '.$_POST['job_script'])));
	}
}
elseif($_REQUEST['action']=='delete_job') {
	if(empty($_REQUEST['job_db_url'])){
		OCP\JSON::error(array('data' => array('message'=>'No job specified')));
		exit;
	}
	$res = $util->deleteJob($_REQUEST['job_db_url'], $user);
	if(!empty($res)){
		OCP\JSON::success();
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem deleting job '.$_REQUEST['job_db_url'])));
	}
}
elseif($_REQUEST['action']=='list_jobs') {
	$jobs = $util->getJobs($user);
	if(!empty($jobs)){
		OCP\JSON::success('data' => $jobs);
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem listing jobs')));
	}
}
elseif($_REQUEST['action']=='get_script'){
	if(empty($_REQUEST['job_script'])){
		OCP\JSON::error(array('data' => array('message'=>'No script file specified')));
		exit;
	}
	$script = $util->getJobScript($_REQUEST['job_script'], $user);
	if(!empty($script)){
		OCP\JSON::success(array('data' => $script));
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem reading script '.$_REQUEST['job_script'])));
	}
}
else{
	OCP\JSON::error(array('message'=>'No action specified'));
}
