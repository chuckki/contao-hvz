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

    private $io;
    private $rows       = [];
    private $statusCode = 0;

    protected function configure(): void
    {
        $commandHelp = 'Erzeugt eine PaypalProfilId';
        $this->setName('hvz:getPaypalId')->setDescription($commandHelp);
    }

    protected function executeLocked(InputInterface $input, OutputInterface $output): ?int
    {
        // Framework initialisieren
        $this->framework->initialize();
        $this->io = new SymfonyStyle($input, $output);
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
    }
}
