# Shopware 5

![Build Status](https://github.com/shopware5/shopware/workflows/PHPUnit/badge.svg)
[![Crowdin](https://d322cqt584bo4o.cloudfront.net/shopware/localized.svg)](https://crowdin.com/project/shopware)
[![Latest Stable Version](https://poser.pugx.org/shopware/shopware/v/stable)](https://packagist.org/packages/shopware/shopware)
[![Total Downloads](https://poser.pugx.org/shopware/shopware/downloads)](https://packagist.org/packages/shopware/shopware)
[![Slack](https://img.shields.io/badge/chat-on%20slack-%23ECB22E)](http://slack.shopware.com?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

- **License**: GNU General Public License v3 (some used parts have different licenses, which can be found in the respective files or directories)
- **GitHub Repository**: <https://github.com/shopware5/shopware>
- **Issues**: <https://github.com/shopware5/shopware/issues>

## Overview

![Shopware 5 collage](https://assets.shopware.com/media/github/shopware5_readme.png)

Shopware 5 is an open source e-commerce software made in Germany.
Based on technologies like `Symfony 4`, `Doctrine 2` & `Zend Framework` Shopware comes as the perfect platform for your next e-commerce project.
Furthermore, Shopware 5 provides an event-driven plugin system and an advanced hook system, giving you the ability to customize every part of the platform.

Visit the forum at <https://forum.shopware.com/>

----

### Shopware Server Requirements

- PHP 7.4.0 or above
- [Apache 2.2 or 2.4](https://httpd.apache.org/)
- Apache's `mod_rewrite` module
- MySQL 5.7.0 or above

#### Required PHP extensions:

-   <a href="https://php.net/manual/en/book.ctype.php" target="_blank">ctype</a>
-   <a href="https://php.net/manual/en/book.curl.php" target="_blank">curl</a>
-   <a href="https://php.net/manual/en/book.dom.php" target="_blank">dom</a>
-   <a href="https://php.net/manual/en/book.filter.php" target="_blank">filter</a>
-   <a href="https://php.net/manual/en/book.hash.php" target="_blank">hash</a>
-   <a href="https://php.net/manual/en/book.iconv.php" target="_blank">iconv</a>
-   <a href="https://php.net/manual/en/book.image.php" target="_blank">gd</a> (with freetype and libjpeg)
-   <a href="https://php.net/manual/en/book.json.php" target="_blank">json</a>
-   <a href="https://php.net/manual/en/book.mbstring.php" target="_blank">mbstring</a>
-   <a href="https://php.net/manual/en/book.openssl.php" target="_blank">OpenSSL</a>
-   <a href="https://php.net/manual/en/book.session.php" target="_blank">session</a>
-   <a href="https://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a>
-   <a href="https://php.net/manual/en/book.xml.php" target="_blank">xml</a>
-   <a href="https://php.net/manual/en/book.zip.php" target="_blank">zip</a>
-   <a href="https://php.net/manual/en/book.zlib.php" target="_blank">zlib</a>
-   <a href="https://php.net/manual/en/ref.pdo-mysql.php" target="_blank">PDO/MySQL</a>
-   <a href="https://php.net/manual/de/book.fileinfo.php" target="_blank">fileinfo</a>

### Installation via Git

Follow the instruction below if you want to install Shopware 5 using Git.

1.) Clone the git repository to the desired location using:

    git clone https://github.com/shopware5/shopware.git

In case you wish to contribute to Shopware, fork the `5.7` branch rather than cloning it, and create a pull request via GitHub.
For further information please read the section "Get involved" of this document.

2.) Set the correct directory permissions:

    chmod -R 755 custom/plugins
    chmod -R 755 engine/Shopware/Plugins/Community
    chmod -R 755 files
    chmod -R 755 media
    chmod -R 755 var
    chmod -R 755 web

Depending on your server configuration, it might be necessary to set whole write permissions (777) to the files and folders above.
You can also start testing with lower permissions due to security reasons (644 for example) as long as your PHP process can write to those files.

3.) A [Makefile](https://www.gnu.org/software/make/manual/make.html) may be used to set up the configuration and database connection:

* Copy `.env.dist` to `.env` and modify variables if needed
* ``make init``

**Info regarding platform inter-compatibility**

The `Makefile` is intended to work with Linux and Mac systems alike which means that we're not able to use all features of modern GNU make.
Some workarounds are in place because of this and place constraints on the functionality of this way to set up Shopware
(there might be issues when using special characters inside the variables of the `.env` file).
**The `Makefile` is therefore only to be used for testing and development setups** at the moment.

4.) Download the test images and extract them:

Go to the root directory of your shopware system and download the test images:

    wget -O test_images.zip http://releases.shopware.com/test_images_since_5.1.zip

For older Shopware versions < 5.1

    wget -O test_images.zip http://releases.shopware.com/test_images.zip

Unzip the files inside the root directory:

    unzip test_images.zip

You can now access your shop

# Backend

The backend is located at `/backend` example `http://your.shop.com/backend`.
Backend Login: demo/demo

The test_images.zip file includes thumbnails for the new responsive theme and the old deprecated template.

If you want to have full-featured demo data, you should download the respective demo data plugin in the First Run Wizard or in the Plugin Manager.

# Frontend users in demo data

* Customer: test@example.com / shopware
* B2B: mustermann@b2b.de / mustermann

# Get involved

Shopware is available under dual license (AGPL v3 and proprietary license).
If you want to contribute code (features or bugfixes), you have to create a pull request and include valid license information.
You can either contribute your code under New BSD or MIT license.

If you want to contribute to the backend part of Shopware, and your changes affect or are based on ExtJS code, they must be licensed under GPL V3, as per license requirements from Sencha Inc.

If you are not sure which license to use, or want more details about available licensing or the contribution agreements we offer, you can contact us at <contact@shopware.com>.

For more information about contributing to Shopware, please see [CONTRIBUTING.md](CONTRIBUTING.md).


### How to report bugs / request features?

We've always had a sympathetic ear for our community, so please feel free to submit tickets with bug reports or feature requests.
In order to have a single issue tracking tool, we've decided to close the GitHub issue panel in favor of our Jira issue tracker, which is directly connected to our development division.

* [Shopware ticket submit form](https://issues.shopware.com/createissue)

# Copying / License

Shopware is distributed under a dual license (AGPL v3 and proprietary license). You can find the whole license text in the `license.txt` file.

# Changelog

The changelog and all available commits are located under <https://github.com/shopware5/shopware>.

## Further reading

* [Shopware AG](https://www.shopware.com) - Homepage of shopware AG
* [Shopware Developer Documentation](https://developers.shopware.com/)
* [Shopware Docs](https://docs.shopware.com/) - Shopware documentation
* [Shopware Forum](https://forum.shopware.com) - Community forum
* [Shopware Marketplace](https://store.shopware.com) - Shopware Store
* [Shopware on Crowdin](https://crowdin.com/project/shopware) - Crowdin (Translations)
