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
     * Holds the Git repository instance.
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $hgRepo) {
        $this->repository = $hgRepo;
    }

    /**
     * Get a config option
     * 
     * @param string $configOption The config option to read
     * @param mixed  $fallback  Value will be returned, if $configOption is not set
     * 
     * @return string
     */
    public function get() {
        $config = parse_ini_file($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", true);
        var_dump($config);
        return $config;
    }

    /**
     * Set or change a *repository* config option
     * 
     * @param string $configOption
     * @param mixed  $configValue 
     */
    public function setAccount($username, $password, $prefix = null) {
        $this->remove($prefix);
        //Get file : hgrc
        $config = parse_ini_file($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", true);
        //Get username and password
        $prefix = $config['paths']['default'];
        preg_match('/([a-zA-Z]{1,}+)(\.)([a-z]{2,4})/', $prefix, $prefix);
        //Open file Hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'r');
        //Get contents
        $contents = file_get_contents($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc");
        //Replace username
        $contents = str_replace($prefix[0] . '.username =', $prefix[0] . '.username = ' . $username, $contents);
        //Replace password
        $contents = str_replace($prefix[0] . '.password = ', $prefix[0] . '.password = ' . $password, $contents);
        //Replace prefix
//        if ($prefix != null) {
//            $contents = str_replace($prefix[0] . '.prefix =', $prefix[0] . '.prefix' . $prefix, $contents);
//        }

        fclose($fileConfig);
        //Re-write contents modify in hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'w+');
        fwrite($fileConfig, $contents);
        fclose($fileConfig);
    }

    public function setPath($path) {
        //Get file : hgrc
        $config = parse_ini_file($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", true);
        //Get username and password
        $default = $config['paths']['default'];


        //Open file Hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'r');
        //Get contents
        $contents = file_get_contents($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc");
        //Delete username
        $contents = str_replace($default, $path, $contents);

        //Re-write contents modify in hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'w+');
        fwrite($fileConfig, $contents);
        fclose($fileConfig);
    }

    /**
     * Removes a option from local config
     * 
     * @param string $configOption 
     */
    public function remove($prefixAuth = null) {
        //Get file : hgrc
        $config = parse_ini_file($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", true);

        //Get username and password
        $prefix = $config['paths']['default'];
        preg_match('/([a-zA-Z]{1,}+)(\.)([a-z]{2,4})/', $prefix, $prefix);
        //Open file Hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'r');
        //Get contents
        $contents = file_get_contents($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc");
        //Delete username
        $contents = str_replace($config['auth'][$prefix[0] . '.username'], "", $contents);
        //Delete password
        $contents = str_replace($config['auth'][$prefix[0] . '.password'], "", $contents);
        //Delete prefix
        if ($prefixAuth != null) {
           $contents = str_replace($config['auth'][$prefix[0] . '.$prefixAuth'], "", $contents);
        }
        
        fclose($fileConfig);
        //Re-write contents modify in hgrc
        $fileConfig = fopen($this->repository->getDir() . "" . $this->repository->getFilleConfig() . "hgrc", 'w+');
        fwrite($fileConfig, $contents);
        fclose($fileConfig);
    }

}
