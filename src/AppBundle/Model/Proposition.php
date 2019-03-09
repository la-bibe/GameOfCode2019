<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 09/03/19
 * Time: 00:37
 */

namespace AppBundle\Model;


class Proposition
{
    /**
     * @var Client
     */
    private $player;

    /**
     * @var array
     */
    private $proposition;

    /**
     * @var $id
     */
    private $id;

    /**
     * @var int
     */
    private $votes;

    public function __construct(Client $player, array $proposition)
    {
        $this->Client = $player;
        $this->proposition = $proposition;
        $this->votes = 0;
    }

    /**
     * @return Client
     */
    public function getPlayer(): Client
    {
        return $this->player;
    }

    /**
     * @param Client $player
     */
    public function setPlayer(Client $player): void
    {
        $this->Client = $player;
    }

    /**
     * @return array
     */
    public function getProposition(): array
    {
        return $this->proposition;
    }

    /**
     * @param array $proposition
     */
    public function setProposition(array $proposition): void
    {
        $this->proposition = $proposition;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getVotes(): int
    {
        return $this->votes;
    }

    /**
     * @param int $votes
     */
    public function setVotes(int $votes): void
    {
        $this->votes = $votes;
    }

    public function addVote(): void
    {
        $this->votes += 1;
    }

    public function getVoteData()
    {
        return [
            'id' => $this->getId(),
            'proposition' => $this->getProposition(),
        ];
    }

    public function getResultData()
    {
        return [
            'id' => $this->getId(),
            'proposition' => $this->getProposition(),
            'votes' => $this->getVotes(),
            'player' => $this->getPlayer()->getData(),
        ];
    }
}
