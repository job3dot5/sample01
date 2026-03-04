<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'seo_', methods: ['GET', 'HEAD'])]
final class SeoController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap_xml')]
    public function sitemap(ParameterBagInterface $params): Response
    {
        $path = rtrim((string) $params->get('app.public_dir'), '/').'/sitemap.xml';

        if (!is_file($path)) {
            return new Response(
                'sitemap.xml not found.',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');
        $response->setPublic();
        $response->setMaxAge(86400);
        $response->setSharedMaxAge(86400);
        $response->setAutoEtag();
        $response->setAutoLastModified();

        return $response;
    }
}
