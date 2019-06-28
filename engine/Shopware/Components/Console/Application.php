<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Console;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner as DoctrineConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Shopware\Kernel;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private const IMPORTANT_COMMANDS = ['sw:migration:migrate', 'sw:cache:clear'];

    /**
     * @var \Shopware\Kernel
     */
    private $kernel;

    /**
     * @var bool
     */
    private $commandsRegistered = false;

    /**
     * @var bool
     */
    private $skipDatabase = false;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct('Shopware', $kernel->getRelease()['version'] . ' - /' . $kernel->getEnvironment() . ($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate process.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
    }

    /**
     * Gets the Kernel associated with this Console.
     *
     * @return \Shopware\Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->kernel->boot();
        } catch (\Exception $e) {
            $this->kernel->boot(true);
            $formatter = $this->getHelperSet()->get('formatter');
            $output->writeln($formatter->formatBlock('WARNING! ' . $e->getMessage() . ' in ' . $e->getFile(), 'error'));
            $this->skipDatabase = true;
        }

        if (!$this->commandsRegistered) {
            $this->setCommandLoader($this->kernel->getContainer()->get('console.command_loader'));

            if (!in_array($input->getFirstArgument(), self::IMPORTANT_COMMANDS, true)) {
                $this->registerCommands($output);
            }

            $this->commandsRegistered = true;
        }

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->kernel->getContainer());
        }

        /** @var \Enlight_Event_EventManager $eventManager */
        $eventManager = $this->kernel->getContainer()->get('events');

        $event = $eventManager->notifyUntil('Shopware_Command_Before_Run', [
            'command' => $command,
            'input' => $input,
            'output' => $output,
        ]);

        if ($event) {
            return (int) $event->getReturn();
        }

        $exitCode = parent::doRunCommand($command, $input, $output);

        $eventManager->notify('Shopware_Command_After_Run', [
           'exitCode' => $exitCode,
           'command' => $command,
           'input' => $input,
           'output' => $output,
       ]);

        return $exitCode;
    }

    protected function registerCommands(OutputInterface $output)
    {
        $this->registerTaggedServiceIds();

        if (!$this->skipDatabase) {
            // Wrap database related logic in a try-catch
            // so that non-db commands can still execute
            try {
                $em = $this->kernel->getContainer()->get('models');

                // Setup doctrine commands
                $helperSet = $this->getHelperSet();
                $helperSet->set(new EntityManagerHelper($em), 'em');
                $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');

                DoctrineConsoleRunner::addCommands($this);

                $this->registerEventCommands();

                foreach ($this->kernel->getPlugins() as $plugin) {
                    if ($plugin->isActive()) {
                        $plugin->registerCommands($this);
                    }
                }
            } catch (\Exception $e) {
                $formatter = $this->getHelperSet()->get('formatter');
                $output->writeln($formatter->formatBlock('WARNING! ' . $e->getMessage() . ' in ' . $e->getFile(), 'error'));
            }
        }
    }

    protected function registerEventCommands()
    {
        $this->kernel->getContainer()->load('plugins');

        /** @var \Enlight_Event_EventManager $eventManager */
        $eventManager = $this->kernel->getContainer()->get('events');

        $collection = new ArrayCollection();
        $collection = $eventManager->collect('Shopware_Console_Add_Command', $collection, ['subject' => $this]);

        /** @var Command $command */
        foreach ($collection as $command) {
            if ($command instanceof Command) {
                $this->add($command);
            }
        }
    }

    /**
     * Register tagged commands in Symfony style
     *
     * @see AddConsoleCommandPass
     */
    protected function registerTaggedServiceIds()
    {
        $lazyServices = array_keys($this->kernel->getContainer()->getParameter('console.lazy_command.ids'));

        if ($this->kernel->getContainer()->hasParameter('console.command.ids')) {
            foreach ($this->kernel->getContainer()->getParameter('console.command.ids') as $id) {
                if (!in_array($id, $lazyServices)) {
                    $this->add($this->kernel->getContainer()->get($id));
                }
            }
        }
    }
}
