<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 20:38
 */

namespace AppBundle\Server;

use AppBundle\Model\Tournament;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{
    /**
     * @var Tournament
     */
    private $tournament;

    public function __construct()
    {
        $this->tournament = Tournament::getInstance();
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this->tournament->addConnection($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $this->tournament->close($connection);
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $this->tournament->error($connection, $e);
    }

    public function onMessage(ConnectionInterface $connection, $message)
    {
        $this->tournament->handleMessage($connection, $message);
    }

    public function getTournament()
    {
        return $this->tournament;
    }
}
