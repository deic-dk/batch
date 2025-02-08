<?php


$user = OCP\USER::getUser();
$script_folder = \OCP\Config::getUserValue($user, 'batch', 'script_folder');

OCP\JSON::success(array(
		'script_folder' => $script_folder
));
