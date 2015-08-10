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

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
abstract class ShopwareCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param OutputInterface $output
     */
    public function registerErrorHandler(OutputInterface $output)
    {
        error_reporting(-1);

        $errorNameMap = array(
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_ALL               => 'E_ALL',
        );

        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($output, $errorNameMap) {

            if ($errno === E_RECOVERABLE_ERROR) {
                return true;
            }

            if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                $errorName = isset($errorNameMap[$errno]) ? $errorNameMap[$errno] : $errno;

                $message = sprintf("Error: %s, \nFile: %s\nLine: %s, Message:\n%s\n", $errorName, $errfile, $errline, $errstr);
                $output->writeln('<comment>' . $message . '</comment>');

                $output->writeln('<comment>Error stack:</comment>');
                $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                foreach ($stack as $trace) {
                    $output->writeln(sprintf(' %s%s%s() at <info>%s:%s</info>', isset($trace['class']) ? $trace['class'] : '', isset($trace['type']) ? $trace['type'] : '', isset($trace['function']) ? $trace['function'] : '', isset($trace['file']) ? $trace['file'] : '', isset($trace['line']) ? $trace['line'] : ''));
                }
                $output->writeln('');
                $output->writeln('');
            }

            // do not trigger internal
            return true;
        });
    }
}
