<?php

namespace App\Tests\Service;

use App\Service\GpxGeneratorService;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class GpxGeneratorServiceTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/gpx_test_' . uniqid();
        mkdir($this->tmpDir . '/waypoints', 0777, true);
        mkdir($this->tmpDir . '/gpx', 0777, true);
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->tmpDir . '/waypoints/*.gpx') ?: []);
        array_map(unlink(...), glob($this->tmpDir . '/gpx/*.gpx') ?: []);
        rmdir($this->tmpDir . '/waypoints');
        rmdir($this->tmpDir . '/gpx');
        rmdir($this->tmpDir);
    }

    private function makeService(): GpxGeneratorService
    {
        $tmpDir = $this->tmpDir;

        $twig = $this->createStub(Environment::class);
        $twig->method('render')->willReturnCallback(function (string $template, array $vars) use ($tmpDir): string {
            if ($template === 'waypoint.xml') {
                return '<wpt ref="' . ($vars['referenceCode'] ?? 'GCTEST') . '"/>';
            }
            $waypoints = implode('', array_map(
                fn (string $f) => file_get_contents($tmpDir . '/waypoints/' . $f),
                $vars['waypoints'] ?? []
            ));

            return '<?xml version="1.0"?><gpx>' . $waypoints . '</gpx>';
        });

        return new GpxGeneratorService($twig, $tmpDir . '/waypoints', $tmpDir . '/gpx');
    }

    public function testGenerateReturnsSingleLinkWhenNoSplit(): void
    {
        $service   = $this->makeService();
        $geocaches = [(object) ['referenceCode' => 'GC12345', 'name' => 'Test Cache']];
        $result    = $service->generate($geocaches, 'testuser', 'userref01', 0);

        $this->assertCount(1, $result['links']);
        $this->assertSame('Download GPX', $result['links'][0]['label']);
        $this->assertStringStartsWith('gpx/', $result['links'][0]['href']);
        $this->assertEmpty($result['failed']);
    }

    public function testGenerateReturnsSplitLinksWhenSplitEnabled(): void
    {
        $service   = $this->makeService();
        $geocaches = [
            (object) ['referenceCode' => 'GC00001', 'name' => 'Cache 1'],
            (object) ['referenceCode' => 'GC00002', 'name' => 'Cache 2'],
            (object) ['referenceCode' => 'GC00003', 'name' => 'Cache 3'],
        ];
        $result = $service->generate($geocaches, 'testuser', 'userref01', 1);

        $this->assertCount(3, $result['links']);
        $this->assertSame('Download GPX (part 1)', $result['links'][0]['label']);
        $this->assertSame('Download GPX (part 2)', $result['links'][1]['label']);
        $this->assertSame('Download GPX (part 3)', $result['links'][2]['label']);
    }

    public function testGenerateCreatesGpxFilesOnDisk(): void
    {
        $service   = $this->makeService();
        $geocaches = [(object) ['referenceCode' => 'GC99999', 'name' => 'Test']];
        $result    = $service->generate($geocaches, 'testuser', 'userref01', 0);

        $filename = basename($result['links'][0]['href']);
        $this->assertFileExists($this->tmpDir . '/gpx/' . $filename);
    }

    public function testSingleFileHasNoPartLabelWhenSplitMatchesCount(): void
    {
        $service   = $this->makeService();
        $geocaches = [
            (object) ['referenceCode' => 'GC00001', 'name' => 'Cache 1'],
            (object) ['referenceCode' => 'GC00002', 'name' => 'Cache 2'],
        ];
        // split = 2 but only 2 caches → 1 chunk → no part label
        $result = $service->generate($geocaches, 'testuser', 'userref01', 2);

        $this->assertCount(1, $result['links']);
        $this->assertSame('Download GPX', $result['links'][0]['label']);
    }
}
