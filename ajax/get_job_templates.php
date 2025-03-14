<?php

require_once('apps/batch/lib/util.php');

$user = OCP\USER::getUser();
$util = new OC_Batch_Util($user);
$util->getTemplates();

OCP\JSON::success();
