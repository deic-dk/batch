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

$tmpl->assign('script_folder', $script_folder);

return $tmpl->fetchPage();
