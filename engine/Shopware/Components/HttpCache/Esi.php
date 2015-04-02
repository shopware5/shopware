<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Esi as BaseEsi;

/**
 * Esi implements the ESI capabilities to Request and Response instances.
 *
 * For more information, read the following W3C notes:
 *
 *  * ESI Language Specification 1.0 (http://www.w3.org/TR/esi-lang)
 *
 *  * Edge Architecture Specification (http://www.w3.org/TR/edge-arch)
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Esi extends BaseEsi
{
    private $contentTypes;
    private $phpEscapeMap = array(
        array('<?', '<%', '<s', '<S'),
        array('<?php echo "<?"; ?>', '<?php echo "<%"; ?>', '<?php echo "<s"; ?>', '<?php echo "<S"; ?>'),
    );

    /**
     * {@inheritdoc}
     */
    public function __construct(array $contentTypes = array('text/html', 'text/xml', 'application/xhtml+xml', 'application/xml'))
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * Backported from https://github.com/symfony/symfony/blob/v2.6.6/src/Symfony/Component/HttpKernel/HttpCache/Esi.php
     * See: http://symfony.com/blog/cve-2015-2308-esi-code-injection
     *
     * {@inheritdoc}
     */
    public function process(Request $request, Response $response)
    {
        $this->request = $request;
        $type = $response->headers->get('Content-Type');
        if (empty($type)) {
            $type = 'text/html';
        }
        $parts = explode(';', $type);
        if (!in_array($parts[0], $this->contentTypes)) {
            return $response;
        }
        // we don't use a proper XML parser here as we can have ESI tags in a plain text response
        $content = $response->getContent();
        $content = preg_replace('#<esi\:remove>.*?</esi\:remove>#', '', $content);
        $content = preg_replace('#<esi\:comment[^>]*(?:/|</esi\:comment)>#', '', $content);
        $chunks = preg_split('#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks[0] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[0]);
        $i = 1;
        while (isset($chunks[$i])) {
            $options = array();
            preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $chunks[$i], $matches, PREG_SET_ORDER);
            foreach ($matches as $set) {
                $options[$set[1]] = $set[2];
            }
            if (!isset($options['src'])) {
                throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
            }
            $chunks[$i] = sprintf('<?php echo $this->esi->handle($this, %s, %s, %s) ?>'."\n",
                var_export($options['src'], true),
                var_export(isset($options['alt']) ? $options['alt'] : '', true),
                isset($options['onerror']) && 'continue' == $options['onerror'] ? 'true' : 'false'
            );
            ++$i;
            $chunks[$i] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[$i]);
            ++$i;
        }
        $content = implode('', $chunks);
        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'ESI');
        // remove ESI/1.0 from the Surrogate-Control header
        if ($response->headers->has('Surrogate-Control')) {
            $value = $response->headers->get('Surrogate-Control');
            if ('content="ESI/1.0"' == $value) {
                $response->headers->remove('Surrogate-Control');
            } elseif (preg_match('#,\s*content="ESI/1.0"#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#,\s*content="ESI/1.0"#', '', $value));
            } elseif (preg_match('#content="ESI/1.0",\s*#', $value)) {
                $response->headers->set('Surrogate-Control', preg_replace('#content="ESI/1.0",\s*#', '', $value));
            }
        }
    }
}
