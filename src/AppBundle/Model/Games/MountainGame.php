<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 09/03/19
 * Time: 05:06
 */

namespace AppBundle\Model\Games;


use AppBundle\Model\AGame;

class MountainGame extends AGame
{
    protected function checkPropositionData(array $data): bool
    {
        return true;
    }

    protected static function getName(): string
    {
        return 'raclette';
    }

    protected static function generateSeed(): array
    {
        return [];
    }
}
