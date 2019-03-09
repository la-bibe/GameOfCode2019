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
    private static $FIRST_NAMES = [
        'Beautiful',
        'Efficient',
        'Sudden',
        'Demonic',
        'Temporary',
        'Expensive',
        'Drunk',
        'Ordinary',
        'Extraordinary',
        'Sticky',
        'Evanescent',
        'Scary',
        'Cute',
        'Brainy',
        'Stormy',
    ];

    private static $LAST_NAMES = [
        'Rainbow',
        'Unicorn',
        'Poney',
        'Bear',
        'Tiger',
        'Cow',
        'Bull',
        'Cat',
        'Cheese',
        'Kitty',
        'Cherry',
        'Window',
        'Crab',
    ];

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
        $this->name = static::generateRandomName();
        $this->actionDone = false;
        $this->points = 0;
    }

    private static function getRandomElement(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    private static function generateRandomName()
    {
        return static::getRandomElement(static::$FIRST_NAMES) . ' ' . static::getRandomElement(static::$LAST_NAMES);
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
            'points' => $this->getPoints(),
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
        $this->resetAction();
    }

    public function act()
    {
        $this->actionDone = true;
    }

    public function canAct()
    {
        return !$this->actionDone;
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function addPoints(int $points)
    {
        $this->points += $points;
    }

    public static function compare(array $a, array $b)
    {
        if ($a['points'] == $a['points'])
            return 0;
        return $a['points'] > $b['points'] ? 1 : -1;
    }
}
