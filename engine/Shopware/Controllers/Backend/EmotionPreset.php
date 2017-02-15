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

class Shopware_Controllers_Backend_EmotionPreset extends Shopware_Controllers_Backend_ExtJs
{
    public function listAction()
    {
        $resource = $this->container->get('shopware.api.emotionpreset');

        $this->View()->assign([
            'success' => true,
            'data' => $resource->getList($this->getLocale()),
        ]);
    }

    /**
     * Model event listener function which fired when the user configure an emotion preset over the backend
     * module and clicks the save button.
     */
    public function saveAction()
    {
        $resource = $this->container->get('shopware.api.emotionpreset');

        $data = $this->Request()->getParams();

        if ($data['id']) {
            $resource->update($data['id'], $data, $this->getLocale());
        } else {
            $resource->create($data, $this->getLocale());
        }

        $this->View()->assign(['success' => true]);
    }

    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');

        $resource = $this->container->get('shopware.api.emotionpreset');

        $resource->delete($id);

        $this->View()->assign(['success' => true]);
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return $this->container->get('Auth')->getIdentity()->locale->getLocale();
    }
}
