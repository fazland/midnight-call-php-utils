<?php declare(strict_types=1);

namespace MidnightCall\Utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @method self addOption(string $name, string|array|null $shortcut = null, int|null $mode = null, string $description = '', string|string[]|int|bool|null $default = null)
 */
trait BaseTrait
{
    private StyleInterface $io;

    private bool $dryRun;

    private function getStyle(InputInterface $input, OutputInterface $output): StyleInterface
    {
        $this->io = new SymfonyStyle($input, $output);

        return $this->io;
    }

    private function setDryRun(InputInterface $input): void
    {
        $this->dryRun = $input->getOption('dry-run') ?? false;
    }

    private function printPrologue(string $title): void
    {
        $this->io->title($title);

        if ($this->dryRun) {
            $this->io->warning('Executing in dry-run mode');
        }
    }

    private function addDryRunOption(): self
    {
        return $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'If set, no changes are committed to db'
        );
    }
}
