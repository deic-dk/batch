<?php

class OC_Batch_Util {
	OCP\JSON::checkAppEnabled('files_sharding');
	OCP\JSON::checkAppEnabled('chooser');
	
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
	private static function getContent($uri, $user){
		$certFile = \OC_Chooser::getSDCertLocation($user);
		$keyFile = \OC_Chooser::decryptSDKey($user);
		if(empty($certFile) || empty($keyFile)){
			throw new \Exception("Missing certificate or key file.");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	private static function mkCol($url, $user){
		$certFile = \OC_Chooser::getSDCertLocation($user);
		$keyFile = \OC_Chooser::decryptSDKey($user);
		if(empty($certFile) || empty($keyFile)){
			throw new \Exception("Missing certificate or key file.");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		unlink($keyFile); // Clean up temporary unencrypted key file
		return $data;
	}
	private static function delete($url, $user){
		$certFile = \OC_Chooser::getSDCertLocation($user);
		$keyFile = \OC_Chooser::decryptSDKey($user);
		if(empty($certFile) || empty($keyFile)){
			throw new \Exception("Missing certificate or key file.");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		unlink($keyFile); // Clean up temporary unencrypted key file
		return $data;
	}
	private static function uploadFile($file, $url, $user){
		$file = fopen($file, 'r');
		$size = filesize($file);
		$data = fread($file, $size);
		return self::upload($data, $url, $user);
	}
	private static function upload($data, $url, $user){
		$filename = basename(parse_url($url, PHP_URL_PATH));
		$certFile = \OC_Chooser::getSDCertLocation($user);
		$keyFile = \OC_Chooser::decryptSDKey($user);
		if(empty($certFile) || empty($keyFile)){
			throw new \Exception("Missing certificate or key file.");
		}
		
		$requestBody = '';
		$separator = '-----'.md5(microtime()).'-----';
		$requestBody .= "--$separator\r\n" . "Content-Disposition: form-data; name=\"$filename\"; filename=\"$filename\"\r\n" . "Content-Length: ".strlen($data)."\r\n" . "Content-Transfer-Encoding: binary\r\n" . "\r\n" . "$data\r\n";
		// Terminate the body
		$requestBody .= "--$separator--";
		$ch = curl_init($url);
		
		// This is necessary as cURL will ignore the CURLOUT_POSTFIELDS if we use the built-in PUT method
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: multipart/form-data; boundary="'.$separator.'"',
		));
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		unlink($keyFile); // Clean up temporary unencrypted key file
		return $response;
	}
	public static function getJobs($user){
		$api_url = \OCP\Config::getUserValue($user, 'batch', 'api_url', '');
		if(empty($api_url)){
			return false;
		}
		$jobs = [];
		$text = self::getContent($api_url."/jobs", $user);
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
		$job_id = $api_url."/jobs/".uniqid();
		self::mkCol($job_id, $user);
		self::upload($jobScriptText, $job_id."/job", $user);
		return $job_id;
	}
	public static function deleteJob($jobDbUrl, $user){
		return self::delete($jobDbUrl, $user);
	}
}

