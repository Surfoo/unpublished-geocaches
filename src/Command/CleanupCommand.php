<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:cleanup',
    description: 'Delete old generated gpx files',
)]
class CleanupCommand extends Command
{
    const MAX_RETENTION = 3600 * 24;

    public function __construct(private ParameterBagInterface $params)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now   = time();
        $files = array_merge(
                    glob($this->params->get('kernel.project_dir') . '/waypoints/*.gpx'),
                    glob($this->params->get('kernel.project_dir') . '/public/gpx/*.gpx')
                );

        foreach ($files as $file) {
            if ($now > filemtime($file) + self::MAX_RETENTION && unlink($file)) {
                $output->writeln("Deleted " . $file);
            }
        }

        return Command::SUCCESS;
    }
}
