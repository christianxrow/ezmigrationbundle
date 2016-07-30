<?php

namespace Kaliop\eZMigrationBundle\Command;

use Kaliop\eZMigrationBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to display the status of migrations.
 */
class StatusCommand extends AbstractCommand
{
    /**
     * Setup the commands.
     *
     * Define the name, options and help text.
     */
    protected function configure()
    {
        $this->setName('kaliop:migration:status')
            ->setDescription('List available migrations and their status.')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "The directory or file to load the migration definitions from"
            )
            ->setHelp(
                <<<EOT
The <info>kaliop:migration:status</info> command displays the status of all available migrations in your bundles:

<info>./ezpublish/console kaliop:migration:status</info>

You can optionally specify the path to migration versions with <info>--path</info>:

<info>./ezpublish/console kaliop:migrations:status --path=/path/to/bundle/version directory_name/version1 --path=/path/to/bundle/version_directory_name/version2</info>
EOT
            );
    }

    /**
     * Run the command and display the results.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationsService = $this->getMigrationService();

        $migrationDefinitions = $migrationsService->getDefinitions($input->getOption('path')) ;

        $migrations = $migrationsService->getMigrations();

return;

/// *** BELOW THE FOLD: TO BE REFACTORED ***

        // Get paths to look for version files in
        $paths = $input->getOption('paths');

        $configuration = $this->getConfiguration($input, $output);

        if ($paths) {
            $paths = is_array($versions) ? $versions : array($versions);
            $paths = $this->groupPathsByBundle($paths);
        } else {
            $paths = $this->groupPathsByBundle($this->getBundlePaths());
        }

        // Check paths for version files

        /* @var $configuration \Kaliop\eZMigrationBundle\Core\Configuration */
        $configuration->registerVersionFromDirectories($paths);

        if ($bundleVersions = $configuration->getVersions()) {
            $migratedVersions = $configuration->getMigratedVersions();

            $output->writeln("\n <info>==</info> Available Migrations\n");

            foreach ($bundleVersions as $bundle => $versions) {
                $output->writeln("<info>{$bundle}</info>:");

                foreach ($versions as $versionNumber => $versionClass) {
                    /** @var $versionClass \Kaliop\eZMigrationBundle\Core\Version */
                    $isMigrated = array_key_exists($bundle, $migratedVersions) && in_array(
                            $versionNumber,
                            $migratedVersions[$bundle]
                        );
                    $status = $isMigrated ? "  <info>< migrated ></info>  " : "<error>< not migrated ></error>";
                    $output->writeln(
                        $status . " " . date(
                            "Y-m-d H:i:s",
                            strtotime($versionNumber)
                        ) . " (<comment>{$versionNumber}</comment>) (<info>$versionClass->type</info>) : " . $versionClass->description
                    );
                }
                $output->writeln('');
            }
        } else {
            $output->writeln('<info>No versions found. Exiting...</info>');
        }
    }
}
