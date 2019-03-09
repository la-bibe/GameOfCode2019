<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:12
 */

namespace AppBundle\Model;


use Ratchet\ConnectionInterface;

class Client
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var null | AGame
     */
    private $game;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $actionDone;

    /**
     * @var int
     */
    private $points;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->game = null;
        $this->name = $this->generateRandomName();
        $this->actionDone = false;
        $this->points = 0;
    }

    private function generateRandomName()
    {
//        TODO
        return 'aled';
    }

    public function send(string $text)
    {
        $this->connection->send($text);
    }

    private function getConnectionId(ConnectionInterface $connection)
    {
        if (isset($connection->resourceId))
            return $connection->resourceId;
        return 0;
    }

    public function getId()
    {
        return $this->getConnectionId($this->connection);
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function setGame(AGame $game)
    {
        $this->game = $game;
//        $this->send($this->game->getData()); TODO
    }

    public function getData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function resetAction()
    {
        $this->actionDone = false;
    }

    public function reset()
    {
        $this->points = 0;
    }

    public function act()
    {
        $this->actionDone = true;
    }

    public function canAct()
    {
        return !$this->actionDone;
    }

    public function getPoint()
    {
        return $this->points;
    }

    public function addPoints(int $points)
    {
        $this->points += $points;
    }
}
