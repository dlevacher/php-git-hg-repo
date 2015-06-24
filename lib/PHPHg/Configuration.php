<?php

namespace PHPHg;

class Configuration {

    const USER_NAME = 'user.name';
    const USER_EMAIL = 'user.email';

    /**
     * Holds the actual configuration
     * @var array
     */
    protected $configuration = array();

    /**
     * Holds the Hg repository instance.
     * @var Repository
     */
    protected $repository;

    /**
     * Holds the credentials name.
     * @var credentialsName
     */
    protected $credentialsName = null;

    public function __construct(Repository $hgRepo) {
        $this->repository = $hgRepo;
        if (!($this->configuration = @parse_ini_file($this->repository->getDir() . $this->repository->getFileConfig() . "hgrc", true)))
			$this->configuration = array();
    }
	
	private function arr2ini(array $a, array $parent = array())
	{
		$out = '';
		foreach ($a as $k => $v)
		{
			if (is_array($v))
			{
				//subsection case
				//merge all the sections into one array...
				$sec = array_merge((array) $parent, (array) $k);
				//add section information to the output
				$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
				//recursively traverse deeper
				$out .= $this->arr2ini($v, $sec) . PHP_EOL;
			}
			else
			{
				//plain key->value case
				$out .= "$k = $v" . PHP_EOL;
			}
		}
		return $out;
	}

    /**
     * Compute the repository credentials name
     * 
     * @return string
     */
    protected function computeCredentialsName($prefix = null) {
		if (!$prefix) {
			$paths = $this->get('paths');
			if ($paths && isset($paths['default'])) {
				$prefix = $paths['default'];
			}
		}
		preg_match('/([http:\/\/|https:\/\/|ssh:\/\/])*(\w@)*([\w\.\-_]{1,}+)/', $prefix, $prefix);
		if (count($prefix)) return end($prefix);
		return basename($this->repository->getDir());
    }

    /**
     * Get a config option
     * 
     * @param string $configOption The config option to read
     * @param mixed  $fallback  Value will be returned, if $configOption is not set
     * 
     * @return string
     */
    protected function get($configOption, $fallback = null) {
		return isset($this->configuration[$configOption]) ? $this->configuration[$configOption] : $fallback;
    }

    /**
     * Set a config option
     * 
     * @param array $configOption The config option to write
     * 
     * @return Configuration
     */
    protected function set(array $configOption) {
        $this->configuration = array_merge($this->configuration, $configOption);
		return $this;
    }

    /**
     * Remove a config option
     * 
     * @param string $configOption The config option to write
     * 
     * @return Configuration
     */
    protected function rm($configOption) {
		if (isset($this->configuration[$configOption]))
			unset($this->configuration[$configOption]);
		return $this;
    }

    /**
     * Save the config to file
     * 
     * @return boolean
     */
    protected function save() {
        // write contents modify in hgrc
        if ($fileConfig = @fopen($this->repository->getDir() . $this->repository->getFileConfig() . "hgrc", 'w+')) {
			$ret = fwrite($fileConfig, $this->arr2ini($this->configuration));
			fclose($fileConfig);
			return $ret;
		}
		return false;
    }

    /**
     * Set or change a *repository* config option
     * 
     * @param string $prefix
     * @param string $username
     * @param string $password	
     * 
     * @return Configuration
     */
    public function setAccount($prefix, $username, $password) {
		$this->credentialsName = $this->computeCredentialsName($prefix);
		// Looking for existing credentials
		$credentials = array();
		if ($auth = $this->get('auth')) {
			foreach($this->configuration['auth'] as $k => $v) {
				$pos = strrpos($k, '.'); $attr = substr($k, $pos + 1); $pref = substr($k, 0, $pos);
				if (!array_key_exists($pref, $credentials)) $credentials[$pref] = array();
				$credentials[$pref][$attr] = $v;
			}
		}
		if (count($credentials)) {
			foreach($credentials as $k => $auth) {
				if (isset($auth['prefix']) && $auth['prefix'] == $prefix) {
					$this->credentialsName = $k;
					if (isset($auth['username']) || isset($auth['password'])) {
						if (	isset($auth['username']) && ($auth['username'] == $username || !strlen($username))
							&&	isset($auth['password']) && ($auth['password'] == $password || !strlen($username))
							)
						{
							// Same user/password
							return $this;
						}
						else {
							// user/password DO NOT match
						}
					}
					else {
						// user/password not defined
					}
					$PHPHg = array("{$this->credentialsName}.prefix" => $auth['prefix']);
					if (isset($auth['username'])) $PHPHg["{$this->credentialsName}.username"] = $auth['username'];
					if (isset($auth['password'])) $PHPHg["{$this->credentialsName}.password"] = $auth['password'];
					$this->set(array('PHPHg' => $PHPHg))->save();
					break;
				}
			}
		}
		else {
			// no credentials
			$this->set(array('PHPHg' => array()))->save();
		}

		$this->set(array('auth' => array(
			"{$this->credentialsName}.prefix" => $prefix,
			"{$this->credentialsName}.username" => $username,
			"{$this->credentialsName}.password" => $password,
		)))->save();
		
		return $this;
    }

    /**
     * Removes a option from local config
     * 
     * @param string $configOption 
     * 
     * @return Configuration
     */
    public function remove() {
		$this->rm('auth');
		if ($PHPHg = $this->get('PHPHg'))
			$this->set(array('auth' => $PHPHg));
		$this->rm('PHPHg')->save();
		return $this;
    }

}
