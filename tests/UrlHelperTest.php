<?php

namespace Tests\Unit;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('UrlHelper\\', __DIR__);

use \PHPUnit\Framework\TestCase;
use \UrlHelper\UrlHelper;

class UrlHelperTest extends TestCase
{
    protected $urlStrings;

    protected function setUp()
    {
        parent::setUp();
        $this->urlStrings = [
            'absolute' => 'https://openweb.co.nz/foo/bar',
            'base' => 'https://openweb.co.nz',
            'rootRelative' => '/foo/bar',
            'contextRelative' => 'foo/bar',
            'allParts' => 'https://jeremy:pass123@www.example.com:8880/foo/bar/baz.php?fish=carp&cheese=cheddar#section-one',
            'absoluteFragment' => 'https://openweb.co.nz/foo/bar#fragment',
            'relativeFragment' => '/foo/bar#fragment',
            'absoluteSlash' => 'https://openweb.co.nz/foo/bar/',
            'absoluteSlashFragment' => 'https://openweb.co.nz/foo/bar/#fragment'
        ];
    }

    public function testConstructFromString()
    {
        $urlHelper = new UrlHelper($this->urlStrings['absolute']);
        self::assertInstanceOf(UrlHelper::class, $urlHelper);
        return $urlHelper;
    }

    /**
     *
     */
    public function testConstructFromSlash()
    {
        $urlHelper = new UrlHelper('/');
        self::assertInstanceOf(UrlHelper::class, $urlHelper);
        self::assertSame('/', (string) $urlHelper->get());
    }

    /**
     * @depends testConstructFromString
     */
    public function testCastToString(UrlHelper $urlHelper)
    {
        self::assertSame($this->urlStrings['absolute'], (string) $urlHelper);
        self::assertSame($this->urlStrings['allParts'], (string) new UrlHelper($this->urlStrings['allParts']));
    }

    public function testGetDefault()
    {
        self::assertSame($this->urlStrings['allParts'], (string) (new UrlHelper($this->urlStrings['allParts']))->get());
    }

    /**
     * @depends testConstructFromString
     */
    public function testIsAbsoluteMethod()
    {
        self::assertTrue((new UrlHelper($this->urlStrings['absolute']))->isAbsolute());
        self::assertTrue((new UrlHelper($this->urlStrings['absoluteSlash']))->isAbsolute());
        self::assertTrue((new UrlHelper($this->urlStrings['base']))->isAbsolute());
        self::assertTrue((new UrlHelper($this->urlStrings['absoluteFragment']))->isAbsolute());

        self::assertFalse((new UrlHelper($this->urlStrings['rootRelative']))->isAbsolute());
        self::assertFalse((new UrlHelper($this->urlStrings['contextRelative']))->isAbsolute());
        self::assertFalse((new UrlHelper($this->urlStrings['relativeFragment']))->isAbsolute());
    }

    /**
     * @depends testIsAbsoluteMethod
     */
    public function testIsRootRelativeMethod()
    {
        self::assertFalse((new UrlHelper($this->urlStrings['absolute']))->isRootRelative());
        self::assertFalse((new UrlHelper($this->urlStrings['absoluteSlash']))->isRootRelative());
        self::assertFalse((new UrlHelper($this->urlStrings['base']))->isRootRelative());
        self::assertFalse((new UrlHelper($this->urlStrings['contextRelative']))->isRootRelative());
        self::assertFalse((new UrlHelper($this->urlStrings['absoluteFragment']))->isRootRelative());

        self::assertTrue((new UrlHelper($this->urlStrings['rootRelative']))->isRootRelative());
        self::assertTrue((new UrlHelper($this->urlStrings['relativeFragment']))->isRootRelative());
    }

    /**
     * @depends testIsRootRelativeMethod
     */
    public function testIsContextRelativeMethod()
    {
        $absolute = new UrlHelper($this->urlStrings['absolute']);
        $base = new UrlHelper($this->urlStrings['base']);
        $rootRelative = new UrlHelper($this->urlStrings['rootRelative']);
        $contextRelative = new UrlHelper($this->urlStrings['contextRelative']);
        self::assertFalse($absolute->isContextRelative());
        self::assertFalse($base->isContextRelative());
        self::assertFalse($rootRelative->isContextRelative());
        self::assertTrue($contextRelative->isContextRelative());
    }

    public function testGetBasePart()
    {
        $absolute = new UrlHelper($this->urlStrings['absolute']);
        $base = new UrlHelper($this->urlStrings['base']);
        $rootRelative = new UrlHelper($this->urlStrings['rootRelative']);
        $contextRelative = new UrlHelper($this->urlStrings['contextRelative']);
        self::assertEquals('https://openweb.co.nz', $absolute->get('base'));
        self::assertEquals('https://openweb.co.nz', $base->get('base'));
        self::assertEquals('', $rootRelative->get('base'));
        self::assertEquals('', $contextRelative->get('base'));
    }

    public function testGetContextPart()
    {
        $absolute = new UrlHelper($this->urlStrings['absolute']);
        $base = new UrlHelper($this->urlStrings['base']);
        $rootRelative = new UrlHelper($this->urlStrings['rootRelative']);
        $contextRelative = new UrlHelper($this->urlStrings['contextRelative']);
        self::assertEquals('https://openweb.co.nz/foo/', (string) $absolute->getContextPart());
        self::assertEquals('https://openweb.co.nz/', (string) $base->getContextPart());
        self::assertEquals('/foo/', (string) $rootRelative->getContextPart());
        self::assertEquals('foo/', (string) $contextRelative->getContextPart());
        self::assertEquals(
            'https://jeremy:pass123@www.example.com:8880/foo/bar/',
            (new UrlHelper($this->urlStrings['allParts']))->getContextPart()
        );
    }

    public function testGetBaseFragment()
    {
        self::assertEquals(
            'https://jeremy:pass123@www.example.com:8880#section-one',
            (new UrlHelper($this->urlStrings['allParts']))->get('base.fragment')
        );
    }

    public function testGetSchemeHostPortDirFileExt()
    {
        self::assertEquals(
            'https://www.example.com:8880/foo/bar/baz.php',
            (new UrlHelper($this->urlStrings['allParts']))->get('scheme.host.port.dir.file.ext')
        );
        self::assertEquals(
            $this->urlStrings['absoluteSlash'],
            (new UrlHelper($this->urlStrings['absoluteSlash']))->get('scheme.host.port.dir.file.ext')
        );
    }

    public function testGetSchemeHostPortDirFileExtQuery()
    {
        $expression = 'scheme.host.port.dir.file.ext.query';
        self::assertEquals(
            'https://www.example.com:8880/foo/bar/baz.php?fish=carp&cheese=cheddar',
            (new UrlHelper($this->urlStrings['allParts']))->get($expression)
        );
        self::assertEquals(
            $this->urlStrings['absolute'],
            (new UrlHelper($this->urlStrings['absolute']))->get($expression)
        );
        self::assertEquals(
            $this->urlStrings['rootRelative'],
            (new UrlHelper($this->urlStrings['rootRelative']))->get($expression)
        );
        self::assertEquals(
            $this->urlStrings['absolute'],
            (new UrlHelper($this->urlStrings['absoluteFragment']))->get($expression)
        );
        self::assertEquals(
            $this->urlStrings['absoluteSlash'],
            (new UrlHelper($this->urlStrings['absoluteSlashFragment']))->get($expression)
        );
    }

    public function testGetExt()
    {
        self::assertEquals(
            'php',
            (string) (new UrlHelper($this->urlStrings['allParts']))->get('ext')
        );
    }
}
