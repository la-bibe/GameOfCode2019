<?php
/**
 * Created by PhpStorm.
 * User: neodar
 * Date: 09/03/19
 * Time: 05:08
 */

namespace AppBundle\Model;


use AppBundle\Model\Games\BoardGame;
use AppBundle\Model\Games\FashionGame;
use AppBundle\Model\Games\MountainGame;

class GameFactory
{
    private static $GAMES = [
        'fashion',
        'board',
        'raclette',
    ];

    private static function getGame(string $name, Tournament $tournament): ?AGame
    {
        switch ($name) {
            case 'fashion':
                return new FashionGame($tournament);
            case 'board':
                return new BoardGame($tournament);
            case 'raclette':
                return new MountainGame($tournament);
            default:
                return null;
        }
    }

    public static function getIndexedGame(int $id, Tournament $tournament): ?AGame
    {
        if (!array_key_exists($id, static::$GAMES))
            return null;
        return static::getGame(static::$GAMES[$id], $tournament);
    }

    public static function getRandomGame(Tournament $tournament): ?AGame
    {
        return static::getIndexedGame(array_rand(static::$GAMES), $tournament);
    }
}
