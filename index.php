<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('batch');
// We use marked.js from files_markdown for parsing pod info
OCP\App::checkAppEnabled('files_markdown');

OCP\App::setActiveNavigationEntry('batch');

require_once('apps/batch/lib/util.php');

OCP\Util::addStyle('batch', 'style');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('batch','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addScript('files_markdown','marked');
OCP\Util::addScript('batch', 'browse_input_file');

$tmpl = new OCP\Template('batch', 'main', 'user');
$user = OCP\USER::getUser();
if(OCP\App::isEnabled('user_group_admin')){
	$groups = OC_User_Group_Admin_Util::getUserGroups($user, false, false, true);
	$tmpl->assign('member_groups', $groups);
}
$batch_folder = \OCP\Config::getUserValue($user, 'batch', 'batch_folder');
$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
if(empty($api_url)){
	$api_url = OC_Config::getValue("batch_api_url", "https://batch.sciencedata.dk/");
}

$tmpl->assign('batch_folder', $batch_folder);
$tmpl->assign('api_url', $api_url);
$tmpl->printPage();

