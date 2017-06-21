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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Application extends BaseApplication
{
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

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct('Shopware', Kernel::VERSION . ' - ' . '/' . $kernel->getEnvironment() . ($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'));
        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate process.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
    }

    /**
     * Gets the Kernel associated with this Console.
     *
     * @return KernelInterface A KernelInterface instance
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
            $this->registerCommands($output);
            $this->commandsRegistered = true;
        }

        $container = $this->kernel->getContainer();

        foreach ($this->all() as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($container);
            }
        }

        if (true === $input->hasParameterOption(['--shell', '-s'])) {
            $shell = new Shell($this);
            $shell->setProcessIsolation($input->hasParameterOption(['--process-isolation']));
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        $exitCode = parent::doRunCommand($command, $input, $output);

       /** @var \Enlight_Event_EventManager $eventManager */
       $eventManager = $this->kernel->getContainer()->get('events');

        $eventManager->notify('Shopware_Command_After_Run', [
           'exitCode' => $exitCode,
           'command' => $command,
           'input' => $input,
           'output' => $output,
       ]);

        return $exitCode;
    }

    /**
     * @param OutputInterface $output
     */
    protected function registerCommands(OutputInterface $output)
    {
        $this->registerFilesystemCommands();
        $this->registerTaggedServiceIds();

        if (!$this->skipDatabase) {
            //Wrap database related logic in a try-catch
            //so that non-db commands can still execute
            try {
                $em = $this->kernel->getContainer()->get('models');

                // setup doctrine commands
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

    protected function registerFilesystemCommands()
    {
        if (!is_dir($dir = $this->getKernel()->getRootDir() . '/engine/Shopware/Commands')) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = 'Shopware\\Commands';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\' . strtr($relativePath, '/', '\\');
            }
            $class = $ns . '\\' . $file->getBasename('.php');

            $r = new \ReflectionClass($class);
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract() && !$r->getConstructor()->getNumberOfRequiredParameters()) {
                $this->add($r->newInstance());
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

        /** @var $command Command */
        foreach ($collection as $command) {
            if ($command instanceof Command) {
                $this->add($command);
            }
        }
    }

    /**
     * Register tagged commands in Symfony style
     *
     * @see Shopware\Components\DependencyInjection\Compiler\AddConsoleCommandPass
     */
    protected function registerTaggedServiceIds()
    {
        if ($this->kernel->getContainer()->hasParameter('console.command.ids')) {
            foreach ($this->kernel->getContainer()->getParameter('console.command.ids') as $id) {
                $this->add($this->kernel->getContainer()->get($id));
            }
        }
    }
}
