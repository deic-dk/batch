<?php

OCP\Util::addScript('batch', 'browse');
OCP\Util::addScript('batch', 'personalsettings');
OCP\Util::addStyle('batch', 'style');

OCP\JSON::checkAppEnabled('chooser');
OCP\User::checkLoggedIn();

$tmpl = new OCP\Template('batch', 'personalsettings');

$user = OCP\USER::getUser();
$batch_folder = \OCP\Config::getUserValue($user, 'batch', 'batch_folder');
$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
if(empty($api_url)){
	$api_url = OC_Config::getValue("batch_api_url", "https://batch.sciencedata.dk/");
}

$tmpl->assign('batch_folder', $batch_folder);
$tmpl->assign('api_url', $api_url);

return $tmpl->fetchPage();
