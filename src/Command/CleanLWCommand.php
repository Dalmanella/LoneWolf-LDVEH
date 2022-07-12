<?php
namespace App\Command;

use App\Controller\TableManagerController;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Repository\CombatEndRepository;

//
#[AsCommand(name: 'app:cleanLW')]

class CleanLWCommand extends Command
{
    public function __construct( TableManagerController $tmc, CombatEndRepository $clearCombat){
        parent::__construct();
        $this->tmc = $tmc;
        $this->clearCombat = $clearCombat;
    }

    protected static $defaultName = 'app:cleanLW';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cc=$this->clearCombat;
        $this->tmc->clearCombatInfo($cc);
      
        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;
    }
}