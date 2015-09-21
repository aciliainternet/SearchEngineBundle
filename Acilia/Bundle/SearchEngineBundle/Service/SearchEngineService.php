<?php
namespace Acilia\Bundle\MetaTagsBundle\Service;

use Acilia\Bundle\SearchEngineBundle\Library\SearchEngineException;

class SearchEngineService
{
    private $config = [];

    private $connection = null;

    /**
     * @param string        $host
     * @param string        $port
     * @param string        $user
     * @param string        $password
     */
    public function __construct($host, $port, $user, $password)
    {
        $this->config['host'] = $host;
        $this->config['port'] = $port;
        $this->config['user'] = $user;
        $this->config['password'] = $password;
    }

    /**
     * Create SphinxQL connection using PDO and save it into private property $connection.
     *
     * @throws \Exception
     */
    private function connect()
    {
        try {
            $dsn = 'mysql:host='.$this->config['host'].';port='.$this->config['port'].';charset=utf8;';
            $this->connection = new \PDO($dsn, $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new SearchEngineException($e->getMessage());
        }
    }

}