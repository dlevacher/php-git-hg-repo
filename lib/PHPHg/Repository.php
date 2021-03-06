<?php

namespace PHPHg;

use PHPHg\Command;
use PHPHg\Configuration;

/**
 * Simple PHP wrapper for Hg repository
 *
 * @link      http://github.com/ornicar/php-git-repo
 * @version   1.3.0
 * @author    Blondeau Gabriel <blondeau.gabriel at gmail dot com>
 * @license   
 *
 * Documentation: http://github.com/ornicar/php-git-repo/blob/master/README.markdown
 * Tickets:       http://github.com/ornicar/php-git-repo/issues
 */
class Repository {

    /**
     * @var string  local repository directory
     */
    protected $dir;
    protected $dateFormat = 'iso';
    protected $logFormat = '"%H|%T|%an|%ae|%ad|%cn|%ce|%cd|%s"';

    /**
     * @var boolean Whether to enable debug mode or not
     * When debug mode is on, commands and their output are displayed
     */
    protected $debug;

    /**
     * @var array of options
     */
    protected $options;
    protected static $defaultOptions = array(
        'hg_executable' => '/usr/bin/hg', // path of the executable on the server
        'file_config' => '/.hg/',
        'login' => '',
        'password' => '',
        'repository' => ''
    );

    /**
     * Instanciate a new Git repository wrapper
     *
     * @param   string $dir real filesystem path of the repository
     * @param   boolean $debug
     * @param   array $options
     */
    public function __construct($dir, $debug = false, array $options = array()) {
        $this->dir = $dir;
        $this->debug = $debug;
        $this->options = array_merge(self::$defaultOptions, $options);

        $this->checkIsValidRepo();
        $config = new Configuration($this);
		if (strlen($this->options['repository']) || strlen($this->options['login']) || strlen($this->options['password']))
			$config->setAccount($this->options['repository'], $this->options['login'], $this->options['password']);
    }

    public function __destruct() {
        $this->clearConfiguration();
    }

    /**
     * Get the configuration for current
     * @return Configuration
     */
    public function getConfiguration() {
        return new Configuration($this);
    }

    /**
     * Clear the configuration for current
     * @return Configuration
     */
    public function clearConfiguration() {
        return $this->getConfiguration()->remove();
    }

    /**
     * Return the result of `hg log` formatted in a PHP array
     *
     * @return array list of commits and their properties
     * */
    public function getCommits($nbCommits = 10) {
        $output = $this->cmd(sprintf('log -l %d', $nbCommits));
        return $output;
    }

    /**
     * Return the result of `hg pull` formatted in a PHP array
     * @return test about pulling
     * */
    public function pull($options = "-u") {
        try {
            $output = $this->cmd(sprintf('pull %s %s', $options, $this->options['repository']));
            
            if(strpos($output['output'], "use 'hg resolve'") !== false){
                $output['error'] = true;  
            }               
            
            return $output;
        } catch (Exception $ex) {
            $output['error'] = $ex;
            
            return $output;
        }
    }

    /**
     * Return the result of `hg update` formatted in a PHP array
     * @return array list of commits and their properties
     * */
    public function update($options = "") {
        $output = $this->cmd(sprintf('update %s', $options));
        return $output;
    }

    /**
     * Check if a directory is a valid Hg repository
     */
    public function checkIsValidRepo() {
        if (!file_exists($this->dir . '/.hg')) {
            throw new InvalidHgRepositoryDirectoryException($this->dir . ' is not a valid Hg repository');
        }
    }

    /**
     * Check current version
     */
    public function checkVers($options = "-i") {
		$output = $this->cmd(sprintf('identify %s', $options));
        $output = substr($output['output'], 0, 11);
        return $output;
    }

    /**
     * Check if they are  local files modified
     */
    public function checkFiles($options = "-m") {

        try {
            $output = $this->cmd(sprintf('status %s', $options));
            if (strlen($output['output']) == 0) {
                $output['output'] = "No files modified.";
                $output['modified'] = false;
            } else {
                $output['modified'] = true;
            }
            return $output;
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * Clean local modified files
     */
    public function updateClean($options = "-C") {
        try {
            $output = $this->cmd(sprintf('update %s', $options));
            
            return $output;
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * Back to "x" version
     */
    public function backupVersion($options = "") {
        $output = $this->cmd(sprintf("hg update -r %s", $options));
        return $output;
    }

    /**
     * Run any hg command, like "status" or "checkout -b mybranch origin/mybranch"
     *
     * @throws  RuntimeException
     * @param   string  $commandString
     * @return  string  $output
     */
    public function cmd($commandString) {
// clean commands that begin with "git "
        $commandString = preg_replace('/^hg\s/', '', $commandString);
        $commandString = $this->options['hg_executable'] . ' ' . $commandString;
        $command = new Command($this->dir, $commandString, $this->debug);

        return $command->run();
    }

    /**
     * Get the repository directory
     *
     * @return  string  the repository directory
     */
    public function getDir() {
        return $this->dir;
    }

    /**
     * Get the repository directory
     *
     * @return  string  the repository directory
     */
    public function getFileConfig() {
        return $this->options['file_config'];
    }
    
    /*
     * Set branch
     * 
     * @return output
     */
    public function setBranch($options = "") {
        $output = $this->cmd(sprintf("up %s", $options));
        return $output;
    }
    
}

class InvalidHgRepositoryDirectoryException extends \InvalidArgumentException {
    
}
