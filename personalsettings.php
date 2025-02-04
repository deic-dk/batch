<?php

OCP\Util::addScript('batch', 'browse');
OCP\Util::addScript('batch', 'personalsettings');
OCP\Util::addStyle('batch', 'style');

require_once('apps/batch/lib/util.php');

OCP\JSON::checkAppEnabled('chooser');
OCP\User::checkLoggedIn();

$tmpl = new OCP\Template('batch', 'personalsettings');

$user = OCP\USER::getUser();
$tmpl->assign('dav_enabled', OC_Chooser::getInternalDavEnabled());
$tmpl->assign('dav_path', OC_Chooser::getInternalDavDir());
$tmpl->assign('storage_enabled', OC_Chooser::getStorageEnabled());
$tmpl->assign('sd_cert_dn', OC_Chooser::getSDCertSubject($user));
$tmpl->assign('sd_cert_expires', OC_Chooser::getSDCertExpires($user));
$tmpl->assign('ssl_active_dns', OC_Chooser::getActiveDNs($user));

return $tmpl->fetchPage();
