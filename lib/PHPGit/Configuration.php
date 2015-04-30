<?php

namespace PHPGit;

/**
 * Simple PHP wrapper for Git configuration
 *
 * @link      http://github.com/ornicar/php-git-repo
 * @version   1.3.0
 * @author    Moritz Schwoerer <moritz.schwoerer at gmail dot com>
 * @license   MIT License
 *
 * Documentation: http://github.com/ornicar/php-git-repo/blob/master/README.markdown
 * Tickets:       http://github.com/ornicar/php-git-repo/issues
 */
class Configuration {

    const USER_NAME = 'user.name';
    const USER_EMAIL = 'user.email';

    /**
     * Holds the actual configuration
     * @var array
     */
    protected $configuration = array();

    /**
     * Holds the Git repository instance.
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $gitRepo) {
        $this->repository = $gitRepo;
    }

    /**
     * Get a config option
     * 
     * @param string $configOption The config option to read
     * @param mixed  $fallback  Value will be returned, if $configOption is not set
     * 
     * @return string
     */
    public function get($configOption, $fallback = null) {
        if (isset($this->configuration[$configOption])) {
            $optionValue = $this->configuration[$configOption];
        } else {
            if (array_key_exists($configOption, $this->configuration)) {
                $optionValue = $fallback;
            }

            try {
                $optionValue = $this->repository->cmd(sprintf('config --get ' . $configOption));
                $this->configuration[$configOption] = $optionValue;
            } catch (GitRuntimeException $e) {
                $optionValue = $fallback;
                $this->configuration[$configOption] = null;
            }
        }

        return $optionValue;
    }

    /**
     * Set or change a *repository* config option
     * 
     * @param string $configOption
     * @param mixed  $configValue 
     */
    public function set($configOption, $configValue) {
        $this->repository->cmd(sprintf('config --local %s %s', $configOption, $configValue));
        unset($this->configuration[$configOption]);
    }

    public function setOriginUrl($name, $pwd, $path) {
        $this->repository->cmd(sprintf('config remote.origin.url https://%s:%s@%s', $name, $pwd, $path));
    }

    /**
     * Removes a option from local config
     * 
     * @param string $configOption 
     */
    public function remove($configOption = "remote.origin.url") {
        $this->repository->cmd(sprintf('config --local --unset %s', $configOption));

        //Open file Hgrc
        $fileConfig = fopen($this->repository->getDir() . "/.git/config", 'r');
        //Get contents
        $contents = file_get_contents($this->repository->getDir() . "/.git/config");
        //Delete [remote "origin"]
        $contents = str_ireplace('[remote "origin"]', null, $contents);
        //Delete blanc ligne
        $contents = trim($contents);

        fclose($fileConfig);
        //Re-write contents modify in hgrc
        $fileConfig = fopen($this->repository->getDir() . "/.git/config", 'w+');
        fwrite($fileConfig, $contents);

        fclose($fileConfig);
    }

}
