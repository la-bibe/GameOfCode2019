<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:12
 */

namespace AppBundle\Model;


use Ratchet\ConnectionInterface;

class AClient
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var null | AGame
     */
    private $game;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->game = null;
    }

    public function send(string $text)
    {
        $this->connection->send($text);
    }

    public function getId()
    {
        return $this->connection->resourceId;
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function setGame(AGame $game)
    {
        $this->game = $game;
        $this->send($this->game->getFirstData());
    }
}
