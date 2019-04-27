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

namespace Shopware\Commands;

use Enlight_Components_Cron_Job;
use Enlight_Components_Cron_Manager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'cronjob') {
            /** @var Enlight_Components_Cron_Manager $manager */
            $manager = $this->container->get('cron');

            return array_map(function (Enlight_Components_Cron_Job $job) {
                return $job->getAction();
            }, $manager->getAllJobs());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:cron:run')
            ->setDescription('Runs cronjobs.')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> runs due cronjobs.
EOF
            )
            ->addArgument(
                'cronjob',
                InputArgument::OPTIONAL,
                "If given, only run the cronjob which action matches, e.g. 'Shopware_CronJob_ClearHttpCache'"
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If given, the cronjob(s) will be run regardless of scheduling'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registerErrorHandler($output);
        $this->container->load('plugins');

        /** @var Enlight_Components_Cron_Manager $manager */
        $manager = $this->container->get('cron');

        $cronjob = $input->getArgument('cronjob');
        $force = $input->getOption('force');

        if (!empty($cronjob)) {
            try {
                $this->runSingleCronjob($output, $manager, $cronjob, $force);
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                $output->writeln('Please use the action name of a cronjob. You can see existing cronjobs in shopware backend or via <info>sw:cron:list</info> command.');

                return 1;
            }

            return 0;
        }

        $stack = [];

        while (($job = $manager->getNextJob($force)) !== null && !isset($stack[$job->getId()])) {
            $stack[$job->getId()] = true;
            $output->writeln('Processing ' . $job->getName());
            $manager->runJob($job);
        }

        return 0;
    }

    /**
     * @param string $cronjob
     * @param bool   $force
     */
    private function runSingleCronjob(OutputInterface $output, Enlight_Components_Cron_Manager $manager, $cronjob, $force)
    {
        $job = $this->getJobByActionName($manager, $cronjob);

        if (!$this->allowRun($force, $job)) {
            return;
        }

        $output->writeln('Processing ' . $job->getName());
        $manager->runJob($job);
    }

    /**
     * @param bool $force
     *
     * @return bool
     */
    private function allowRun($force, Enlight_Components_Cron_Job $job)
    {
        if ($force === true) {
            return true;
        }

        /** @var \Zend_Date $nextRun */
        $nextRun = $job->getNext();
        $nextRun = new \DateTime($nextRun->getIso());

        return $nextRun <= new \DateTime();
    }

    /**
     * Tries to resolve a string to a cronjob action name.
     * This is necessary since Shopware currently renames
     * a cronjob action after first run when it is in a
     * unknown format
     *
     * @param string $action
     *
     * @throws \RuntimeException
     *
     * @return Enlight_Components_Cron_Job
     */
    private function getJobByActionName(Enlight_Components_Cron_Manager $manager, $action)
    {
        $job = $manager->getJobByAction($action);

        if ($job !== null) {
            return $job;
        }

        if (strpos($action, 'Shopware_') !== 0) {
            $action = str_replace(' ', '', ucwords(str_replace('_', ' ', $action)));
            $action = 'Shopware_CronJob_' . $action;
            $job = $manager->getJobByAction($action);
        }

        if ($job != null) {
            return $job;
        }

        throw new \RuntimeException(sprintf('Cron not found by action name "%s".', $action));
    }
}
