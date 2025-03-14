<?php

require_once __DIR__ . '/../../../lib/base.php';
require_once('apps/chooser/appinfo/apache_note_user.php');

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('batch');
//\OCP\JSON::callCheck();

\OC_Util::setupFS();

$group = empty($_GET['group'])?'':$_GET['group'];
$user = \OCP\User::getUser();
if(!empty($group)){
	\OC\Files\Filesystem::tearDown();
	$groupDir = '/'.$user.'/user_group_admin/'.$group;
	\OC\Files\Filesystem::init($user, $groupDir);
}

/*
 parameters:
 - layers
 - StartDir
 - file

 shall return all subdirs within (layer) layers
*/

$l = \OC_L10N::get('batch');
$showLayers = (!empty($_GET['layers']))?$_GET['layers']:2;
$scriptFiles = array();
if(!empty($_GET['StartDir'])){
	$actualDir = $_GET['StartDir'];
	if(!strlen($actualDir)<=1 && substr($actualDir,0,1)!=='/'){
		$actualDir = '/'.$actualDir;
	}
}
else{
	$actualDir = '/';
}

$mainDir = (!empty($_GET['dir']))?$_GET['dir'].'/':'/';

if(!\OC\Files\Filesystem::is_dir($actualDir)){
	\OCP\JSON::error(array('data'=>array('message'=>$actualDir.' '.$l->t('is not a directory'))));
	exit;
}

function getScriptsList($dir, $depth=-1){
	if($depth == 0) return array();
	$ret = array();
	\OCP\Util::writeLog('batch', 'Listing dir '.$dir, \OCP\Util::WARN);
	foreach(\OC\Files\Filesystem::getDirectoryContent( $dir ) as $i ){
		$path = $dir.'/'.$i['name'];
		if($i['type']=='dir'){
			$dir = rtrim($dir, '/');
			$ret = array_merge($ret, getScriptsList($path, $depth-1));
		}
		else{
			if(!preg_match('|.*\.sh|', $path) && !preg_match('|.*\.py|', $path)){
				\OCP\Util::writeLog('batch', 'Not a script, '.$path, \OCP\Util::WARN);
				continue;
			}
			if(!empty($i['permissions']) && $i['permissions']&\OCP\PERMISSION_UPDATE!=0 ){
				$ret[] = $path;
			}
		}
	}
	return $ret;
}

$tmp = getScriptsList($actualDir, $showLayers, $mainDir);
$scriptFiles = array_merge($scriptFiles,$tmp);

\OCP\JSON::encodedPrint($scriptFiles);
