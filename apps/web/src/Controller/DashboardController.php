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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(name: 'dashboard_', methods: ['GET', 'HEAD'])]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'index')]
    public function index(
        KernelInterface $kernel,
        RouterInterface $router,
        ParameterBagInterface $params,
        Request $request,
        CacheInterface $cache,
    ): Response {
        $stats = $this->buildStats($kernel, $params, $request);
        $routes = $this->collectRoutes($router, $cache);

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'routes' => $routes,
        ]);
    }

    #[Route('/routes', name: 'routes')]
    public function routes(RouterInterface $router, CacheInterface $cache): Response
    {
        return $this->render('dashboard/routes.html.twig', [
            'routes' => $this->collectRoutes($router, $cache),
        ]);
    }

    #[Route('/health', name: 'health')]
    public function health(KernelInterface $kernel, ParameterBagInterface $params, Request $request): Response
    {
        $stats = $this->buildStats($kernel, $params, $request);

        return $this->render('dashboard/health.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/about', name: 'about', defaults: ['sitemap' => true])]
    public function about(KernelInterface $kernel): Response
    {
        return $this->render('dashboard/about.html.twig');
    }

    /**
     * Routes are cached for 60 seconds.
     *
     * @return array<int, array{name: string, path: string, methods: string}>
     */
    private function collectRoutes(RouterInterface $router, CacheInterface $cache): array
    {
        return $cache->get('dashboard.routes', static function (ItemInterface $item) use ($router): array {
            $item->expiresAfter(60);

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
        });
    }

    /**
     * @return array{
     *     php_version: string,
     *     symfony_version: string,
     *     environment: string,
     *     writable_var: string,
     *     writable_public: string,
     *     duration_ms: string
     * }
     */
    private function buildStats(
        KernelInterface $kernel,
        ParameterBagInterface $params,
        Request $request,
    ): array {
        $projectDir = $kernel->getProjectDir();
        $varDir = $projectDir.'/var';
        $publicDir = (string) $params->get('app.public_dir');

        $start = $request->server->get('REQUEST_TIME_FLOAT');
        $durationMs = $start ? (microtime(true) - (float) $start) * 1000 : null;

        return [
            'php_version' => \PHP_VERSION,
            'symfony_version' => Kernel::VERSION,
            'environment' => (string) $params->get('kernel.environment'),
            'writable_var' => is_writable($varDir) ? 'yes' : 'no',
            'writable_public' => is_writable($publicDir) ? 'yes' : 'no',
            'duration_ms' => $durationMs ? \sprintf('%.2f ms', $durationMs) : 'n/a',
        ];
    }
}
