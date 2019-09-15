<?php


namespace BitPress\AutoDiscovery\Tests\Discovery;

use BitPress\AutoDiscovery\Discovery\SourceFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SourceFileTest extends TestCase
{
    /**
     * @var Finder
     */
    private $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = (new Finder())->in(__DIR__.'/../resources/fixtures');
    }

    /** @test */
    public function it_gets_class_info()
    {
       $file = new SplFileInfo(
           __DIR__.'/../resources/fixtures/ValidServiceProvider.php',
           '',
           ''
       );

        $sourceFile = new SourceFile($file);

        $this->assertEquals(['Example\\NamespaceTest', 'ValidServiceProvider'], $sourceFile->getClassInfo());
    }

    /** @test */
    public function it_gets_class_info_for_non_namespaced_classes()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ClassWithoutNamespace.php',
            '',
            ''
       );
        $sourceFile = new SourceFile($file);

        $this->assertEquals([null, 'ClassWithoutNamespace'], $sourceFile->getClassInfo());
    }

    /** @test */
    public function it_detects_valid_service_provider()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidServiceProvider.php',
            '',
            ''
        );

        $sourceFile = new SourceFile($file);

        $this->assertTrue($sourceFile->isServiceProvider());
        $this->assertEquals('Example\NamespaceTest\ValidServiceProvider', $sourceFile->fullyQualifiedClass());

        $file2 = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidServiceProviderWithExtraSpacesAndLeadingBackslash.php',
            '',
            ''
        );

        $sourceFile2 = new SourceFile($file2);

        $this->assertTrue($sourceFile2->isServiceProvider());
        $this->assertEquals('Example\NamespaceTest\ValidServiceProviderWithExtraSpacesAndLeadingBackslash', $sourceFile2->fullyQualifiedClass());
    }

    /** @test */
    public function it_detects_valid_service_provider_with_fully_qualified_extends()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidServiceProviderWithFullyQualfiedExtends.php',
            '',
            ''
        );

        $sourceFile = new SourceFile($file);

        $this->assertTrue($sourceFile->isServiceProvider());
        $this->assertEquals(
            ['Example\NamespaceTest', 'ValidServiceProviderWithFullyQualfiedExtends'],
            $sourceFile->getClassInfo()
        );
        $this->assertEquals(
            'Example\NamespaceTest\ValidServiceProviderWithFullyQualfiedExtends',
            $sourceFile->fullyQualifiedClass()
        );
    }

    /** @test */
    public function it_detects_valid_service_provider_with_extra_spacing()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidServiceProviderWithExtraSpaces.php',
            '',
            ''
        );

        $sourceFile = new SourceFile($file);

        $this->assertTrue($sourceFile->isServiceProvider());
        $this->assertEquals(
            ['Example\NamespaceTest', 'ValidServiceProviderWithExtraSpaces'],
            $sourceFile->getClassInfo()
        );
        $this->assertEquals(
            'Example\NamespaceTest\ValidServiceProviderWithExtraSpaces',
            $sourceFile->fullyQualifiedClass()
        );
    }

    /** @test */
    public function it_detects_valid_facades()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidFacade.php',
            '',
            ''
        );

        $sourceFile = new SourceFile($file);

        $this->assertTrue($sourceFile->isFacade());
        $this->assertEquals(['Example\NamespaceTest', 'ValidFacade'], $sourceFile->getClassInfo());

        $this->assertEquals('Example\NamespaceTest\ValidFacade', $sourceFile->fullyQualifiedClass());

        $file2 = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidFacadeWithExtraSpaces.php',
            '',
            ''
        );

        $sourceFile2 = new SourceFile($file2);

        $this->assertTrue($sourceFile2->isFacade());
        $this->assertEquals(['Example\NamespaceTest', 'ValidFacadeWithExtraSpaces'], $sourceFile2->getClassInfo());

        $this->assertEquals('Example\NamespaceTest\ValidFacadeWithExtraSpaces', $sourceFile2->fullyQualifiedClass());
    }

    /** @test */
    public function it_detects_valid_facades_with_fully_qualified_extends()
    {
        $file = new SplFileInfo(
            __DIR__.'/../resources/fixtures/ValidFacadeWithFullyQualifiedExtends.php',
            '',
            ''
        );

        $sourceFile = new SourceFile($file);

        $this->assertTrue($sourceFile->isFacade());
        $this->assertEquals(['Example\NamespaceTest', 'ValidFacadeWithFullyQualifiedExtends'], $sourceFile->getClassInfo());

        $this->assertEquals('Example\NamespaceTest\ValidFacadeWithFullyQualifiedExtends', $sourceFile->fullyQualifiedClass());
    }
}
