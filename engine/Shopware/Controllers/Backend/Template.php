<?php

class Shopware_Controllers_Backend_Template extends Shopware_Controllers_Backend_Application
{
    protected $model = 'Shopware\Models\Shop\Template';

    public function listAction()
    {
        $this->registerThemes();
        $this->registerTemplates();

        return parent::listAction();
    }

    protected function getList($offset, $limit, $sort = array(), $filter = array(), array $wholeParams = array())
    {
        $data = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        return $data;
    }


    public function assignAction()
    {

    }

    public function refreshThemeAction()
    {

    }

    /**
     * Assigns the passed template (identified over the primary key)
     * to the passed shop (identified over the shop primary key)
     *
     * @param $shopId
     * @param $templateId
     */
    protected function assign($shopId, $templateId)
    {

    }


    /**
     * Iterates all Shopware 5 themes which
     * stored in the /engine/Shopware/Themes directory.
     * Each theme are stored as new Shopware\Models\Shop\Template.
     */
    protected function registerThemes()
    {
    }

    /**
     * Iterates all Shopware 3-4 templates which
     * stored in the /templates/ directory.
     * Each template are stored as new Shopware\Models\Shop\Template.
     */
    protected function registerTemplates()
    {
    }

}