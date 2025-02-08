<?php

require_once('apps/batch/lib/util.php');

$user = OCP\USER::getUser();
$script_folder = \OCP\Config::getUserValue($user, 'batch', 'script_folder');
OC_Batch_Util::getScriptFiles($user, $script_folder);

OCP\JSON::success();
