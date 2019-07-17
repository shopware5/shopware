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

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\WebLink\EventListener\AddLinkHeaderListener;

trait ControllerTrait
{
    use ContainerAwareTrait;

    /**
     * Returns true if the service id is defined.
     *
     * @final
     */
    protected function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * Gets a container service by its id.
     *
     * @return object The service
     *
     * @final
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @final
     */
    protected function generateUrl(array $parameters = []): string
    {
        return $this->container->get('router')->assemble($parameters);
    }

    /**
     * Forwards the request to another controller.
     *
     *
     * @final
     */
    protected function forward(array $parameters = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $subRequest = $request->duplicate($parameters, [], []);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @final
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @final
     */
    protected function redirectToRoute(array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($parameters), $status);
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     *
     * @param \SplFileInfo|string $file File object or path to file to be sent as response
     *
     * @final
     */
    protected function file($file, string $fileName = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition($disposition, $fileName === null ? $response->getFile()->getFilename() : $fileName);

        return $response;
    }

    /**
     * Returns a rendered view.
     *
     * @final
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        /** @var \Enlight_Template_Manager $template */
        $template = $this->container->get('template');
        $template = $template->createTemplate($view);
        $template->assign($parameters);

        return $template->fetch($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @final
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $content = $this->renderView($view, $parameters);

        if ($response === null) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @final
     */
    protected function createNotFoundException(string $message = 'Not Found', \Exception $previous = null): NotFoundHttpException
    {
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @final
     */
    protected function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance.
     *
     * @final
     */
    protected function createFormBuilder($data = null, array $options = []): FormBuilderInterface
    {
        return $this->container->get('form.factory')->createBuilder(FormType::class, $data, $options);
    }

    /**
     * Adds a Link HTTP header to the current response.
     *
     * @see https://tools.ietf.org/html/rfc5988
     *
     * @final
     */
    protected function addLink(Request $request, Link $link)
    {
        if (!class_exists(AddLinkHeaderListener::class)) {
            throw new \LogicException('You can not use the "addLink" method if the WebLink component is not available. Try running "composer require symfony/web-link".');
        }

        if (null === $linkProvider = $request->attributes->get('_links')) {
            $request->attributes->set('_links', new GenericLinkProvider([$link]));

            return;
        }

        $request->attributes->set('_links', $linkProvider->withLink($link));
    }
}
