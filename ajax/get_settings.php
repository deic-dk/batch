<?php


$user = OCP\USER::getUser();
$batch_folder = \OCP\Config::getUserValue($user, 'batch', 'batch_folder');
$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url');

OCP\JSON::success(array(
		'batch_folder' => $batch_folder,
		'api_url' => $api_url
));
