<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:10
 */

namespace AppBundle\Model;


use Ratchet\ConnectionInterface;

class Tournament
{
    public static $STATE_LOUNGE = 0;
    public static $STATE_IN_GAME = 1;
    public static $STATE_VOTE = 2;

    /**
     * @var AGame
     */
    private $game;

    /**
     * @var int
     */
    private $size;

    /**
     * @var Client[]
     */
    private $players;

    /**
     * @var Client[]
     */
    private $voters;

    /**
     * @var Client[]
     */
    private $clients;

    /**
     * @var int
     */
    private $state;

    /**
     * @var int
     */
    private $rounds;

    public function __construct(int $size = 8, int $rounds = 3)
    {
        $this->size = $size;
        $this->players = [];
        $this->voters = [];
        $this->clients = [];
        $this->state = self::$STATE_LOUNGE;
        $this->rounds = $rounds;
    }

    private function notify(SocketEvent $event)
    {
        $raw = $event->getRawJson();
        foreach ($this->players as $player)
            $player->send($raw);
        foreach ($this->voters as $voter)
            $voter->send($raw);
        foreach ($this->clients as $client)
            $client->send($raw);
    }

    private function notifyNewPlayer(int $id)
    {
        $this->notify(new SocketEvent('newPlayer', $this->players[$id]->getData()));
    }

    private function notifyNewVoter(int $id)
    {
        $this->notify(new SocketEvent('newVoter', $this->voters[$id]->getData()));
    }

    private function getConnectionId(ConnectionInterface $connection)
    {
        if (isset($connection->resourceId))
            return $connection->resourceId;

        return 0;
    }

    private function sendMessageTo(int $id, string $message): bool
    {
        if ($this->isClient($id))
            $this->clients[$id]->send($message);
        else if ($this->isPlayer($id))
            $this->players[$id]->send($message);
        else if ($this->isVoter($id))
            $this->voters[$id]->send($message);
        else
            return false;

        return true;
    }

    private function welcomeConnection(int $id)
    {
        $event = new SocketEvent('welcome', [
            'self' => $this->clients[$id]->getData(),
            'tournament' => $this->getData(),
        ]);
        $this->sendMessageTo($id, $event->getRawJson());
    }

    private function launchNextGame()
    {
        $this->resetAllClientsActions();
        // TODO check if next game + launch
        $this->changeState(self::$STATE_IN_GAME);
    }

    private function getState()
    {
        switch ($this->state) {
            case self::$STATE_LOUNGE:
                return 'lounge';
            case self::$STATE_IN_GAME:
                return 'game';
            case self::$STATE_VOTE:
                return 'vote';
        }

        return '';
    }

    private function resetAllClientsActions()
    {
        foreach ($this->clients as $client)
            $client->resetAction();
        foreach ($this->players as $player)
            $player->resetAction();
        foreach ($this->voters as $voter)
            $voter->resetAction();
    }

    public function getData(): array
    {
        $data = [
            'size' => $this->size,
            'state' => $this->getState(),
            'game' => [is_null($this->game) ? null : $this->game->getData()],
            'players' => [
                'size' => count($this->players),
                'data' => [],
            ],
            'voters' => [
                'size' => count($this->voters),
                'data' => [],
            ],
        ];
        foreach ($this->players as $player)
            $data['players']['data'][] = $player->getData();
        foreach ($this->voters as $voter)
            $data['voters']['data'][] = $voter->getData();

        return $data;
    }

    public function isFull(): bool
    {
        return count($this->players) == $this->size;
    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function getVoters()
    {
        return $this->voters;
    }

    public function isPlayer(int $id)
    {
        return array_key_exists($id, $this->players);
    }

    public function isVoter(int $id)
    {
        return array_key_exists($id, $this->voters);
    }

    public function isClient(int $id)
    {
        return array_key_exists($id, $this->clients);
    }

    public function getAnyClient(int $id)
    {
        if ($this->isClient($id))
            return $this->clients[$id];
        if ($this->isPlayer($id))
            return $this->players[$id];
        if ($this->isVoter($id))
            return $this->voters[$id];

        return null;
    }

    public function addConnection(ConnectionInterface $connection)
    {
        $id = $this->getConnectionId($connection);
        $this->clients[$id] = new Client($connection);
        $this->welcomeConnection($id);
    }

    private function joinPlayer($id)
    {
        if ($this->state != self::$STATE_LOUNGE) {
            $error = new SocketErrorEvent('Tournament is already launched, please wait for it to be in lounge mode');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($this->isFull()) {
            $error = new SocketErrorEvent('Tournament is full, please join voters instead');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        $this->players[$id] = $this->clients[$id];
        unset($this->clients[$id]);
        $this->notifyNewPlayer($id);
        if ($this->isFull())
            $this->launchNextGame();
    }

    private function joinVoter($id)
    {
        $this->voters[$id] = $this->clients[$id];
        unset($this->clients[$id]);
        $this->notifyNewVoter($id);
    }

    private function handleClientEvent(int $id, SocketEvent $event)
    {
        switch ($event->getType()) {
            case 'playerJoin':
                $this->joinPlayer($id);
                break;
            case 'voterJoin':
                $this->joinVoter($id);
                break;
            default:
                $error = new SocketErrorEvent('Unknown event type for not logged connection');
                $this->sendMessageTo($id, $error->getRawJson());
        }
    }

    private function handlePlayerEvent(int $id, SocketEvent $event)
    {
        if (!$this->players[$id]->canAct()) {
            $error = new SocketErrorEvent('You have already played, wait for the next round');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($this->state != self::$STATE_IN_GAME) {
            $error = new SocketErrorEvent('Wait for game state to play');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($event->getType() != 'play') {
            $error = new SocketErrorEvent('Unknown event type from player');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if (array_key_exists('data', $event->getPayload())) {
            $data = $event->getPayload()['data'];
            if (is_array($data)) {
                $player = $this->players[$id];
                if ($this->game->addProposition($player, $data)) {
                    $this->notify(new SocketEvent('playerAnswered', ['player' => $player->getData()]));
                    $player->act();
                    $this->game->checkEndPropositions();
                } else {
                    $error = new SocketErrorEvent('Wrong format for proposition');
                    $player->send($error->getRawJson());
                }

                return;
            }
        }
        $error = new SocketErrorEvent('Incorrect json');
        $this->sendMessageTo($id, $error->getRawJson());
    }

    private function getWonPointsFromRank(int $rank)
    {
        // TODO
        return (4 - $rank) * 5;
    }

    private function checkEndOfVote()
    {
        foreach ($this->voters as $voter)
            if ($voter->canAct())
                return;
        $results = $this->game->getVoteResults();
        $this->notify(new SocketEvent('voteResult', $results));
        $i = 0;
        foreach ($results as $result)
            if ($i > 4)
                break;
            elseif ($result instanceof Proposition)
                $result->getPlayer()->addPoints($this->getWonPointsFromRank($i++));
        $this->launchNextGame();
    }

    private function handleVoterEvent(int $id, SocketEvent $event)
    {
        if (!$this->voters[$id]->canAct()) {
            $error = new SocketErrorEvent('You have already voted, wait for the next round');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($this->state != self::$STATE_VOTE) {
            $error = new SocketErrorEvent('Wait for vote state to vote');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($event->getType() != 'vote') {
            $error = new SocketErrorEvent('Unknown event type from voter');
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if (array_key_exists('id', $event->getPayload())) {
            $propositionId = $event->getPayload()['id'];
            if (is_numeric($propositionId)) {
                $this->game->vote($propositionId);
                $this->voters[$id]->act();
                $this->notify(new SocketEvent('addVote', ['id' => $propositionId]));
                $this->checkEndOfVote();

                return;
            }
        }
        $error = new SocketErrorEvent('Incorrect json');
        $this->sendMessageTo($id, $error->getRawJson());
    }

    private function handleChatMessage(int $id, SocketEvent $event)
    {
        $from = $this->getAnyClient($id);
        if (array_key_exists('message', $event->getPayload())) {
            $message = $event->getPayload()['message'];
            if (is_string($message)) {
                $this->notify(new SocketEvent('message', [
                    'from' => is_null($from) ? null : $from->getData(),
                    'message' => $message,
                ]));

                return;
            }
        }
        $error = new SocketErrorEvent('Incorrect json');
        $this->sendMessageTo($id, $error->getRawJson());
    }

    private function handleLoggedClientEvent(int $id, SocketEvent $event)
    {
        if ($event->getType() == 'message')
            $this->handleChatMessage($id, $event);
        else if ($this->isPlayer($id))
            $this->handlePlayerEvent($id, $event);
        else if ($this->isVoter($id))
            $this->handleVoterEvent($id, $event);
    }

    private function changeState(int $state)
    {
        $this->state = $state;
        $this->notify(new SocketEvent('changeState', ['state' => $this->state]));
    }

    public function handleMessage(ConnectionInterface $connection, string $message)
    {
        $id = $this->getConnectionId($connection);
        try {
            $event = new SocketEvent($message);
        } catch (\InvalidArgumentException $e) {
            $error = new SocketErrorEvent($e->getMessage());
            $this->sendMessageTo($id, $error->getRawJson());

            return;
        }
        if ($this->isClient($id))
            $this->handleClientEvent($id, $event);
        else
            $this->handleLoggedClientEvent($id, $event);
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function finishPropositions()
    {
        $this->changeState(self::$STATE_VOTE);
        $this->notify(new SocketEvent('propositions', $this->game->getPropositionsVoteData()));
    }

    private function dropPlayer(int $id)
    {
        if (!is_null($this->game))
            $this->game->dropPlayer($this->players[$id]);
        $event = new SocketEvent('playerLeave', $this->players[$id]->getData());
        unset($this->players[$id]);
        $this->notify($event);
    }

    private function dropVoter(int $id)
    {
        $event = new SocketEvent('voterLeave', $this->voters[$id]->getData());
        unset($this->voters[$id]);
        $this->notify($event);
    }

    public function close(ConnectionInterface $connection)
    {
        $id = $this->getConnectionId($connection);
        if ($this->isClient($id))
            unset($this->clients[$id]);
        else if ($this->isPlayer($id))
            $this->dropPlayer($id);
        else if ($this->isVoter($id))
            $this->dropVoter($id);
    }

    private function reset()
    {
        // TODO
    }

    // TODO Log everything + time to play and vote
}
