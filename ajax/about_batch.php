<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('batch');
OCP\JSON::callCheck();

$tmpl = new OCP\Template("batch", "about");
$page = $tmpl->fetchPage();
OCP\JSON::success(array('data' => array('page'=>$page)));
