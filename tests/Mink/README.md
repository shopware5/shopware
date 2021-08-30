# Shopware 5 mink testsuite

## Usage
Install shopware with dev requirements: `php composer.phar install --dev`.

Copy configuration template and adjust `base_url`:

```shell
cp behat.yml.dist behat.yml
```

Disable frontend CSRF protection in your config.php

```php
'csrfProtection' => [
    'frontend' => false,
    'backend' => true
],
```

### Run entire testsuite

Make sure Selenium is up and running.

```shell
./behat
```

### Run single feature

```shell
./behat features/search.feature
```

To append unimplemented snippets to a context

```shell
./behat features/search.feature --append-snippets
```

## Selenium

Note that you need a Java SDK installation on your system.

### Download

```shell
wget http://selenium-release.storage.googleapis.com/3.10/selenium-server-standalone-3.10.0.jar
```

### Install chromedriver

Check the version of your Chrome first.
You might need to adjust the version.
Open https://chromedriver.storage.googleapis.com to see available versions.

```shell
sudo apt install -y unzip xvfb libxi6 libgconf-2-4
wget https://chromedriver.storage.googleapis.com/92.0.4515.107/chromedriver_linux64.zip
unzip chromedriver_linux64.zip
sudo mv chromedriver /usr/bin/chromedriver
sudo chown root:root /usr/bin/chromedriver
sudo chmod +x /usr/bin/chromedriver
```

### Start Selenium

Opens the mink execution in a browser, so you can see what happens.
```shell
java -jar selenium-server-standalone-3.10.0.jar
```

The tests are done in the shell and you see only the Mink output.
```shell
xvfb-run java -Dwebdriver.chrome.driver=/usr/bin/chromedriver -jar selenium-server-standalone-3.10.0.jar
```
