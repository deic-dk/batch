<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('batch');
// We use marked.js from files_markdown for parsing pod info
OCP\App::checkAppEnabled('files_markdown');

OCP\App::setActiveNavigationEntry('batch');

require_once('apps/batch/lib/util.php');

OCP\Util::addStyle('batch', 'style');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('batch','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addScript('files_markdown','marked');

$tmpl = new OCP\Template('batch', 'main', 'user');
$util = new OC_Batch_Util();
$tmpl->assign('scripts', $util->getJobScripts());
$tmpl->printPage();

