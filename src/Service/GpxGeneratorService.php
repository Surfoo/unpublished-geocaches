<?php

namespace App\Service;

use Twig\Environment;

class GpxGeneratorService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly string $waypointsDir,
        private readonly string $gpxDir,
    ) {
    }

    /**
     * @param object[] $geocaches     API geocache objects
     * @param string   $username      For GPX author tag
     * @param string   $referenceCode User reference code (used to derive GPX filename prefix)
     * @param int      $gpxSplit      0 = no split, >0 = number of caches per file
     *
     * @return array{links: list<array{href: string, label: string}>, failed: string[]}
     */
    public function generate(array $geocaches, string $username, string $referenceCode, int $gpxSplit): array
    {
        $time          = date('c');
        $failed        = [];
        $waypointFiles = [];

        foreach ($geocaches as $geocache) {
            $filename = sprintf('%s/%s.gpx', $this->waypointsDir, $geocache->referenceCode);
            $handle   = fopen($filename, 'w');

            if ($handle === false) {
                $failed[] = $geocache->referenceCode;
                continue;
            }

            fwrite($handle, trim($this->twig->render('waypoint.xml', [...(array) $geocache, 'time' => $time])));
            fclose($handle);
            $waypointFiles[] = basename($filename);
        }

        $chunks         = $gpxSplit > 0 ? array_chunk($waypointFiles, $gpxSplit) : [$waypointFiles];
        $filenamePrefix = substr(hash('sha512', $referenceCode), 0, 12);
        $links          = [];

        foreach ($chunks as $key => $chunk) {
            $gpxContent = $this->twig->render('geocaches.xml', [
                'username'  => $username,
                'waypoints' => $chunk,
                'time'      => $time,
            ]);

            $xml = new \DOMDocument();
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput       = true;
            $xml->loadXML($gpxContent);

            $gpxFilename = sprintf('%s/%s.part%02d.gpx', $this->gpxDir, $filenamePrefix, $key + 1);
            file_put_contents($gpxFilename, $xml->saveXML());

            $label = 'Download GPX';
            if ($gpxSplit > 0 && count($chunks) > 1) {
                $label .= ' (part ' . ($key + 1) . ')';
            }

            $links[] = [
                'href'  => 'gpx/' . basename($gpxFilename),
                'label' => $label,
            ];
        }

        return ['links' => $links, 'failed' => $failed];
    }
}
