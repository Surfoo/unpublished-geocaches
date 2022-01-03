<?php

namespace App\Controller;

use App\Service\GeocachingService;
use App\Service\UnpublishedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function __construct(private ParameterBagInterface $params, private GeocachingService $geocachingService)
    {
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        $template = is_null($this->getUser()) ? 'tunnel.html.twig' : 'index.html.twig';

        return $this->render($template);
    }

    #[Route('/unpublished', name: 'app_get_unpublished', format: 'json')]
    public function getUnpublished(Request $request): Response
    {
        $api = $this->geocachingService->checkAndRefreshToken()->getGeocachingClientApi();
        $unpublisedService = new UnpublishedService($api);

        if($request->getContent() == "") {
            return $this->json($unpublisedService->getUnpublishedGeocaches());
        }

        $data = $request->toArray();
        if(!isset($data['geocodes'])) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        preg_match_all('/(GC[a-z-0-9]+)/mi', $data['geocodes'], $matches);
        $geocodes = array_unique($matches[1]);

        return $this->json($unpublisedService->getGeocaches($geocodes));
    }

    #[Route('/createGpx', name: 'app_create_gpx', /*format: 'json'*/)]
    public function getCreateGpx(Request $request): Response
    {
        if ($request->getContent() == "") {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $data = $request->toArray();

        if (!isset($data['geocodes'])) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $geocodes = $data['geocodes'];
        $gpxSplit = (int) $data['gpxSplit'];

        if (empty($geocodes) || !is_array($geocodes)) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $api = $this->geocachingService->checkAndRefreshToken()->getGeocachingClientApi();
        $unpublisedService = new UnpublishedService($api);

        $data = $unpublisedService->getGeocaches($geocodes);

        $failed = [];
        $waypointsList = [];
        $time = date('c');

        //create each geocache file
        foreach ($data as $geocache) {
            $filename = sprintf($this->params->get('kernel.project_dir') . '/waypoints/%s.gpx', $geocache->referenceCode);

            if (!$hd = fopen($filename, 'w')) {
                $failed[] = $geocache->referenceCode;
                continue;
            }

            $waypointsList[] = basename($filename);
            fwrite($hd, trim($this->renderView('waypoint.xml', [...(array) $geocache, 'time' => $time])));
            fclose($hd);
        }

        // create gpx files
        $twig_vars['username'] = $this->getUser()->getUsername();

        if ($gpxSplit > 0) {
            $waypointsList = array_chunk($waypointsList, $gpxSplit);
        } else {
            $waypointsListTmp[] = $waypointsList;
            unset($waypointsList);
            $waypointsList = $waypointsListTmp;
        }

        $links = [];

        foreach ($waypointsList as $key => $waypoints) {
            $twig_vars['waypoints'] = $waypoints;
            $gpxContent = $this->renderView('geocaches.xml', [...$twig_vars, 'time' => $time]);

            $xml = new \DomDocument();
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput = true;
            $xml->loadXML($gpxContent);
            $gpxContent = $xml->saveXML();
            $gpxFilename = sprintf($this->params->get('kernel.project_dir') . '/public/gpx/%s.part%02d.gpx',
                                   substr(hash('sha512', $this->getUser()->getReferenceCode()), 0, 12), $key + 1);
            $hd = fopen($gpxFilename, 'w');
            fwrite($hd, $gpxContent);
            fclose($hd);

            $linkName = 'Download GPX';
            if ($gpxSplit > 0 && count($waypointsList) > 1) {
                $linkName.= ' (part ' . ($key + 1) . ')';
            }

            $links[] = '<li><a href="gpx/' . basename($gpxFilename) . '" class="btn btn-success" id="download-gpx">' .
                       '<i class="bi bi-file-arrow-down"></i> ' . $linkName . '</a></li>';
        }

        return $this->json(['success' => true, 'fail' => $failed, 'link' => $links]);
    }
}
