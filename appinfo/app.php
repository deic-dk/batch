<?php

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('chooser');

if(\OCP\User::isLoggedIn()){
	
	require_once('apps/chooser/lib/lib_chooser.php');
	
	// Only enable fileactions if there's a valid certificate and internal access is allowed
	$user = OCP\USER::getUser();
	$valid = \OC_Chooser::getSDCertIsValid($user);
	$davEnabled = \OC_Chooser::getInternalDavEnabled($user)=='yes';
	
	if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "/settings/")===false &&
			strpos($_SERVER['REQUEST_URI'], "/apps/user_group_admin/")===false &&
			strpos($_SERVER['REQUEST_URI'], "&view=trashbin")===false){
				OCP\App::addNavigationEntry(
						array( 'id'    => 'batch',
								'order' => 7,
								'icon'  => OCP\Util::imagePath( 'batch', 'stack.svg' ),
								'href'  => OCP\Util::linkTo( 'index.php/apps/batch' , 'index.php' ),
								'name'  => 'Batch'
						)
				);
	}
	OCP\App::registerPersonal('batch', 'personalsettings');
	if($valid && $davEnabled &&
			isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "&view=trashbin")===false &&
			strpos($_SERVER['REQUEST_URI'], "/apps/chooser/")===false &&
			strpos($_SERVER['REQUEST_URI'], "/apps/user_group_admin")===false &&
			strpos($_SERVER['REQUEST_URI'], "/settings/")===false){
				OCP\Util::addScript('batch', 'fileactions');
	}
}