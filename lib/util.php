<?php

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('chooser');

class OC_Batch_Util {
	
	private $api_url;
	private $local_api_url;
	private $user;
	private $batchFolder;
	private $certFile;
	private $keyFile;
	private $dn;
	
	function __construct($user){
		$this->user = $user;
		$this->api_url = \OCP\Config::getUserValue($this->user, 'batch', 'api_url', '');
		$this->local_api_url = preg_replace('|^(https://[^\./]+)\.[^/]+/|', '\1/', $this->api_url);
		$this->batchFolder = \OCP\Config::getUserValue($this->user, 'batch', 'batch_folder');
		$this->certFile = \OC_Chooser::getSDCertLocation($this->user);
		$this->keyFile = \OC_Chooser::decryptSDKey($this->user);
		if(empty($this->certFile) || empty($this->keyFile)){
			throw new \Exception("Missing certificate or key file.");
		}
		$this->dn = \OC_Chooser::getSDCertSubject($this->user);
	}
	
	private static function recursive_copy($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		$new_files = [];
		while(( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					self::recursive_copy($src .'/'. $file, $dst .'/'. $file);
				}
				else {
					$new_file = $dst .'/'. $file;
					copy($src .'/'. $file, $new_file);
					$new_files [] = $new_file;
				}
			}
		}
		closedir($dir);
		return $new_files;
	}
	
	/**
	 * Copy over scripts from lib/scripts to the folder chosen by the user in her settings
	 * @param String $batchFolder
	 */
	public function getTemplates(){
		$myTemplatesFolderFullPath = \OC\Files\Filesystem::getLocalFile($this->batchFolder.'/job_templates');
		if(!file_exists($myTemplatesFolderFullPath)){
			mkdir($myTemplatesFolderFullPath);
		}
		$templatesFolder = dirname(__FILE__).'/job_templates';
		\OCP\Util::writeLog('batch', 'Copying'.$templatesFolder.'-->'.$myTemplatesFolderFullPath, \OC_Log::WARN);
		$newfiles = self::recursive_copy($templatesFolder, $myTemplatesFolderFullPath);
		if(!empty($newfiles)){
			$view = \OC\Files\Filesystem::getView();
			$absPath = $view->getAbsolutePath($this->batchFolder);
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
	 * Creates the folder "output_files" inside the chosen work folder.
	 */
	public function createOutputFolder(){
		$batch_folder = \OCP\Config::getUserValue($this->user, 'batch', 'batch_folder');
		\OC\Files\Filesystem::mkdir($batch_folder."/output_files");
	}
	/**
	 * Read the file $jobScript and return the text content.
	 * @param String $jobScript
	 */
	public static function getJobScript($jobScript){
		$filecontent = \OC\Files\Filesystem::file_get_contents($jobScript);
		return $filecontent;
	}
	/**
	 * Save the text content $jobScriptText into file $jobScript.
	 * @param String $jobScript
	 * @param String $jobScriptText
	 */
	public static function saveJobScript($jobScript, $jobScriptText){
		$status = false;
		if(!empty($jobScriptText)){
			$status = \OC\Files\Filesystem::file_put_contents($jobScript, $jobScriptText);
		}
		return $status;
	}
	/**
	 * Return a list of filenames ending in ".sh" or ".py" in the script folder.
	 */
	public function listJobScripts(){
		if(empty($this->batchFolder)){
			\OCP\Util::writeLog('batch', 'Script folder not set.', \OC_Log::WARN);
			return [];
		}
		$batchFolderFullPath = \OC\Files\Filesystem::getLocalFile($this->batchFolder);
		$handle = opendir($batchFolderFullPath);
		$scriptFiles = [];
		while (false !== ($entry = readdir($handle))) {
			\OCP\Util::writeLog('batch', 'Reading '.serialize($entry), \OC_Log::WARN);
			if(empty($entry)){
				break;
			}
			if($entry != "." && $entry != ".." && (substr($entry, -3)=='.sh' || substr($entry, -3)=='.py')){
				$scriptFiles[] = $entry;
			}
		}
		return $scriptFiles;
	}
	public function getContent($uri, $proxy=false){
		\OCP\Util::writeLog('batch', 'Getting URL '.$uri, \OC_Log::WARN);
		$this->keyFile = \OC_Chooser::decryptSDKey($this->user);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyFile);
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($proxy){
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
				echo $data;
				return strlen($data);
			});
		}
		$data = curl_exec($ch);
		curl_close($ch);
		unlink($this->keyFile); // Clean up temporary unencrypted key file
		return $data;
	}
	private function mkCol($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyFile);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	private function delete($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyFile);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $httpcode;
	}
	private function put($url, $str){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyFile);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
	private function uploadFile($file, $url){
		$file = fopen($file, 'r');
		$size = filesize($file);
		$data = fread($file, $size);
		return $this->upload($data, $url);
	}
	private function upload($data, $url){
		//$filename = basename(parse_url($url, PHP_URL_PATH));
		/*$requestBody = '';
		$separator = '-----'.md5(microtime()).'-----';
		$requestBody .= "--$separator\r\n" . "Content-Disposition: form-data; name=\"$filename\"; filename=\"$filename\"\r\n" . "Content-Length: ".strlen($data)."\r\n" . "Content-Transfer-Encoding: binary\r\n" . "\r\n" . "$data\r\n";
		// Terminate the body
		$requestBody .= "--$separator--";*/
		$ch = curl_init($url);
		
		// This is necessary as cURL will ignore the CURLOUT_POSTFIELDS if we use the built-in PUT method
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
		/*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: multipart/form-data; boundary="'.$separator.'"',
		));*/
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_CAINFO, \OCA\FilesSharding\Lib::$wsCACert);
		curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->keyFile);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ScienceData/cURL');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
//////////////////////////
// API functions
/////////////////////////
	public function getJobs(){
		$api_url = \OCP\Config::getUserValue($this->user, 'batch', 'api_url', '');
		if(empty($api_url)){
			\OCP\Util::writeLog('batch', 'API URL not set', \OC_Log::ERROR);
			return false;
		}
		$jobs = [];
		$text = $this->getContent($api_url."db/jobs/?userInfo=".$this->dn);
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
		\OCP\Util::writeLog('batch', 'Returning jobs '.serialize($jobs), \OC_Log::WARN);
		return $jobs;
	}
	public function getJobInfo($identifier){
		if(empty($this->api_url)){
			\OCP\Util::writeLog('batch', 'API URL not set', \OC_Log::ERROR);
			return false;
		}
		$job = [];
		$text = $this->getContent($this->api_url.'db/jobs/'.$identifier.'/');
		$lines = explode("\n", $text);
		foreach($lines as $line){
			if(empty($line)){
				continue;
			}
			$keyVal = explode(": ", $line);
			$job[$keyVal[0]] = $keyVal[1];
		}
		$jobUrl = $this->api_url.'gridfactory/jobs/'.$identifier.'/job';
		$localJobUrl = $this->local_api_url.'gridfactory/jobs/'.$identifier.'/job';
		if(!in_array($jobUrl, explode(' ', $job['inputFileURLs'])) && !in_array($localJobUrl, explode(' ', $job['inputFileURLs']))){
			$job['inputFileURLs'] .= " ".$localJobUrl;
		}
		\OCP\Util::writeLog('batch', 'Returning job '.serialize($job), \OC_Log::WARN);
		return $job;
	}
	public function submitJob($jobScript, $jobScriptText, $inputFile){
		if(empty($this->api_url)){
			return false;
		}
		$this->keyFile = \OC_Chooser::decryptSDKey($this->user);
		if(empty($jobScriptText)){
			$jobScriptText = $this->getJobScript($jobScript);
		}
		$job_id = $this->api_url."gridfactory/jobs/".uniqid();
		$homeServerInternalUrl = \OCA\FilesSharding\Lib::getServerForUser($this->user, true);
		$homeServerPrivateUrl = \OCA\FilesSharding\Lib::internalToPrivate($homeServerInternalUrl);
		$inputFileUrl = $homeServerPrivateUrl.'/grid'.$inputFile;
		$inputFilename = basename($inputFile);
		$inputFileBasename = preg_replace('|\.[^\.]+$|', '', $inputFilename);
		$batch_folder = \OCP\Config::getUserValue($this->user, 'batch', 'batch_folder');
		$batch_folder_url = $homeServerPrivateUrl.'/grid'.$batch_folder;
		# Substitute in job script
		$pos = strpos($jobScriptText, '#GRIDFACTORY');
		$jobScriptText = substr_replace($jobScriptText, "#GRIDFACTORY -u " . $job_id . "\n#GRIDFACTORY", $pos, strlen('#GRIDFACTORY'));
		$jobScriptText = str_replace('IN_FILE_URL', $inputFileUrl, $jobScriptText);
		$jobScriptText = str_replace('IN_FILENAME', $inputFilename, $jobScriptText);
		$jobScriptText = str_replace('IN_BASENAME', $inputFileBasename, $jobScriptText);
		$jobScriptText = str_replace('WORK_FOLDER_URL', $batch_folder_url, $jobScriptText);
		$jobScriptText = str_replace('HOME_SERVER_PRIVATE_URL', $homeServerPrivateUrl, $jobScriptText);
		$jobScriptText = str_replace('MY_SSL_DN', $this->dn, $jobScriptText);
		$jobScriptText = str_replace('SD_USER', $this->user, $jobScriptText);
		\OCP\Util::writeLog('batch', 'Creating job dir '.$job_id, \OC_Log::WARN);
		$this->mkCol($job_id);
		\OCP\Util::writeLog('batch', 'Uploading job '.$job_id."/job", \OC_Log::WARN);
		$this->upload($jobScriptText, $job_id."/job");
		unlink($this->keyFile); // Clean up temporary unencrypted key file
		return $job_id;
	}
	public function requestJobOutput($identifier){
		if(empty($this->api_url)){
			\OCP\Util::writeLog('batch', 'API URL not set', \OC_Log::ERROR);
			return false;
		}
		$this->keyFile = \OC_Chooser::decryptSDKey($this->user);
		$ret = $this->put($this->api_url."db/jobs/".$identifier, 'csStatus: running:requestOutput');
		unlink($this->keyFile); // Clean up temporary unencrypted key file
		return $ret;
	}
	public function deleteJob($identifier){
		if(empty($this->api_url)){
			return false;
		}
		$this->keyFile = \OC_Chooser::decryptSDKey($this->user);
		$jobUrl = $this->api_url . 'gridfactory/jobs/' . $identifier;
		$ret = $this->delete($jobUrl);
		unlink($this->keyFile); // Clean up temporary unencrypted key file
		return $ret;
	}
}

