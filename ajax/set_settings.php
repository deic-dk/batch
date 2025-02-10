<?php


$script_folder = $_POST['script_folder'];
$api_url = $_POST['api_url'];
$user = OCP\USER::getUser();

\OCP\Config::setUserValue($user, 'batch', 'script_folder', $script_folder);
\OCP\Config::setUserValue($user, 'batch', 'api_url', $api_url);

OCP\JSON::success();
