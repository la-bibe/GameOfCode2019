<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:10
 */

namespace AppBundle\Model;


class AGame
{
    protected static $name = '';

    /**
     * @var Proposition[]
     */
    private $propositions;

    /**
     * @var Tournament
     */
    private $tournament;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    private function finishPropositions()
    {
        shuffle($this->propositions);
        for ($i = 0; $i < count($this->propositions); $i++)
            $this->propositions[$i]->setId($i);
        $this->tournament->finishPropositions();
    }

    public function getData(): array
    {
        $data = [
            'name' => static::$name,
        ];
        // TODO
        return $data;
    }

    public function addProposition(Client $player, array $data)
    {
        $this->propositions[] = new Proposition($player, $data);
        if (count($this->propositions) == count($this->tournament->getPlayers()))
            $this->finishPropositions();
    }

    public function getPropositionsVoteData()
    {
        $data = [];
        foreach ($this->propositions as $proposition)
            $data[] = $proposition->getVoteData();
        return $data;
    }

    public function dropPlayer(Client $player)
    {
//        for ($i = 0; $i < count($this->propositions); $i++)
//            if ($this->propositions[$i]->getPlayer() === $player) {
//                unset($this->propositions[$i]);
//                return;
//            }
//        TODO
    }
}
