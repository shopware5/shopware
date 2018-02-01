## Nightwatch E2E Test Suite

### Requirements
- Node.js installed on your system

### Installation
Switch the `tests/nightwatch` directory and install the dependencies using:

```
$ npm install
```

### Usage
You can run the test using the provided NPM script:

``` 
$ npm run test
```

### Providing additional parameter
The runner provides the ability to configure it using environment variables. The following variables are available:

- `SELENIUM_HOST`
    - The selenium host
    - Default: `localhost`
- `SELENIUM_PORT`
    - The selenium port
    - Default: `4444`
- `URL`
    - Base URL of the shop
    - Default: `http://localhost`
   
#### Example usage
 
```
$ URL=http://shopware-next.local npm run test
```

#### Switching the runners configuration    
The runner can be reconfigured with launch arguments too. Here's a list of all available options:

- `--config <path-to-config>`
    - Lets you switch the nightwatch configuration
- `--env <browser-name>`
    - Lets you switch the used browser. You can provide of comma separated list of browsers.
    
#### Example usage

```
$ npm run test -- --env phantomjs
```

### Full example

```
URL=http://shopware-next.local npm run test -- --env phantomjs,chromne,chromeHeadless
```