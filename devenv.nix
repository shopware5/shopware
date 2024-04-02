{ pkgs, lib, inputs, config, ... }:

{
  imports = [ inputs.nur.nixosModules.nur ];

  languages.javascript.enable = true;
  languages.javascript.package = lib.mkDefault pkgs.nodejs-18_x;

    languages.php = {
        enable = lib.mkDefault true;
        version = lib.mkDefault "8.1";
        extensions = [ "redis" "pcov" ];

        ini = ''
          memory_limit = 2G
          realpath_cache_ttl = 3600
          session.gc_probability = 0
          ${lib.optionalString config.services.redis.enable ''
          session.save_handler = redis
          session.save_path = "tcp://127.0.0.1:6379/0"
          ''}
          display_errors = On
          error_reporting = E_ALL
          assert.active = 0
          opcache.memory_consumption = 256M
          opcache.interned_strings_buffer = 20
          zend.assertions = 0
          short_open_tag = 0
          zend.detect_unicode = 0
          realpath_cache_ttl = 3600
        '';

        fpm.pools.web = lib.mkDefault {
          settings = {
            "clear_env" = "no";
            "pm" = "dynamic";
            "pm.max_children" = 10;
            "pm.start_servers" = 2;
            "pm.min_spare_servers" = 1;
            "pm.max_spare_servers" = 10;
          };
        };
  };

  services.caddy.enable = lib.mkDefault true;

  services.caddy.virtualHosts."http://localhost:8000" = {
    extraConfig = ''
      root * .
      php_fastcgi unix/${config.languages.php.fpm.pools.web.socket} {
        index shopware.php
      }
      file_server
    '';
  };

  services.mysql = {
    enable = true;
    initialDatabases = lib.mkDefault [
        { name = "shopware"; }
    ];
    ensureUsers = lib.mkDefault [
      {
        name = "shopware";
        password = "shopware";
        ensurePermissions = {
          "shopware.*" = "ALL PRIVILEGES";
        };
      }
    ];
    settings = {
      mysqld = {
        log_bin_trust_function_creators = 1;
      };
    };
  };

  services.redis.enable = lib.mkDefault true;
  services.adminer.enable = lib.mkDefault true;
  services.adminer.listen = lib.mkDefault "127.0.0.1:9080";
  services.mailpit.enable = lib.mkDefault true;

  env.SW_HOST = lib.mkDefault "localhost:8000";
  env.DB_USER = lib.mkDefault "shopware";
  env.DB_PASSWORD = lib.mkDefault "shopware";
  env.DB_NAME = lib.mkDefault "shopware";
  env.DB_HOST = lib.mkDefault "localhost";
  env.DB_PORT = lib.mkDefault "3306";
  env.MAILER_DSN = lib.mkDefault "smtp://localhost:1025";
  env.SW_BASE_PATH = lib.mkDefault "";
  env.SELENIUM_HOST = lib.mkDefault "localhost";
  env.SMTP_HOST = lib.mkDefault "localhost";

  scripts.init-shopware.exec= ''
    make init
  '';

  scripts.check-code.exec= ''
    make check-code
  '';

  scripts.check-js-code.exec= ''
      make check-js-code
  '';

  scripts.test-jest.exec= ''
      make test-jest
  '';

  scripts.test-phpunit.exec= ''
      make test-phpunit
  '';

  scripts.info-setup.exec= ''
      php -v
      mysql -V
  '';

  scripts.test-phpunit-coverage-statistic.exec= ''
    make test-phpunit-coverage-statistic
  '';

  scripts.test-phpunit-elasticsearch.exec= ''
    make test-phpunit-elasticsearch
  '';

  scripts.prepare-mink.exec= ''
    make prepare-mink
  '';
}
