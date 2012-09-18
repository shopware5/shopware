# Shopware 4

- **Version**: 4.0.2
- **Release Date**: 14th September 2012
- **License**: Dual license AGPL v3 / Proprietary
- **Github Repository**: <https://github.com/ShopwareAG/shopware-4>

## Overview

![](http://www.shopware.de/templates/0/de/media/img/sw4_home/banner_home_top.png)

Shopware 4 is the next generation of open source e-commerce software made in Germany. Based on bleeding edge technologies like `Symfony 2`, `Doctrine 2` & `Zend Framework` Shopware comes as the perfect platform for your next eCommerce project.
Furthermore Shopware 4 provides an event-driven plugin-system and an advanced hook system, which unleashes the truth power and gives you the ability to customize every part of it.

Vist the forum at <http://forum.shopware.de/>

### Shopware Server Requirements

- PHP 5.3.2 or above
- PHP's `cURL` and `GD` library
- An Apache web server
- Apache's `mod_rewrite` module
- MySQL 5.1.0 or above

### Installation via Git

Follow the instruction below if you want to install Shopware 4 using Git.

1.) Clone the git repository to the desired location using:

		git clone git@github.com:ShopwareAG/shopware-4.git

In case you wish to contribute to Shopware, fork the master tree rather than cloning it and create a pull request via Github. For further information please visit the section "Get involved" in this document.

2.) Set the correct directory permissions:

		chmod 777 config.php
		chmod 777 -R cache
		chmod 777 -R files
		chmod 777 -R media
		chmod 777 -R engine/Libary/Mpdf/tmp
		chmod 777 -R engine/Libary/Mpdf/ttfontdata
		chmod 777 -R engine/Shopware/Plugins/Community
		chmod 777 -R engine/Shopware/Proxies
		chmod 777 -R engine/Shopware/Models/Attribute
		
3.) Point your web browser at <http://yourwebsite.com/install/> and provide details for establishing a database connection, your used licence and take the basic configuration of your new store.

## Get involved

Shopware is available under dual license (AGPL v3 and proprietary license). If you want to contribute code (features or bugfixes) you have to create a pull request that considers a valid license information. You can either contribute your code under New BSD or MIT license.

If you want to contribute to the backend part of Shopware and you got in touch with `ExtJS`-based code these parts must be licensed under GPL V3, this is due to the license terms of Sencha Inc.

If you are not sure, how to contribute code under right license and right way you can contact us under <info@shopware.de>. Further you can conclude a contribution aggreement with us to get more safety around your code submits.

### Start hacking

To start contributing, just fork the master tree and clone your fork to your local machine:

		git clone git@github.com:[YOUR USERNAME]/shopware-4.git
		
After having done this, configure the remotes:

		cd shopware-4
		git remote add upstream git://github.com/ShopwareAG/shopware-4.git
		git fetch upstream
		
Now you're ready to start hacking and contributing to Shopware. If you're done hacking, filling bugs or building fancy new features push your changes to your forked repo:

		git push origin master
		
... and send us a pull request with your changes. We'll verify the pull request and merge it with the master repository.

### How to report bugs / feature requests?

We've always had a sympathetic ear for our community, so please feel free to submit tickets with bug reports or feature request. You can either use the Github issue tracker or our Jira based web-frontend:

* [Shopware Jira ticket submit form](http://jira.shopware.de/jira)
* [Github Issue tracker](https://github.com/ShopwareAG/shopware-4/issues)

# Copying / License

Shopware is distributed under a dual license (AGPL v3 and proprietary license). You can find the whole license text in the `license.txt` file.

# Changelog

The changelog and all available commits are located under <https://github.com/ShopwareAG/shopware-4/commits/master>.

## Futher reading

* [Shopware AG](http://www.shopware.de) - Homepage of shopware AG
* [Shopware Wiki](http://wiki.shopware.de) - Shopware Wiki
* [Shopware Forum](http://forum.shopware.de) - Community forum
* [Shopware Marketplace](http://store.shopware.de) - Shopware Store
* [Shopware Developer Guide](http://wiki.shopware.de/Developers-Guide_cat_487.html) - Shopware 4 Developer Guide
* [Shopware Designer Guide](http://wiki.shopware.de/Designers-Guide_cat_486.html) - Shopware 4 Designer Guide