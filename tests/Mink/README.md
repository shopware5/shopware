# Shopware 4 mink testsuite

## Usage
Install shopware with dev requirenments: `php composer.phar install --dev`.

Copy configuration template and adjust `base_url`:

```
cp behat.yml.dist behat.yml
```

### Run entire testsuite
```
$ ./behat
```

### Run single feature
```
$ ./behat features/search.feature
```

To append unimplemented snippets to a context
```
$ ./behat features/search.feature --append-snippets
```
