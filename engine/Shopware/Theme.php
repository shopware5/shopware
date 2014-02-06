<?php

namespace Shopware;

class Theme
{
    protected $extend = null;

    protected $name = '';

    protected $description = null;

    protected $author = null;

    protected $license = null;

    /**
     * Don't override this function. Used
     * from the backend template module
     * to get the template hierarchy
     * @return null|string
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Helper function which returns the theme
     * directory name
     *
     * @return mixed
     */
    public function getTemplate()
    {
        $class = get_class($this);
        $paths = explode("\\", $class);
        return $paths[count($paths) - 2];
    }


}