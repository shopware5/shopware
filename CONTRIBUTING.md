# Get involved

Shopware is available under dual license (AGPL v3 and proprietary license). If you want to contribute code (features or bugfixes) you have to create a pull request that considers a valid license information. You can either contribute your code under New BSD or MIT license.

If you want to contribute to the backend part of Shopware and you got in touch with `ExtJS`-based code these parts must be licensed under GPL V3, this is due to the license terms of Sencha Inc.

If you are not sure, how to contribute code under right license and right way you can contact us under <info@shopware.de>. Further you can conclude a contribution aggreement with us to get more safety around your code submits.


# Pull Requests
When creating a pull requests you should mention

 * *why* you are changing it
 * *what* you are changing
 * if this will *break* something

Generally the pull request should be english (title as well as description).

When coding and committing, please

 * have your commit messages in english
 * have them short and descriptive
 * don't fix things which are related to other issues / pull requests
 * mention you changes in the UPGRADE.md
 * provide a test
 * follow the coding standards


# Coding standards
All contributions should follow the [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding
standards.


# Start hacking

To start contributing, just fork the repository and clone your fork to your local machine:

    git clone git@github.com:[YOUR USERNAME]/shopware.git

After having done this, configure the upstream remote:

    cd shopware
    git remote add upstream git://github.com/shopware/shopware.git
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

# Running Tests
## Database
For mosts tests a configured database connection is required.

## Running the tests
The tests are located in the `tests/Shopware/` directory
You can run the entire test suite with the following command:

    vendor/bin/phpunit -c tests/Shopware

If you want to test a single component, add its path after the phpunit command, e.g.:

    vendor/bin/phpunit -c tests/Shopware tests/Shopware/Tests/Components/Api/

