<?php

namespace AppBundle\Command;

use AppBundle\Model\Tournament;
use AppBundle\Server\GameServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GocStartGameServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('goc:start-game-server')
            ->setDescription('Start Game of Code 2019 game server')
            ->addArgument('host', InputArgument::REQUIRED, 'Host address')
            ->addArgument('port', InputArgument::REQUIRED, 'Port to run the server')
            ->addArgument('players', InputArgument::REQUIRED, 'Number of players per tournament')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new GameServer()
                )
            ),
            intval($input->getArgument('port')),
            $input->getArgument('host'));
        $tournament = Tournament::getInstance(intval($input->getArgument('players')));
        $server->loop->addPeriodicTimer(1, [$tournament, 'update']);
        $server->run();
    }
}
