<?php

namespace Acilia\Bundle\SearchEngineBundle\Service;

use Acilia\Bundle\SearchEngineBundle\Library\SearchEngineException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SearchEngineService
{
    protected array $config = [];
    protected ParameterBagInterface $params;
    private \PDO $connection = null;

    public function __construct(ParameterBagInterface $params)
    {
        $this->config['host'] = $params->get('search.host');
        $this->config['port'] = $params->get('search.port');
        $this->config['user'] = $params->get('search.user');
        $this->config['password'] = $params->get('search.pass');
    }

    /**
     * Create SphinxQL connection using PDO and save it into private property $connection.
     *
     * @throws SearchEngineException
     */
    protected function connect(): void
    {
        if ($this->config['host'] == 'localhost') {
            $this->config['host'] = '127.0.0.1';
        }

        try {
            $dsn = 'mysql:host=%host%;port=%port%;charset=utf8;';
            $dsn = str_replace(['%host%', '%port%'], [$this->config['host'], $this->config['port']], $dsn);

            $this->connection = new \PDO($dsn, $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            throw new SearchEngineException(sprintf('Could not connect to Sphinx daemon (%s)', $e->getMessage()));
        }
    }

    public function query(string $query): \PDOStatement
    {
        if ($this->connection ==  null) {
            $this->connect();
        }

        $result = $this->connection->query($query);
        if (!$result instanceof \PDOStatement) {
            throw new SearchEngineException('Incorrect results retrieved from Sphinx daemon');
        }

        return $result;
    }

    public function escapeQuery(string $query): string
    {
        $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=', "'", "\x00", "\n", "\r", "\x1a");
        $to = array('\\\\', '\\\(','\\\)','\\\|','\\\-','\\\!','\\\@','\\\~','\\\"', '\\\&', '\\\/', '\\\^', '\\\$', '\\\=', "\\'", '\\x00', '\\n', '\\r', '\\x1a');

        return str_replace($from, $to, $query);
    }
}
