<?php

namespace App\Console\Commands;

use App\Console\Traits\DbHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * MigrateCommand
 */
class MigrateCommand extends Command
{
    use DbHelper;

    /**
     * @var string Table name where migrations info is kept
     */
    const MIGRATIONS_TABLE = 'migrations';

    /**
     * Configuration of command
     */
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Command for run migration')
        ;
    }

    /**
     * Execute method of command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir(MIGRATIONS_PATH) || !is_readable(MIGRATIONS_PATH)) {
            throw new \RunTimeException(sprintf('Migrations path `%s` is not good', MIGRATIONS_PATH));
        }

        $output->writeln([
            '<info>Run migrations</info>',
            sprintf('Ensure table `%s` presence', self::MIGRATIONS_TABLE)
        ]);

        try {
            $this->safeCreateTable(self::MIGRATIONS_TABLE);
        } catch (\Exception $e) {
            $output->writeln([
                sprintf('Can\'t ensure table `%s` presence. Please verify DB connection params and presence of database named', self::MIGRATIONS_TABLE),
                sprintf('Error: `%s`', $e->getMessage()),
            ]);
        }

        $finder = new Finder();
        $finder->files()->name('*.php')->in(MIGRATIONS_PATH);

        $this->runActions($finder, $output, self::MIGRATIONS_TABLE, 'up');

        return;
    }
}
