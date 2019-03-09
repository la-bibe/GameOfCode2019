<?php

namespace AppBundle\Command;

use AppBundle\Model\Tournament;
use AppBundle\Server\GameServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GocStartGameServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('goc:start-game-server')
            ->setDescription('Start Game of Code 2019 game server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new GameServer()
                )
            ),
            8080,
            '192.168.43.78');
        $tournament = Tournament::getInstance();
        $server->loop->addPeriodicTimer(1, [$tournament, 'update']);
        $server->run();
    }
}
