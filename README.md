# Shopware 5

[![Build Status](https://travis-ci.org/shopware/shopware.svg?branch=master)](https://travis-ci.org/shopware/shopware)

- **License**: Dual license AGPL v3 / Proprietary
- **Github Repository**: <https://github.com/shopware/shopware>
- **Issue-Tracker**: <http://jira.shopware.de/jira>

## Overview

![Shopware 5 collage](http://cdn.shopware.de/github/readme_screenshot.png)

Shopware 5 is the next generation of open source e-commerce software made in Germany. Based on bleeding edge technologies like `Symfony 2`, `Doctrine 2` & `Zend Framework` Shopware comes as the perfect platform for your next eCommerce project.
Furthermore Shopware 5 provides an event-driven plugin-system and an advanced hook system, which unleashes the truth power and gives you the ability to customize every part of it.

Visit the forum at <http://forum.shopware.com/>

### Shopware Server Requirements

- PHP 5.4.0 or above
- PHP's `cURL` and `GD` library
- An Apache web server
- Apache's `mod_rewrite` module
- MySQL 5.5.0 or above

### Installation via Git

Follow the instruction below if you want to install Shopware 5 using Git.

1.) Clone the git repository to the desired location using:

    git clone https://github.com/shopware/shopware.git

In case you wish to contribute to Shopware, fork the master tree rather than cloning it and create a pull request via Github. For further information please visit the section "Get involved" in this document.

2.) Set the correct directory permissions:

    chmod 755 config.php
    chmod -R 755 logs
    chmod -R 755 cache
    chmod -R 755 files
    chmod -R 755 media
    chmod -R 755 engine/Shopware/Plugins/Community


Depending on your server configuration it might be neccesarry to set whole write permissions (777) to the files and folders above.
Also you can start testing with lower permissions due to security reasons (644 for example), if your php-process can write to
those files.

3.) An [Ant](http://ant.apache.org/) Buildfile is used to set up the configuration and database connection:

    cd build/
    ant configure
    ant build-unit

4.) Download the demo data files and extract them:

Go to the checkout directory and download the demo data files:

	wget -O demo.zip http://releases.s3.shopware.com/demo_4.2.0.zip

Unzip the files to the checkout directory:

	unzip demo.zip

You can now access your shop

Backend Login: demo/demo

# Get involved

Shopware is available under dual license (AGPL v3 and proprietary license). If you want to contribute code (features or bugfixes) you have to create a pull request that considers a valid license information. You can either contribute your code under New BSD or MIT license.

If you want to contribute to the backend part of Shopware and you got in touch with `ExtJS`-based code these parts must be licensed under GPL V3, this is due to the license terms of Sencha Inc.

If you are not sure, how to contribute code under right license and right way you can contact us under <info@shopware.de>. Further you can conclude a contribution agreement with us to get more safety around your code submits.

If you whish to contribute to shopware, please see [CONTRIBUTING.md](CONTRIBUTING.md).


### How to report bugs / feature requests?

We've always had a sympathetic ear for our community, so please feel free to submit tickets with bug reports or feature requests. In order to have one place to go, we've decided to close the GitHub issue tracker in favor of our Jira issue tracker, which is directly connected to our development division.

* [Shopware Jira ticket submit form](http://jira.shopware.de/jira)

# Copying / License

Shopware is distributed under a dual license (AGPL v3 and proprietary license). You can find the whole license text in the `license.txt` file.

# Changelog

The changelog and all available commits are located under <https://github.com/shopware/shopware/commits/master>.

## Further reading

* [Shopware AG](http://www.shopware.com) - Homepage of shopware AG
* [Shopware Developer Documentation](https://devdocs.shopware.com/)
* [Shopware Wiki](http://wiki.shopware.com) - Shopware Wiki
* [Shopware Forum](http://forum.shopware.com) - Community forum
* [Shopware Marketplace](http://store.shopware.com) - Shopware Store
