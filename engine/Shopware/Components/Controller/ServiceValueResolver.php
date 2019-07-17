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

namespace Shopware\Components\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver as InnerService;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ServiceValueResolver implements ArgumentValueResolverInterface
{
    private $container;
    private $innerService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->innerService = new InnerService($container);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if ($this->innerService->supports($request, $argument)) {
            return true;
        }

        $controller = $request->attributes->get('_controller');

        if (\is_array($controller) && \is_callable($controller, true) && \is_string($controller[0])) {
            $controller = $controller[0] . ':' . $controller[1];
        } elseif (!\is_string($controller) || $controller === '') {
            return false;
        }

        if (strpos($controller, ':') === false) {
            $controller .= ':__invoke';
        }

        return $this->container->has($controller) && $this->container->get($controller)->has($argument->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($this->innerService->supports($request, $argument)) {
            yield from $this->innerService->resolve($request, $argument);

            return;
        }

        if (\is_array($controller = $request->attributes->get('_controller'))) {
            $controller = $controller[0] . ':' . $controller[1];
        }

        if (strpos($controller, ':') === false) {
            $controller .= ':__invoke';
        }

        yield $this->container->get($controller)->get($argument->getName());
    }
}
