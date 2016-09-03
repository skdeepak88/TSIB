<?php
require_once('interface.php');

class FileBrowser extends Core implements __FileBrowser{

	private $_rootPath;
	private $_currentPath;
	private $_extensionFilter;
	private $_isFileRequest;
	private $_filePath;

	/**
	 * Construct
	 *
	 * @param string $rootPath Directory path to list files
	 * @param string $currentPath Current directory path to list files - will default to $rootPath if null
	 * @param array  $extensionFilter Array of file extensions to filter - will not apply a filter if empty
	 */
	function __construct($rootPath, $currentPath = null, array $extensionFilter = array()){

		$this->_rootPath = $rootPath;
		$this->_currentPath = $currentPath;
		$this->_extensionFilter = $extensionFilter;
	}

	/**
	 * Set private root path
	 */
	function SetRootPath($rootPath){
		$this->_rootPath = $rootPath;
	}

	/**
	 * Set private current path
	 */
	function SetCurrentPath($currentPath){

		// Trim the path
		$currentPath = trim($currentPath, '/');

		// Actual path
		$actualPath = $this->getActualPath($currentPath);

		// If this is a file get the directory name and set it
		if(is_file($actualPath)) {
			$this->_isFileRequest = true;
			$this->_filePath = $actualPath;
			$this->_currentPath = dirname($currentPath);
		}
		else {
			$this->_isFileRequest = false;
			$this->_currentPath = $currentPath;
		}
	}

	/**
	 * Set private extension filter
	 */
	function SetExtensionFilter(array $extensionFilter){
		$this->_extensionFilter = array_filter($extensionFilter);
	}


	/**
	 * Check if the current reques is for a file or directory
	 */
	function isFileRequest() {
		return $this->_isFileRequest;
	}


	/**
	 * Get files using currently-defined object properties
	 * @return array Array of files within the current directory
	 */
	function Get(){

		// Placeholder
		$result = [];

		// Parent navigation
		$parentPath = $this->getParentPath();

		// For the parent node
		$parentStats = $this->getFileStats($parentPath, true);

		// Assing it if we could find the stats
		if($parentStats !== false)
			$result[] = $parentStats;

		// Used to filter unwanted files
		$unwanted = ['.', '..', '.DS_Store'];

		// Get the current directory path
		$currentDir = $this->getActualPath();

		// No point in continuing without a valid path
		if(!file_exists($currentDir)) {
			$this->setError('Current directory is invalid! Please set the configuration and try again.');
			return $result;
		}

		// Get the raw list of files
		$files = scandir($this->getActualPath());

		// Filter dots and unwanted files
		$files = array_diff($files, $unwanted);

		// Prepare the files with some extra data
		foreach($files as $file) {

			// Get the actual path
			$actualPath = $this->getActualPath($file);

			// Skip the files that are not in filter
			if(is_file($actualPath)) {

				$pathInfo = pathinfo($actualPath);
				$extension = $pathInfo['extension'];

				if(!empty($this->_extensionFilter) && !in_array($extension, $this->_extensionFilter))
					continue;
			}

			// Find the stats for the file/folder
			$fileStats = $this->getFileStats($actualPath);

			// Assign it if we can find the stats
			if($fileStats !== false)
				$result[] = $fileStats;
		}

		return $result;
	}


	/**
	 * Used in previewing the file
	 * If the file is image it will return the image else will return the formatted code format
	 * Note: This can be extended to handle .doc, .xls etc files
	 */
	public function getFileContent() {

		// Get the file type
		$pathInfo = pathinfo($this->_filePath);
		$extension = $pathInfo['extension'];
		$extension = strtolower($extension);

		switch ($extension) {

			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':

				// Read image path, convert to base64 encoding
				$imageData = base64_encode(file_get_contents($this->_filePath));

				// Format the image SRC:  data:{mime};base64,{data};
				$src = 'data: '.mime_content_type($this->_filePath).';base64,'.$imageData;

				// Echo out a sample image
				echo '<img src="' . $src . '">';

			break;

			default:

				show_source($this->_filePath);

			break;
		}
	}


	/**
	 * Used to get the actual path recognised by the system
	 * It takes optional boolean parameter which when true it will return path with no root path
	 *
	 * @param string $path the path to get absolute path for
	 * @param boolean $noRoot when true will skip the root path
	 */
	private function getActualPath($path = '', $noRoot = false) {

		$paths = [$this->_rootPath, $this->_currentPath, $path];
		$paths = array_filter($paths);
		$paths = array_map(function($a){ return rtrim($a, '/'); }, $paths);

		if($noRoot)
			array_shift($paths);

		return implode('/', $paths);
	}


	/**
	 * Special method to get the parent path to navigate back
	 * It takes optional boolean parameter which when true it will return path with no root path
	 *
	 * @param boolean $noRoot when true will skip the root path
	 */
	private function getParentPath($noRoot = false) {

		$paths = [$this->_rootPath, $this->_currentPath];
		$paths = array_filter($paths);
		$paths = array_map(function($a){ return rtrim($a, '/'); }, $paths);

		if($noRoot)
			array_shift($paths);

		// Now strip of the last path
		$paths = explode('/', implode('/', $paths));
		array_pop($paths);

		return implode('/', $paths);
	}


	/**
	 * Used to outut pretty size
	 * Note: Can be extended for more sizes
	 */
	private function getFormattedSize($size) {

		// Placeholder
		$result = '';

		if(($size / 1024) < 1)
			$result = number_format($size) . 'B';

		elseif(($size / 1024) < 1024)
			$result = number_format(($size / 1024)) . 'KB';

		elseif(($size / 1024 * 1024))
			$result = number_format(($size / 1024 * 1024)) . 'MB';

		else
			$result = number_format(($size / 1024 * 1024 * 1024)) . 'GB';

		return $result;
	}


	/**
	 * Get the file stats to show on the screen
	 *
	 * @param string $file actual file path
	 * @param boolean $isParent if true the stats will be for parent folder
	 */
	private function getFileStats($file, $isParent = false) {

		if(!file_exists($file))
			return false;

		// Get the stats
		$stats = stat($file);

		// Get the base name
		$ls_basename = basename($file);

		// Get the path for navigation
		if($isParent)
			$path = $this->getParentPath(true);
		else
			$path = $this->getActualPath($ls_basename, true);

		// custom file array
		$data = [];
		$data['name'] = $ls_basename . ($isParent ? ' (Parent)' : '');
		$data['type'] = is_file($file) ? 'FILE' : 'DIR';
		$data['path'] = $path;
		$data['size'] = $this->getFormattedSize($stats['size']);
		$data['modified'] = date('d-m-Y H:i:s', $stats['mtime']);

		return $data;
	}

}