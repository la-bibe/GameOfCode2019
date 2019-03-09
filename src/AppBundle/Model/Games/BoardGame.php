<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 09/03/19
 * Time: 05:06
 */

namespace AppBundle\Model\Games;


use AppBundle\Model\AGame;

class BoardGame extends AGame
{
    private static $WORDS = [
        'bubble',
        'dog',
        'volcano',
        'watch',
        'tomato',
        'earth',
        'church',
        'fish',
        'horse',
        'key',
        'cake',
        'cat',
    ];

    protected function checkPropositionData(array $data): bool
    {
        return true;
    }

    protected static function getName(): string
    {
        return 'board';
    }

    protected static function generateSeed(): array
    {
        return [
            'word' => self::$WORDS[array_rand(self::$WORDS)],
        ];
    }
}
