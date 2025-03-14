<?php

require_once('apps/batch/lib/util.php');

$batch_folder = $_POST['batch_folder'];
$api_url = $_POST['api_url'];
$user = OCP\USER::getUser();
$util = new OC_Batch_Util($user);

\OCP\Config::setUserValue($user, 'batch', 'batch_folder', $batch_folder);
\OCP\Config::setUserValue($user, 'batch', 'api_url', $api_url);
$util->createOutputFolder();

OCP\JSON::success();
