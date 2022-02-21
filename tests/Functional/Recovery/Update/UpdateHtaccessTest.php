<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Recovery\Update;

require_once __DIR__ . '/../../../../recovery/update/src/UpdateHtaccess.php';

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Recovery\Update\UpdateHtaccess;

class UpdateHtaccessTest extends TestCase
{
    private const HTACCESS_V576 = <<<'EOF'
<IfModule mod_rewrite.c>
RewriteEngine on

#RewriteBase /shopware/

# Fix for office 365 autodiscover feature to prevent CSRF errors
RewriteRule ^autodiscover/autodiscover.xml$ - [F,L,NC]

# HTTPS config for the backend
#RewriteCond %{HTTPS} !=on
#RewriteRule backend/(.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

RewriteRule shopware.dll shopware.php
RewriteRule files/documents/.* engine [NC,L]
RewriteRule backend/media/(.*) media/$1 [NC,L]
RewriteRule custom/.*(config|menu|services|plugin)\.xml$ ./shopware.php?controller=Error&action=pageNotFoundError [NC,L]

RewriteCond %{REQUEST_URI} !(\/(engine|files|templates|themes|web)\/)
RewriteCond %{REQUEST_URI} !(\/media\/(archive|banner|image|music|pdf|unknown|video)\/)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ shopware.php [PT,L,QSA]

# Fix missing authorization-header on fast_cgi installations
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
</IfModule>

<IfModule mod_alias.c>
    # Restrict access to VCS directories
    RedirectMatch 404 /\\.(svn|git|hg|bzr|cvs)(/|$)

    # Restrict access to root folder files
    RedirectMatch 404 /(autoload\.php|composer\.(json|lock|phar)|README\.md|UPGRADE-(.*)\.md|CONTRIBUTING\.md|eula.*\.txt|\.gitignore|.*\.dist|\.env.*)$

    # Restrict access to shop configs files
    RedirectMatch 404 /(web\/cache\/(config_\d+\.json|all.less))$

    # Restrict access to theme configurations
    RedirectMatch 404 /themes/(.*)(.*\.lock|package\.json|\.gitignore|Gruntfile\.js|all\.less|node_modules\/.*)$
</IfModule>

# Staging environment
#SetEnvIf Host "staging.test.shopware.in" SHOPWARE_ENV=staging

# Development environment
#SetEnvIf Host "dev.shopware.in" SHOPWARE_ENV=dev
#SetEnv SHOPWARE_ENV dev

DirectoryIndex index.html
DirectoryIndex index.php
DirectoryIndex shopware.php

# Disables download of configuration
<Files ~ "\.(tpl|yml|ini)$">
    # Deny all requests from Apache 2.4+.
    <IfModule mod_authz_core.c>
          Require all denied
    </IfModule>

    # Deny all requests from Apache 2.0-2.2.
    <IfModule !mod_authz_core.c>
        Deny from all
    </IfModule>
</Files>

# Enable gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/xml text/plain text/css text/javascript application/javascript application/json application/font-woff application/font-woff2 image/svg+xml
</IfModule>

<Files ~ "\.(jpe?g|png|gif|css|js|woff|woff2|ttf|svg|webp|eot|ico)$">
    <IfModule mod_expires.c>
        ExpiresActive on
        ExpiresDefault "access plus 1 month"
    </IfModule>

    <IfModule mod_headers.c>
        Header append Cache-Control "public"
        Header unset ETag
    </IfModule>

    FileETag None
</Files>

# Match generated files like:
# 1429684458_t22_s1.css
# 1429684458_t22_s1.js
<FilesMatch "([0-9]{10})_(.+)\.(js|css)$">
    <ifModule mod_headers.c>
        Header set Cache-Control "max-age=31536000, public"
    </ifModule>

    <IfModule mod_expires.c>
        ExpiresActive on
        ExpiresDefault "access plus 1 year"
    </IfModule>
</FilesMatch>

<IfModule mod_headers.c>
    <FilesMatch "\.(?i:svg)$">
        Header set Content-Security-Policy "script-src 'none'"
    </FilesMatch>
</IfModule>

# Disables auto directory index
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

# AddType x-mapp-php7 .php
# AddHandler x-mapp-php7.php

<IfModule mod_headers.c>
    Header append X-Frame-Options SAMEORIGIN
    # Uncomment the following line to enable HSTS (https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security) and force clients to use HTTPS for at least one year (31536000 seconds)
    # Header always set Strict-Transport-Security "max-age=31536000"
</IfModule>

EOF;

    private const HTACCESS_V575_SECURITY_PLUGIN = <<<'EOF'
<IfModule mod_rewrite.c>
RewriteEngine on

#RewriteBase /shopware/

# Fix for office 365 autodiscover feature to prevent CSRF errors
RewriteRule ^autodiscover/autodiscover.xml$ - [F,L,NC]

# HTTPS config for the backend
#RewriteCond %{HTTPS} !=on
#RewriteRule backend/(.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

RewriteRule shopware.dll shopware.php
RewriteRule files/documents/.* engine [NC,L]
RewriteRule backend/media/(.*) media/$1 [NC,L]
RewriteRule custom/.*(config|menu|services|plugin)\.xml$ ./shopware.php?controller=Error&action=pageNotFoundError [NC,L]

RewriteCond %{REQUEST_URI} !(\/(engine|files|templates|themes|web)\/)
RewriteCond %{REQUEST_URI} !(\/media\/(archive|banner|image|music|pdf|unknown|video)\/)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ shopware.php [PT,L,QSA]

# Fix missing authorization-header on fast_cgi installations
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
</IfModule>

<IfModule mod_alias.c>
    # Restrict access to VCS directories
    RedirectMatch 404 /\\.(svn|git|hg|bzr|cvs)(/|$)

    # Restrict access to root folder files
    RedirectMatch 404 /(autoload\.php|composer\.(json|lock|phar)|README\.md|UPGRADE-(.*)\.md|CONTRIBUTING\.md|eula.*\.txt|\.gitignore|.*\.dist|\.env.*)$

    # Restrict access to shop configs files
    RedirectMatch 404 /(web\/cache\/(config_\d+\.json|all.less))$

    # Restrict access to theme configurations
    RedirectMatch 404 /themes/(.*)(.*\.lock|package\.json|\.gitignore|Gruntfile\.js|all\.less|node_modules\/.*)$
</IfModule>

# Staging environment
#SetEnvIf Host "staging.test.shopware.in" SHOPWARE_ENV=staging

# Development environment
#SetEnvIf Host "dev.shopware.in" SHOPWARE_ENV=dev
#SetEnv SHOPWARE_ENV dev

DirectoryIndex index.html
DirectoryIndex index.php
DirectoryIndex shopware.php

# Disables download of configuration
<Files ~ "\.(tpl|yml|ini)$">
    # Deny all requests from Apache 2.4+.
    <IfModule mod_authz_core.c>
          Require all denied
    </IfModule>

    # Deny all requests from Apache 2.0-2.2.
    <IfModule !mod_authz_core.c>
        Deny from all
    </IfModule>
</Files>

# Enable gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/xml text/plain text/css text/javascript application/javascript application/json application/font-woff application/font-woff2 image/svg+xml
</IfModule>

<Files ~ "\.(jpe?g|png|gif|css|js|woff|woff2|ttf|svg|webp|eot|ico)$">
    <IfModule mod_expires.c>
        ExpiresActive on
        ExpiresDefault "access plus 1 month"
    </IfModule>

    <IfModule mod_headers.c>
        Header append Cache-Control "public"
        Header unset ETag
    </IfModule>

    FileETag None
</Files>

# Match generated files like:
# 1429684458_t22_s1.css
# 1429684458_t22_s1.js
<FilesMatch "([0-9]{10})_(.+)\.(js|css)$">
    <ifModule mod_headers.c>
        Header set Cache-Control "max-age=31536000, public"
    </ifModule>

    <IfModule mod_expires.c>
        ExpiresActive on
        ExpiresDefault "access plus 1 year"
    </IfModule>
</FilesMatch>

# Disables auto directory index
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

# AddType x-mapp-php7 .php
# AddHandler x-mapp-php7.php

<IfModule mod_headers.c>
    Header append X-Frame-Options SAMEORIGIN
    # Uncomment the following line to enable HSTS (https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security) and force clients to use HTTPS for at least one year (31536000 seconds)
    # Header always set Strict-Transport-Security "max-age=31536000"
</IfModule>

# SW-26367 - Added by the Shopware Security Plugin. Do not delete or alter the following section delimited by comments with the issue key "SW-26367".
<IfModule mod_headers.c>
    <FilesMatch "\.(?i:svg)$">
        Header set Content-Security-Policy "script-src 'none'"
    </FilesMatch>
</IfModule>
# SW-26367
EOF;

    /**
     * @dataProvider getCombinations
     */
    public function testCombination(string $currentHtaccess, ?string $newHtaccess, string $expected): void
    {
        $tmpDir = implode(
            DIRECTORY_SEPARATOR,
            [sys_get_temp_dir(), uniqid(__METHOD__, true)]
        );

        mkdir($tmpDir);

        file_put_contents($tmpDir . '/.htaccess', $currentHtaccess);

        if ($newHtaccess) {
            file_put_contents($tmpDir . '/.htaccess.dist', $newHtaccess);
        }

        $updater = new UpdateHtaccess($tmpDir . '/.htaccess');
        $updater->update();

        static::assertSame(
            $expected,
            file_get_contents($tmpDir . '/.htaccess'),
            sprintf('File hash is: %s', md5($currentHtaccess))
        );
    }

    /**
     * @return Generator<array>
     */
    public function getCombinations(): Generator
    {
        yield 'Dist file missing' => [
            'Test',
            null,
            'Test',
        ];

        yield 'User has removed marker' => [
            'Test',
            '# BEGIN Shopware
Test
# END Shopware',
            'Test',
        ];

        yield 'Update marker' => [
            '# BEGIN Shopware
OLD
# END Shopware',
            '# BEGIN Shopware
NEW
# END Shopware',
            '# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
NEW
# END Shopware',
        ];

        yield 'Update marker with pre and after lines' => [
            'BEFORE
# BEGIN Shopware
OLD
# END Shopware
AFTER',
            '# BEGIN Shopware
NEW
# END Shopware',
            'BEFORE
# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
NEW
# END Shopware
AFTER',
        ];

        yield 'Update help text' => [
            'BEFORE
# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
OLD
# END Shopware
AFTER',
            '# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
NEW
# END Shopware',
            'BEFORE
# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
NEW
# END Shopware
AFTER',
        ];

        yield 'Update marker with pre and after lines containing multi-byte symbols' => [
            'BEFORE 😱
# BEGIN Shopware
OLD
# END Shopware
😮‍💨',
            '# BEGIN Shopware
NEW
# END Shopware',
            'BEFORE 😱
# BEGIN Shopware
# The directives (lines) between "# BEGIN Shopware" and "# END Shopware" are dynamically generated. Any changes to the directives between these markers will be overwritten.
NEW
# END Shopware
😮‍💨',
        ];

        yield 'Known previous version' => [
            self::HTACCESS_V576,
            '# BEGIN Shopware
NEW
# END Shopware',
            '# BEGIN Shopware
NEW
# END Shopware',
        ];

        yield 'Known previous version modified by the SwagSecurity plugin' => [
            self::HTACCESS_V575_SECURITY_PLUGIN,
            '# BEGIN Shopware
NEW
# END Shopware',
            '# BEGIN Shopware
NEW
# END Shopware',
        ];
    }
}
