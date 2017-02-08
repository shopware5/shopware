<?php
namespace Shopware\tests\Unit\Controllers\Backend;

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $controller;

    /**
     * @var \ReflectionMethod
     */
    private $prepareNumberSyntaxMethod;

    /**
     * @var \ReflectionMethod
     */
    private $interpretNumberSyntaxMethod;

    protected function setUp()
    {
        $this->controller = $this->createPartialMock(\Shopware_Controllers_Backend_Article::class, []);

        $class = new \ReflectionClass($this->controller);

        $this->prepareNumberSyntaxMethod = $class->getMethod('prepareNumberSyntax');
        $this->prepareNumberSyntaxMethod->setAccessible(true);

        $this->interpretNumberSyntaxMethod = $class->getMethod('interpretNumberSyntax');
        $this->interpretNumberSyntaxMethod->setAccessible(true);
    }

    public function testinterpretNumberSyntax()
    {
        $article = new Article();

        $detail = new Detail();
        $detail->setNumber('SW500');
        $article->setMainDetail($detail);

        $commands = $this->prepareNumberSyntaxMethod->invokeArgs($this->controller, ['{mainDetail.number}.{n}']);

        $result = $this->interpretNumberSyntaxMethod->invokeArgs($this->controller, [
            $article,
            $detail,
            $commands,
            2
        ]);

        $this->assertSame('SW500.2', $result);
    }
}
