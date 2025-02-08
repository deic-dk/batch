<?php

class OC_Batch_Util {
	
	public static function getScriptFiles($user, $scriptDir){
		$scriptDirFullPath = \OC\Files\Filesystem::getLocalFile($scriptDir);
		$defaultScriptsFolder = dirname(__FILE__).'/scripts';
		\OCP\Util::writeLog('batch', 'Copying'.$defaultScriptsFolder.'-->'.$scriptDirFullPath, \OC_Log::WARN);
		$files = array_diff(scandir($defaultScriptsFolder), array('.', '..'));
		$success = true;
		$newfiles = [];
		foreach($files as $file){
			if(substr($file, -3)=='.sh'){
				$srcFileFullPath = $defaultScriptsFolder.'/'.$file;
				$newfileFullPath = $scriptDirFullPath.'/'.$file;
				$ok = copy($srcFileFullPath, $newfileFullPath);
				if(!$ok){
					$success = false;
					break;
				}
				$newfiles[] = $file;
			}
		}
		if($success){
			$view = \OC\Files\Filesystem::getView();
			$absPath = $view->getAbsolutePath($scriptDir);
			list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath('/' . $absPath);
			\OCP\Util::writeLog('batch', 'Internal path: '.$internalPath, \OC_Log::WARN);
			if($storage){
				$scanner = $storage->getScanner($internalPath);
				array_map(function($file) use ($scanner){
					return $scanner->scanFile($file);
				}, $newfiles);
			}
		}
	}
	public static function getJobScript($jobScript){
		// TODO
	}
	public static function getJobScripts(){
		// TODO
	}
	public static function submitJob($jobScriptText){
		// TODO
	}
	public static function deleteJob($jobID){
		// TODO
	}
	public static function getJobs(){
		// TODO
	}
}

