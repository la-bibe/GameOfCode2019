<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:10
 */

namespace AppBundle\Model;


class Tournament
{
    /**
     * @var AGame[]
     */
    private $games;

    /**
     * @var int
     */
    private $size;

    /**
     * @var Player[]
     */
    private $players;

    /**
     * @var Voter[]
     */
    private $voters;

    public function __construct(int $size = 8)
    {
        $this->games = [];
        $this->size = $size;
        $this->players = [];
        $this->voters = [];
    }

    public function getWelcomeData(): string
    {
        // TODO
        return '';
    }

    public function isFull(): bool
    {
        return count($this->players) < $this->size;
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
        return $this->isPlayer($id) || $this->isVoter($id);
    }

    public function addPlayer(Player $player): bool
    {
        if ($this->isFull())
            return false;
        $this->players[$player->getId()] = $player;
        // TODO Welcome ?
        return true;
    }

    public function addVoter(Voter $voter)
    {
        $this->voters[$voter->getId()] = $voter;
        // TODO Welcome ?
    }

    public function drop(int $id)
    {
        // TODO
    }

    public function handleMessage(int $id, string $message)
    {
        // TODO
    }
}
