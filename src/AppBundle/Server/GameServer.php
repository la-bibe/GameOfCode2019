<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 20:38
 */

namespace AppBundle\Server;

use AppBundle\Model\Player;
use AppBundle\Model\Tournament;
use AppBundle\Model\Voter;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class GameServer implements MessageComponentInterface
{
    /**
     * @var ConnectionInterface[]
     */
    private $loungeConnections;

    /**
     * @var Tournament
     */
    private $tournament;

    public function __construct()
    {
        $this->loungeConnections = [];
        $this->reinitTournament();
    }

    private function reinitTournament()
    {
        foreach ($this->tournament->getPlayers() as $client)
            $this->loungeConnections[$client->getId()] = $client->getConnection();
        foreach ($this->tournament->getVoters() as $client)
            $this->loungeConnections[$client->getId()] = $client->getConnection();
        $this->tournament = new Tournament();
        foreach ($this->loungeConnections as $connection)
            $this->welcomeNewConnection($connection);
    }

    private function welcomeNewConnection(ConnectionInterface $connection)
    {
        $connection->send($this->tournament->getWelcomeData());
    }

    private function getConnectionId(ConnectionInterface $connection)
    {
        if (isset($connection->resourceId))
            return $connection->resourceId;
        return 0;
    }

    private function isClient(int $id)
    {
        if ($this->tournament->isClient($id))
            return true;
        return false;
    }

    private function clientifyConnection(int $id, string $message)
    {
        if (!array_key_exists($id, $this->loungeConnections))
            return;
        $connection = $this->loungeConnections[$id];
        $splitted = explode(' ', $message);
        switch ($splitted[0]) {
            case 'player':
                if ($this->tournament->isFull() || count($splitted) != 2) {
                    $this->welcomeNewConnection($connection);
                    return;
                }
                if (!$this->tournament->addPlayer(new Player($connection, $splitted[1])))
                    return;
                break;
            case 'voter':
                $this->tournament->addVoter(new Voter($connection));
                break;
            default:
                $this->welcomeNewConnection($connection);
                return;
        }
        unset($this->loungeConnections[$id]);
    }

    public function onOpen(ConnectionInterface $connection)
    {
        $this->loungeConnections[$this->getConnectionId($connection)] = $connection;
        $this->welcomeNewConnection($connection);
    }

    public function onClose(ConnectionInterface $connection)
    {
        $id = $this->getConnectionId($connection);
        if ($this->tournament->isClient($id))
            $this->tournament->drop($id);
        else
            unset($this->loungeConnections[$id]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: '.$e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $connection, $message)
    {
        $id = $this->getConnectionId($connection);
        if (!$this->isClient($id))
            $this->clientifyConnection($id, $message);
        else
            $this->tournament->handleMessage($id, $message);
    }
}
