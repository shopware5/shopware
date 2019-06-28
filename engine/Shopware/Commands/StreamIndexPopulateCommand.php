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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\CustomerStream\CustomerStream;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StreamIndexPopulateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'streamId') {
            /** @var ModelRepository $customerStreamRepository */
            $customerStreamRepository = $this->getContainer()->get('models')->getRepository(CustomerStream::class);
            $queryBuilder = $customerStreamRepository->createQueryBuilder('stream');

            if (is_numeric($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('stream.id', ':id'))
                    ->setParameter('id', addcslashes($context->getCurrentWord(), '%_') . '%');
            }

            $result = $queryBuilder->select(['stream.id'])
                ->getQuery()
                ->getArrayResult();

            return array_column($result, 'id');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:customer:stream:index:populate')
            ->setDescription('Refreshs all Customer Streams with the saved conditions')
            ->addOption('streamId', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $streamIds = [];
        if ($streamId = $input->getOption('streamId')) {
            $streamIds = [$streamId];
        }
        $streams = $this->getStreams($streamIds);

        foreach ($streams as $stream) {
            $output->writeln("\n## Indexing Customer Stream: " . $stream->getName() . ' ##');
            $this->container->get('shopware.api.customer_stream')->indexStream($stream);
        }
    }

    /**
     * @param array $ids
     *
     * @return CustomerStream[]|false
     */
    private function getStreams($ids = [])
    {
        $query = $this->container->get('models')->createQueryBuilder();
        $query->select(['stream']);
        $query->from(CustomerStream::class, 'stream');

        if (!empty($ids)) {
            $query->where('stream.id IN (:ids)');
            $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        }

        return $query->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);
    }
}
