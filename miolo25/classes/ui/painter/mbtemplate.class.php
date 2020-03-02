<?php
/**
 * Adaptado de Brian E. Lozier (brian@massassi.net) - 2003
 * http://www.sitepoint.com/article/beyond-template-engine
 */

class MBTemplate {
	var $vars; /// Holds all the template variables
	var $path; /// Path to the templates

	/**
	 * Constructor
	 *
	 * @param string $path the path to the templates
	 *
	 * @return void
	 */
	function __construct($path = null) {
		$this->path = $path;
	}

	/**
	 * Set the path to the template files.
	 *
	 * @param string $path path to template files
	 *
	 * @return void
	 */
	function set_path($path) {
		$this->path = $path;
	}

	/**
	 * Set a template variable.
	 *
	 * @param string $name name of the variable to set
	 * @param mixed $value the value of the variable
	 *
	 * @return void
	 */
	function set($name, $value) {
		$this->vars[$name] = $value;
	}

	/**
	 * Open, parse, and return the template file.
	 *
	 * @param string string the template file name
	 *
	 * @return string
	 */
	function fetch($file) {
		extract($this->vars);          // Extract the vars to local namespace
		ob_start();                    // Start output buffering
		include($this->path . $file);  // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean();                // End buffering and discard
		return $contents;              // Return the contents
	}

	/**
	 * Fetch the HTML template file and send to browser via redirect.
	 *
	 * @param string string the template file name
	 *
	 * @return string
	 */
    public function execute($file)
    {
		$MIOLO = MIOLO::getInstance();
		$html = $this->fetch($file);
        $fname = substr(uniqid(md5(uniqid(""))), 0, 10) . '.html';
        $fileexp = $MIOLO->getConf('home.reports') . '/' . $fname;
        $fp = fopen($fileexp, 'x');
        fwrite($fp, $html);
        fclose ($fp);
        $fileout = $MIOLO->getActionURL('miolo', 'reports:' . $fname);
        $MIOLO->getPage()->redirect($fileout);
    }
}

/**
 * An extension to Template that provides automatic caching of
 * template contents.
 */
class MCachedTemplate extends MBTemplate {
	var $cache_id;
	var $expire;
	var $cached;

	/**
	 * Constructor.
	 *
	 * @param string $path path to template files
	 * @param string $cache_id unique cache identifier
	 * @param int $expire number of seconds the cache will live
	 *
	 * @return void
	 */
	function __construct($path, $cache_id = null, $expire = 900) {
		parent::__construct($path);
		$this->cache_id = $cache_id ? 'cache/' . md5($cache_id) : $cache_id;
		$this->expire   = $expire;
	}

	/**
	 * Test to see whether the currently loaded cache_id has a valid
	 * corrosponding cache file.
	 *
	 * @return bool
	 */
	function is_cached() {
		if($this->cached) return true;

		// Passed a cache_id?
		if(!$this->cache_id) return false;

		// Cache file exists?
		if(!file_exists($this->cache_id)) return false;

		// Can get the time of the file?
		if(!($mtime = filemtime($this->cache_id))) return false;

		// Cache expired?
		if(($mtime + $this->expire) < time()) {
			@unlink($this->cache_id);
			return false;
		}
		else {
			/**
			 * Cache the results of this is_cached() call.  Why?  So
			 * we don't have to double the overhead for each template.
			 * If we didn't cache, it would be hitting the file system
			 * twice as much (file_exists() & filemtime() [twice each]).
			 */
			$this->cached = true;
			return true;
		}
	}

	/**
	 * This function returns a cached copy of a template (if it exists),
	 * otherwise, it parses it as normal and caches the content.
	 *
	 * @param $file string the template file
	 *
	 * @return string
	 */
	function fetch_cache($file) {
		if($this->is_cached()) {
			$fp = @fopen($this->cache_id, 'r');
			$contents = fread($fp, filesize($this->cache_id));
			fclose($fp);
			return $contents;
		}
		else {
			$contents = $this->fetch($file);

			// Write the cache
			if($fp = @fopen($this->cache_id, 'w')) {
				fwrite($fp, $contents);
				fclose($fp);
			}
			else {
				die('Unable to write cache.');
			}

			return $contents;
		}
	}
}
?>
