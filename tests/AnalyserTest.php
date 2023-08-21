<?php

declare(strict_types=1);

namespace TomasVotruba\Lines\Tests;

use PHPUnit\Framework\TestCase;
use TomasVotruba\Lines\Analyser;
use TomasVotruba\Lines\DependencyInjection\ContainerFactory;

final class AnalyserTest extends TestCase
{
    private Analyser $analyser;

    protected function setUp(): void
    {
        $containerFactory = new ContainerFactory();
        $container = $containerFactory->create();

        $this->analyser = $container->make(Analyser::class);
    }

    public function test(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/source.php']);

        $this->assertSame(0, $measurements->getDirectoryCount());
        $this->assertSame(1, $measurements->getFileCount());

        // lines
        $this->assertSame(82, $measurements->getLines());
        $this->assertSame(75, $measurements->getNonCommentLines());
        $this->assertSame(7, $measurements->getCommentLines());

        // structure
        $this->assertSame(1, $measurements->getNamespaceCount());
        $this->assertSame(2, $measurements->getClassCount());
        $this->assertSame(4, $measurements->getMethodCount());
        $this->assertSame(1, $measurements->getClassConstantCount());
        $this->assertSame(1, $measurements->getInterfaceCount());
        $this->assertSame(0, $measurements->getTraitCount());
        $this->assertSame(2, $measurements->getFunctionCount());
        $this->assertSame(1, $measurements->getGlobalConstantCount());

        // methods
        $this->assertSame(2, $measurements->getPublicMethods());
        $this->assertSame(1, $measurements->getProtectedMethods());
        $this->assertSame(1, $measurements->getPrivateMethods());

        // static and non-static
        $this->assertSame(25.0, $measurements->getStaticMethodsRelative());
        $this->assertSame(1, $measurements->getStaticMethods());
        $this->assertSame(3, $measurements->getNonStaticMethods());
        $this->assertSame(75.0, $measurements->getNonStaticMethodsRelative());

        // relative
        $this->assertSame(8.5, $measurements->getCommentLinesRelative());
        $this->assertSame(91.5, $measurements->getNonCommentLinesRelative());
    }

    public function testCrLineEndings(): void
    {
        $path = __DIR__ . '/Fixture/cr_line_endings.php';

        // make sure that the fixture wasn't accidentally
        // auto-formatted to normal line endings
        $content = file_get_contents($path);
        $this->assertNotFalse($content);
        $this->assertStringNotContainsString("\n", $content);

        $measurements = $this->analyser->measureFiles([$path]);

        $this->assertSame(9, $measurements->getLines());
        $this->assertSame(7, $measurements->getNonCommentLines());
        $this->assertSame(2, $measurements->getCommentLines());
    }

    public function testSkipAnonymousClass(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/issue_138.php']);
        $this->assertSame(1, $measurements->getClassCount());
    }

    public function testConstAndPublicClassConst(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/class_constants.php']);

        $this->assertSame(5, $measurements->getClassConstantCount());
        $this->assertSame(1, $measurements->getGlobalConstantCount());
    }

    public function testClasses(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/classes.php']);
        $this->assertSame(9, $measurements->getClassCount());
    }

    public function testMethodVisibility(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/methods.php']);

        $this->assertSame(2, $measurements->getPublicMethods());
        $this->assertSame(1, $measurements->getProtectedMethods());
        $this->assertSame(3, $measurements->getPrivateMethods());

        $this->assertSame(6, $measurements->getMethodCount());
    }

    public function testEnums(): void
    {
        $measurements = $this->analyser->measureFiles([__DIR__ . '/Fixture/enums.php']);

        $this->assertSame(0, $measurements->getClassCount());
        $this->assertSame(1, $measurements->getEnumCount());
    }
}
