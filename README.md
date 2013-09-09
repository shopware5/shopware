# Shopware 4

- **License**: Dual license AGPL v3 / Proprietary
- **Github Repository**: <https://github.com/ShopwareAG/shopware-4>
- **Issue-Tracker**: <http://jira.shopware.de/jira>

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

    git clone https://github.com/ShopwareAG/shopware-4.git

In case you wish to contribute to Shopware, fork the master tree rather than cloning it and create a pull request via Github. For further information please visit the section "Get involved" in this document.

2.) Set the correct directory permissions:

    chmod 755 config.php
    chmod 755 -R cache
    chmod 755 -R files
    chmod 755 -R media
    chmod 755 -R engine/Library/Mpdf/tmp
    chmod 755 -R engine/Library/Mpdf/ttfontdata
    chmod 755 -R engine/Shopware/Plugins/Community


Depending on your server configuration it might be neccesarry to set whole write permissions (777) to the files and folders above.
Also you can start testing with lower permissions due to security reasons (644 for example), if your php-process can write to
those files.

3.) An [Ant](http://ant.apache.org/) Buildfile is used to set up the configuration and database connection:

    cd build/
    ant -Ddb.user=youruser -Ddb.password=yourpassword -Ddb.name=shopware build-database build-config

4.) Download the demo data files and extract them:

Go to the checkout directory and download the demo data files:

	wget -O demo.zip files.shopware.de/download.php?package=demo

Unzip the files to the checkout directory:

	unzip demo.zip

You can now access your shop

Backend Login: demo/demo

## Get involved

Shopware is available under dual license (AGPL v3 and proprietary license). If you want to contribute code (features or bugfixes) you have to create a pull request that considers a valid license information. You can either contribute your code under New BSD or MIT license.

If you want to contribute to the backend part of Shopware and you got in touch with `ExtJS`-based code these parts must be licensed under GPL V3, this is due to the license terms of Sencha Inc.

If you are not sure, how to contribute code under right license and right way you can contact us under <info@shopware.de>. Further you can conclude a contribution aggreement with us to get more safety around your code submits.

### Start hacking

To start contributing, just fork the repository and clone your fork to your local machine:

    git clone git@github.com:[YOUR USERNAME]/shopware-4.git

After having done this, configure the upstream remote:

    cd shopware-4
    git remote add upstream git://github.com/ShopwareAG/shopware-4.git
    git config branch.master.remote upstream

To keep your master up-to-date:

    git checkout master
    git pull --rebase
    php build/ApplyDeltas.php

Checkout a new topic-branch and you're ready to start hacking and contributing to Shopware:

    git checkout -b feature/your-cool-feature

If you're done hacking, filling bugs or building fancy new features push your changes to your forked repo:

    git push origin feature/your-cool-feature


... and send us a pull request with your changes. We'll verify the pull request and merge it with the `master` Branch.

### Running Tests
#### Database
For mosts tests a configured database connection is required.

#### PHPUnit
To run the Shopware 4 test suite, install PHPUnit 3.6 or later first:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit
    pear install phpunit/DbUnit

#### Running the tests
The tests are located in the `tests/Shopware/` directory

    cd tests/Shopware/

You can run the entire test suite with the following command:

    phpunit

If you want to test a single component, add its path after the phpunit command, e.g.:

    phpunit Tests/Components/Api/


### Coding standards
All contributions should follow the [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding
standards.


## How to report bugs / feature requests?

We've always had a sympathetic ear for our community, so please feel free to submit tickets with bug reports or feature requests. In order to have one place to go, we've decided to close the GitHub issue tracker in favor of our Jira issue tracker, which is directly connected to our development division.

* [Shopware Jira ticket submit form](http://jira.shopware.de/jira)

# Copying / License

Shopware is distributed under a dual license (AGPL v3 and proprietary license). You can find the whole license text in the `license.txt` file.

# Changelog

The changelog and all available commits are located under <https://github.com/ShopwareAG/shopware-4/commits/master>.

## Further reading

* [Shopware AG](http://www.shopware.de) - Homepage of shopware AG
* [Shopware Wiki](http://wiki.shopware.de) - Shopware Wiki
* [Shopware Forum](http://forum.shopware.de) - Community forum
* [Shopware Marketplace](http://store.shopware.de) - Shopware Store
* [Shopware Developer Guide](http://wiki.shopware.de/Developers-Guide_cat_487.html) - Shopware 4 Developer Guide
* [Shopware Designer Guide](http://wiki.shopware.de/Designers-Guide_cat_486.html) - Shopware 4 Designer Guide
