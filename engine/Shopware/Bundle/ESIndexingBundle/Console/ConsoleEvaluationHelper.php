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

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleEvaluationHelper implements EvaluationHelperInterface
{
    /**
     * @var bool
     */
    private $active;

    /**
     * @var bool
     */
    private $stopOnError;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var int
     */
    private $error = 0;

    /**
     * @var int
     */
    private $success = 0;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    public function setStopOnError($stopOnError)
    {
        $this->stopOnError = $stopOnError;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStopOnError()
    {
        return $this->stopOnError;
    }

    /**
     * {@inheritdoc}
     */
    public function addResult(array $data)
    {
        if (!$this->getActive() && !$this->getStopOnError()) {
            return;
        }

        foreach ($data['items'] as $item) {
            if (!isset($item['index'])) {
                continue;
            }

            $this->handleItem($item['index']);
        }
    }

    public function finish()
    {
        if (!$this->getActive() || !$this->output instanceof OutputInterface) {
            return;
        }

        if ($this->total == 0) {
            $this->output->writeln('No items indexed');
        }

        $this->output->writeln("\n Evaluation:");
        $this->output->writeln('  Total: ' . $this->total . ' items');
        $this->output->writeln('  Error: ' . $this->error . ' items');
        $this->output->writeln('  Success: ' . $this->success . ' items');
        $this->output->writeln("\n");

        $this->reset();
    }

    /**
     * @throws \Exception
     */
    private function handleItem(array $item)
    {
        switch ($item['status']) {
            case 201:
                ++$this->success;
                break;
            case 400:
                if ($this->getStopOnError()) {
                    $this->abort($item);
                }
                ++$this->error;
                break;
            default:
                break;
        }

        ++$this->total;
    }

    private function reset()
    {
        $this->total = 0;
        $this->error = 0;
        $this->success = 0;
    }

    /**
     * @throws \Exception
     */
    private function abort(array $item)
    {
        if (isset($item['error'])) {
            throw new \Exception("An error occured:\n" . $item['_id'] . ': ' . $item['error']['reason']);
        }

        throw new \Exception('No error reason found. Please check the backend ES system logs for further details.');
    }
}
