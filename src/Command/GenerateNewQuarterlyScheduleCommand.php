<?php
namespace App\Command;

use App\Service\Schedule;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateNewQuarterlyScheduleCommand extends Command
{
    private $schedule;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-quarterly-schedule';

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Genereer een planning')
            ->setHelp('Dit commando helpt je om een planning te maken voor de aankomende 3 maanden');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->schedule->generate();
        $writer = Writer::createFromPath('file.csv', 'w+');
        $writer->insertAll($collection->toArray());
        return Command::SUCCESS;
    }
}