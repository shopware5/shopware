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
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function registerErrorHandler(OutputInterface $output)
    {
        error_reporting(-1);

        $errorNameMap = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_ALL => 'E_ALL',
        ];

        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($output, $errorNameMap) {
            if ($errno === E_RECOVERABLE_ERROR) {
                return true;
            }

            // Ignore suppressed errors/warnings
            if (error_reporting() === 0) {
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

    /**
     * @param int|string $input
     *
     * @return int[]
     */
    protected function completeShopIds($input)
    {
        return array_map('intval', $this->completeInputByQueryingProperty($input, Shop::class, 'id'));
    }

    /**
     * @param int|string $input
     *
     * @return string[]
     */
    protected function completeInstalledLocaleKeys($input)
    {
        return $this->completeInputByQueryingProperty($input, Locale::class, 'locale');
    }

    /**
     * @param int|string $input
     *
     * @return string[]
     */
    protected function queryPluginNames($input)
    {
        return $this->completeInputByQueryingProperty($input, Plugin::class, 'name', function (QueryBuilder $queryBuilder, $modelAlias) {
            return $queryBuilder->andWhere($queryBuilder->expr()->eq("$modelAlias.capabilityEnable", 'true'));
        });
    }

    /**
     * @param string           $input
     * @param string           $modelClass
     * @param string           $property
     * @param array|mixed|null $conditionCallback
     *
     * @return array
     */
    protected function completeInputByQueryingProperty($input, $modelClass, $property, $conditionCallback = null)
    {
        $likePattern = addcslashes($input, '%_') . '%';
        $checkForPrefix = function (QueryBuilder $queryBuilder, $alias) use ($likePattern, $property, $conditionCallback) {
            $parameterAlias = uniqid("param$property");
            $queryBuilder = $queryBuilder->andWhere($queryBuilder->expr()->like("$alias.$property", ":$parameterAlias"))
                ->setParameter($parameterAlias, $likePattern);

            return is_callable($conditionCallback) ? call_user_func($conditionCallback, $queryBuilder, $alias) : $queryBuilder;
        };

        return $this->queryProperty($modelClass, $property, $checkForPrefix);
    }

    /**
     * @param string           $modelClass
     * @param string           $property
     * @param array|mixed|null $conditionCallback
     *
     * @return array
     */
    protected function queryProperty($modelClass, $property, $conditionCallback = null)
    {
        $alias = uniqid('modelAlias');

        /* @var ModelManager $em */
        try {
            $em = $this->getContainer()->get('models');
        } catch (\Exception $e) {
            return [];
        }

        /** @var ModelRepository $repository */
        $repository = $em->getRepository($modelClass);
        $queryBuilder = $repository->createQueryBuilder($alias);

        if (is_callable($conditionCallback)) {
            $queryBuilder = call_user_func($conditionCallback, $queryBuilder, $alias);
        }

        $result = $queryBuilder->select(["$alias.$property"])
            ->addOrderBy($queryBuilder->expr()->asc("$alias.$property"))
            ->getQuery()
            ->getArrayResult();

        return array_column($result, $property);
    }

    /**
     * @param string $directory
     *
     * @return array
     */
    protected function completeDirectoriesInDirectory($directory = null)
    {
        // ls -1d -- */
        // TODO set path for shell completion. Hint: the exit code gets checked in the generated completion bash script
        exit(ShellPathCompletion::PATH_COMPLETION_EXIT_CODE/* + 2 */);
    }

    /**
     * @param string $directory
     *
     * @return array
     */
    protected function completeInDirectory($directory = null)
    {
        // https://unix.stackexchange.com/a/34277
        // TODO set path for shell completion. Hint: the exit code gets checked in the generated completion bash script
        exit(ShellPathCompletion::PATH_COMPLETION_EXIT_CODE/* + 1 */);
    }
}
