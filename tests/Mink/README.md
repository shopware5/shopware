# Shopware 5 mink testsuite

## Usage
Install shopware with dev requirements: `php composer.phar install --dev`.

Copy configuration template and adjust `base_url`:

```
cp behat.yml.dist behat.yml
```

### Run entire testsuite
```
$ ./behat --profile=responsive --tags="~javascript"
```

### Run single feature
```
$ ./behat --profile=responsive --tags="~javascript" features/search.feature
```

To append unimplemented snippets to a context
```
$ ./behat --profile=responsive --tags="~javascript" features/search.feature --append-snippets
```

### Run Javascript Tests

## PhantomJS

```
$ phantomjs --webdriver=4444
```

## Selenium

```
$ java -jar selenium-server-standalone.jar
```
