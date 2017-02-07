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

namespace Shopware\Bundle\FormBundle;

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;

class EnlightRequestHandler implements RequestHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(FormInterface $form, $request = null)
    {
        if (!$request instanceof \Enlight_Controller_Request_Request) {
            throw new UnexpectedTypeException($request, 'Enlight_Controller_Request_Request');
        }

        $name = $form->getName();
        $method = $form->getConfig()->getMethod();
        $default = $form->getConfig()->getCompound() ? [] : null;

        if ($method !== $request->getMethod()) {
            return;
        }

        $data = $this->getDataByRequest($name, $method, $request, $default);

        if ($name === '' && $this->hasFieldsSet($form, $data) === false) {
            return;
        }

        $form->submit($data, 'PATCH' !== $method);
    }

    /**
     * remove shopware GET parameters from the request
     *
     * @param array $data
     *
     * @return array
     */
    private function filterDefaultParameters(array $data)
    {
        unset($data['action'], $data['module'], $data['controller']);

        return $data;
    }

    /**
     * @param string                              $name
     * @param string                              $method
     * @param \Enlight_Controller_Request_Request $request
     * @param array|null                          $defaultValue
     *
     * @return array|bool
     */
    private function getDataByRequest($name, $method, \Enlight_Controller_Request_Request $request, $defaultValue)
    {
        // For request methods that must not have a request body we fetch data
        // from the query string. Otherwise we look for data in the request body.
        if ('GET' === $method || 'HEAD' === $method || 'TRACE' === $method) {
            $data = $this->handleRequestWithoutBody($request, $name);
        } else {
            $data = $this->handleRequestWithBody($request, $name, $defaultValue);
        }

        $data = $this->filterDefaultParameters($data);

        return $data;
    }

    /**
     * Checks if the form has at least one field present
     *
     * @param FormInterface $form
     * @param array         $data
     *
     * @return bool
     */
    private function hasFieldsSet(FormInterface $form, array $data)
    {
        return count(array_intersect_key($data, $form->all())) > 0;
    }

    /**
     * Gather data from request query strings based on the form name
     *
     * @param \Enlight_Controller_Request_Request $request
     * @param string                              $name
     *
     * @return bool|array
     */
    private function handleRequestWithoutBody(\Enlight_Controller_Request_Request $request, $name)
    {
        if ('' === $name) {
            $data = $request->getQuery();
        } else {
            // Don't submit GET requests if the form's name does not exist
            // in the request
            if (!$request->getQuery($name)) {
                return [];
            }

            $data = $request->getQuery($name);
        }

        return $data;
    }

    /**
     * Gather data from request body based on the form name
     *
     * @param \Enlight_Controller_Request_Request $request
     * @param string                              $name
     * @param array|null                          $defaultValue
     *
     * @return array|bool
     */
    private function handleRequestWithBody(\Enlight_Controller_Request_Request $request, $name, $defaultValue)
    {
        $params = [];

        if ('' === $name) {
            $params = $request->getParams();
        } elseif ($request->getParam($name)) {
            $params = $request->getParam($name, $defaultValue);
        }

        return $params;
    }
}
