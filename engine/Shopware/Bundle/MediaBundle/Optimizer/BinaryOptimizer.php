<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\MediaBundle\Optimizer;

use Symfony\Component\Process\ExecutableFinder;

abstract class BinaryOptimizer implements OptimizerInterface
{
    /**
     * @var bool
     */
    private $isRunnable;

    /**
     * @return string
     */
    abstract public function getCommand();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getCommand();
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public function getCommandArguments($filepath)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function run($filepath)
    {
        if (!$this->isRunnable()) {
            return;
        }

        $arguments = $this->getCommandArguments($filepath);
        $arguments = array_merge($arguments, [$filepath]);

        $suppressOutput = ' 1> /dev/null 2> /dev/null';
        $escapeShellCmd = 'escapeshellcmd';

        $isWindowsPlatform = \defined('PHP_WINDOWS_VERSION_BUILD');
        if ($isWindowsPlatform) {
            $suppressOutput = '';
            $escapeShellCmd = 'escapeshellarg';
        }

        $command = $escapeShellCmd($this->getCommand()) . ' ' . implode(' ', array_map('escapeshellarg', $arguments)) . $suppressOutput;

        exec($command, $output, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function isRunnable()
    {
        if ($this->isRunnable !== null) {
            return $this->isRunnable;
        }

        $finder = new ExecutableFinder();
        $bin = $finder->find($this->getCommand(), $this->getCommand());

        $this->isRunnable = !empty($bin) && is_executable($bin);

        return $this->isRunnable;
    }
}
