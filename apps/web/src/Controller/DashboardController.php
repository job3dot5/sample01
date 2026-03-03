<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route(name: 'app_')]
final class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        KernelInterface $kernel,
        RouterInterface $router,
        ParameterBagInterface $params,
        Request $request,
    ): Response {
        $routes = $this->collectRoutes($router);

        $projectDir = $kernel->getProjectDir();
        $varDir = $projectDir.'/var';
        $publicDir = (string) $params->get('app.public_dir');

        $start = $request->server->get('REQUEST_TIME_FLOAT');
        $durationMs = $start ? (microtime(true) - (float) $start) * 1000 : null;

        $stats = [
            'symfony_version' => Kernel::VERSION,
            'environment' => (string) $params->get('kernel.environment'),
            'writable_var' => is_writable($varDir) ? 'yes' : 'no',
            'writable_public' => is_writable($publicDir) ? 'yes' : 'no',
            'duration_ms' => $durationMs ? \sprintf('%.2f ms', $durationMs) : 'n/a',
        ];

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'routes' => $routes,
        ]);
    }

    #[Route('/routes', name: 'routes')]
    public function routes(RouterInterface $router): Response
    {
        return $this->render('dashboard/routes.html.twig', [
            'routes' => $this->collectRoutes($router),
        ]);
    }

    #[Route('/health', name: 'health')]
    public function health(KernelInterface $kernel, ParameterBagInterface $params, Request $request): Response
    {
        $projectDir = $kernel->getProjectDir();
        $varDir = $projectDir.'/var';
        $publicDir = (string) $params->get('app.public_dir');

        $start = $request->server->get('REQUEST_TIME_FLOAT');
        $durationMs = $start ? (microtime(true) - (float) $start) * 1000 : null;

        $rows = [
            'php_version' => \PHP_VERSION,
            'symfony_version' => Kernel::VERSION,
            'writable_var' => is_writable($varDir) ? 'yes' : 'no',
            'writable_public' => is_writable($publicDir) ? 'yes' : 'no',
            'duration_ms' => $durationMs ? \sprintf('%.2f ms', $durationMs) : 'n/a',
        ];

        return $this->render('dashboard/health.html.twig', [
            'rows' => $rows,
        ]);
    }

    #[Route('/sitemap', name: 'sitemap')]
    public function sitemap(ParameterBagInterface $params): Response
    {
        $path = rtrim((string) $params->get('app.public_dir'), '/').'/sitemap.xml';

        if (!is_file($path)) {
            return new Response(
                'sitemap.xml not found. Generate it with: php bin/console app:generate-sitemap',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $content = @file_get_contents($path);
        if (false === $content) {
            return new Response(
                'sitemap.xml could not be read',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * @return array<int, array{name: string, path: string, methods: string}>
     */
    private function collectRoutes(RouterInterface $router): array
    {
        $rows = [];
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            $path = $route->getPath();
            $methods = $route->getMethods();
            $rows[] = [
                'name' => $name,
                'path' => $path,
                'methods' => $methods ? implode(', ', $methods) : 'ANY',
            ];
        }

        array_multisort(array_column($rows, 'path'), \SORT_ASC, $rows);

        return $rows;
    }
}
