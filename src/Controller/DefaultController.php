<?php

namespace App\Controller;

use App\Security\User;
use App\Service\GeocachingService;
use App\Service\GpxGeneratorService;
use App\Service\UnpublishedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly GeocachingService $geocachingService,
        private readonly GpxGeneratorService $gpxGeneratorService,
    ) {
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
        $api                = $this->geocachingService->getGeocachingClientApi();
        $unpublishedService = new UnpublishedService($api);

        if ($request->getContent() === '') {
            return $this->json($unpublishedService->getUnpublishedGeocaches());
        }

        $data = $request->toArray();
        if (!isset($data['geocodes'])) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        preg_match_all('/(GC[a-z0-9]+)/mi', $data['geocodes'], $matches);
        $geocodes = array_unique($matches[1]);

        return $this->json($unpublishedService->getGeocaches($geocodes));
    }

    #[Route('/createGpx', name: 'app_create_gpx')]
    public function getCreateGpx(Request $request): Response
    {
        if ($request->getContent() === '') {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $data = $request->toArray();

        if (!isset($data['geocodes']) || !is_array($data['geocodes']) || empty($data['geocodes'])) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $geocodes = $data['geocodes'];
        $gpxSplit = (int) ($data['gpxSplit'] ?? 0);

        $api                = $this->geocachingService->getGeocachingClientApi();
        $unpublishedService = new UnpublishedService($api);
        $geocacheData       = $unpublishedService->getGeocaches($geocodes);

        /** @var User $user */
        $user   = $this->getUser();
        $result = $this->gpxGeneratorService->generate(
            $geocacheData,
            $user->getUsername(),
            $user->getReferenceCode(),
            $gpxSplit,
        );

        return $this->json(['success' => true, 'fail' => $result['failed'], 'links' => $result['links']]);
    }
}
