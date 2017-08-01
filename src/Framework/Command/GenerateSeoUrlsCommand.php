<?php

namespace Shopware\Framework\Command;

use Shopware\Context\TranslationContext;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSeoUrlsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('framework:seo:generate')
            ->setDescription('Generates all seo urls')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shops = $this->getContainer()->get('dbal_connection')->fetchAll(
            "SELECT id, fallback_id, `default` as is_default FROM s_core_shops"
        );

        $writer = $this->getContainer()->get('shopware.framework.routing.seo_url_writer');

        foreach ($shops as $shop) {
            $context = new TranslationContext(
                (int) $shop['id'],
                (bool) $shop['is_default'],
                $shop['fallback_id'] ? (int) $shop['fallback_id']: null
            );

            $writer->write((int) $shop['id'], $context);
        }
    }
}