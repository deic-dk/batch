<?php


$script_folder = $_POST['script_folder'];
$user = OCP\USER::getUser();

\OCP\Config::setUserValue($user, 'batch', 'script_folder', $script_folder);

OCP\JSON::success();
