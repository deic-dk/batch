<?php

OCP\Util::addScript('batch', 'browse');
OCP\Util::addScript('batch', 'personalsettings');
OCP\Util::addStyle('batch', 'style');

require_once('apps/batch/lib/util.php');

OCP\JSON::checkAppEnabled('chooser');
OCP\User::checkLoggedIn();

$tmpl = new OCP\Template('batch', 'personalsettings');

$user = OCP\USER::getUser();
$script_folder = \OCP\Config::getUserValue($user, 'batch', 'script_folder');
$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
if(empty($api_url)){
	$api_url = OC_Config::getValue("forcessl", "https://batch.sciencedata.dk/db");
}

$tmpl->assign('script_folder', $script_folder);
$tmpl->assign('api_url', $api_url);

return $tmpl->fetchPage();
