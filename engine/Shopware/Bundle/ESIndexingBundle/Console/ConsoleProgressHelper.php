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

namespace Shopware\Bundle\ESIndexingBundle\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleProgressHelper implements ProgressHelperInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progress;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $current;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function start($count, $label = '')
    {
        $this->count = $count;
        $this->current = 0;
        if ($label) {
            $this->output->writeln($label);
        }
        $this->progress = new ProgressBar($this->output, $count);
        $this->progress->setFormat('very_verbose');
    }

    /**
     * {@inheritdoc}
     */
    public function advance($step = 1)
    {
        if ($this->current + $step > $this->count) {
            $step = $this->count - $this->current;
        }
        $this->progress->advance($step);
        $this->current += $step;
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        $this->progress->finish();
        $this->output->writeln('');
    }
}
