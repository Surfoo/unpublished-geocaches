<?php

namespace App\Tests\Service;

use App\Service\UnpublishedService;
use Geocaching\GeocachingSdk;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class UnpublishedServiceTest extends TestCase
{
    /**
     * @param array<string, string[]> $headers
     */
    private function makeResponse(int $status, string $body, array $headers = []): ResponseInterface
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);
        $stream->method('getContents')->willReturn($body);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($status);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeader')->willReturnCallback(
            fn (string $name) => $headers[$name] ?? []
        );

        return $response;
    }

    public function testIsOkReturnsTrueFor2xx(): void
    {
        $service = new UnpublishedService($this->createStub(GeocachingSdk::class));
        $this->assertTrue($service->isOk(200));
        $this->assertTrue($service->isOk(201));
        $this->assertTrue($service->isOk(298));
    }

    public function testIsOkReturnsFalseFor300AndAbove(): void
    {
        $service = new UnpublishedService($this->createStub(GeocachingSdk::class));
        $this->assertFalse($service->isOk(300));
        $this->assertFalse($service->isOk(400));
        $this->assertFalse($service->isOk(500));
    }

    public function testGetGeocachesReturnsDecodedArray(): void
    {
        $sdk = $this->createStub(GeocachingSdk::class);
        $sdk->method('getGeocaches')->willReturn(
            $this->makeResponse(200, '[{"referenceCode":"GC12345","name":"My Cache"}]')
        );

        $service = new UnpublishedService($sdk);
        $result  = $service->getGeocaches(['GC12345']);

        $this->assertCount(1, $result);
        $this->assertSame('GC12345', $result[0]->referenceCode);
    }

    public function testGetGeocachesChunksLargeInputIntoTwoBatches(): void
    {
        // 55 geocodes → two batches (50 + 5), so getGeocaches called twice
        $geocodes = array_map(fn (int $i) => sprintf('GC%05d', $i), range(1, 55));

        $sdk = $this->createMock(GeocachingSdk::class);
        $sdk->expects($this->exactly(2))
            ->method('getGeocaches')
            ->willReturn($this->makeResponse(200, '[]'));

        $service = new UnpublishedService($sdk);
        $service->getGeocaches($geocodes);
    }

    public function testGetGeocachesThrowsOnErrorResponse(): void
    {
        $sdk = $this->createStub(GeocachingSdk::class);
        $sdk->method('getGeocaches')->willReturn($this->makeResponse(401, '{"message":"Unauthorized"}'));

        $service = new UnpublishedService($sdk);

        $this->expectException(\Exception::class);
        $service->getGeocaches(['GC12345']);
    }
}
