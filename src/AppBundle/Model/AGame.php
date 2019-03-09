<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 08/03/19
 * Time: 21:10
 */

namespace AppBundle\Model;


abstract class AGame
{
    /**
     * @var Proposition[]
     */
    private $propositions;

    /**
     * @var Tournament
     */
    private $tournament;

    /**
     * @var array
     */
    private $seed;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        $this->seed = static::generateSeed();
    }

    abstract protected function checkPropositionData(array $data): bool;

    abstract protected static function getName(): string;

    abstract protected static function generateSeed(): array;

    public function getSeed()
    {
        return $this->seed;
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
        return [
            'name' => static::getName(),
            'seed' => $this->getSeed(),
        ];
    }

    public function addProposition(Client $player, array $data)
    {
        if (!$this->checkPropositionData($data))
            return false;
        $this->propositions[] = new Proposition($player, $data);
        return true;
    }

    public function checkEndPropositions()
    {
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

    public function getVoteResults()
    {
        $data = [];
        foreach ($this->propositions as $proposition)
            $data[] = $proposition->getResultData();
        usort($data, ['Proposition', 'compare']);
        return $data;
    }

    public function dropPlayer(Client $player)
    {
//        for ($i = 0; $i < count($this->propositions); $i++)
//            if ($this->propositions[$i]->getPlayer() === $player) {
//                unset($this->propositions[$i]);
//                return;
//            }
//        TODO ?
    }

    public function vote(int $id)
    {
        $this->propositions[$id]->addVote();
    }
}
