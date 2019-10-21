<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle\Command;

use Chuckki\ContaoHvzBundle\HvzPaypal;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\System;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PaypalCommand extends AbstractLockedCommand
{
    use FrameworkAwareTrait;

    /** @var SymfonyStyle */
    private $io;

    private $rows = [];

    private $statusCode = 0;

    protected function configure(): void
    {
        $commandHelp = 'Erzeugt eine PaypalProfilId';
        $this->setName('hvz:getPaypalId')//    ->setDefinition([$argument])   // Die Parameter werden als Array übergeben, so kann es mehr als ein geben.
        ->setDescription($commandHelp);
    }

    protected function executeLocked(InputInterface $input, OutputInterface $output): ?int
    {
        // Framework initialisieren
        $this->framework->initialize();

        $this->io = new SymfonyStyle($input, $output);

        /* Wird hier nicht benötigt, ist aber ganz nützlich.

        // Der Container steht im Konstruktor noch nicht zur Verfügung und kann somit nicht injiziert werden!
        $this->di = $this->getContainer()->get('event_dispatcher');

        // TL_ROOT kann nicht injiziert werden und steht im Command nicht zur Verfügung!
        // Deshalb wird hier das root directory ausgelesen.
        $rootDir = $this->getContainer()->getParameter('kernel.project_dir');

        */

        // Hier wird der Kommandozeilenparameter ausgelesen.
        //$name = $input->getArgument('name');

        // Hier wird die eigentliche Verarbeitung auf gerufen.
        $this->getPaypalProvilId();

        if (!empty($this->rows)) {
            $this->io->newLine();
            $this->io->table(['', 'Ouput', 'Target / Error'], $this->rows);
        }

        return $this->statusCode;
    }

    protected function getPaypalProvilId(): void
    {
        $paypalProfil = System::getContainer()->get('chuckki.contao_hvz_bundle.paypal');
        /** @var HvzPaypal $paypalProfil */
        $profilId = $paypalProfil->createProfile();
        $this->io->text('Profil-ID: '.$profilId->getId());
        // Hier findet die eigentliche Verarbeitung statt.
        // Normalerweise würde hier z.B. ein Event aufgerufen.
    }
}
