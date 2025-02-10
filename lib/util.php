<?php

class OC_Batch_Util {
	
	/**
	 * Copy over scripts from lib/scripts to the folder chosen by the user in her settings
	 * @param String $user
	 * @param String $scriptDir
	 */
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
	/**
	 * Read the file $jobScript and return the text content.
	 * @param String $jobScript
	 */
	public static function getJobScript($jobScript, $user){
		$script_folder = \OCP\Config::getUserValue($user, 'batch', 'script_folder');
		$filecontent = \OC\Files\Filesystem::file_get_contents($script_folder.'/'.$jobScript);
		return $filecontent;
	}
	/**
	 * Return a list of filenames ending in ".sh" in the script folder.
	 */
	public static function listJobScripts($user){
		$script_folder = \OCP\Config::getUserValue($user, 'batch', 'script_folder');
		$handle = \OC\Files\Filesystem::opendir($script_folder);
		$scriptFiles = [];
		while (false !== ($entry = \OC\Files\Filesystem::readdir($handle))) {
			if ($entry != "." && $entry != ".." && substr($entry, -3)=='.sh') {
				$scriptFiles[] = $entry;
			}
		}
		return $scriptFiles;
	}
	private static function getContent($uri){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	public static function getJobs($user){
		$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
		if(empty($api_url)){
			return false;
		}
		$jobs = [];
		$text = self::getContent($api_url."/jobs");
		$lines = explode("\n", $text);
		$firstLine = true;
		foreach($lines as $line){
			if($firstLine){
				$keys = explode("\t", $line);
				$firstLine = false;
				continue;
			}
			$vals = explode("\t", $line);
			$job = [];
			$i = 0;
			foreach($keys as $key){
				$job[$key] = $vals[$i];
				++$i;
			}
			$jobs[] = $job;
		}
		return $jobs;
	}
	public static function submitJob($jobScriptText, $user){
		$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
		if(empty($api_url)){
			return false;
		}
		// TODO
	}
	public static function deleteJob($jobID, $user){
		// TODO
	}
}

