<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Generate sitemap.xml from public GET routes.',
)]
final class GenerateSitemapCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly Environment $twig,
        #[Autowire('%app.public_dir%')]
        private readonly string $publicPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = $this->router->getRouteCollection()->all();

        $urls = [];
        foreach ($routes as $name => $route) {
            // ignore symfony internal routes
            if (str_starts_with($name, '_')) {
                continue;
            }

            // ignore parametrized routes like /user/{id}
            $path = $route->getPath();
            if (str_contains($path, '{')) {
                continue;
            }

            // ignore everything but GET routes
            $methods = $route->getMethods();
            if ($methods && !\in_array('GET', $methods, true)) {
                continue;
            }

            try {
                $urls[] = $this->router->generate($name, [], UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (\Throwable $e) {
                $output->writeln(\sprintf('Skipped %s: %s', $name, $e->getMessage()));
            }
        }

        sort($urls);

        $xml = $this->renderXml($urls);
        $path = rtrim($this->publicPath, '/').'/sitemap.xml';
        $dir = \dirname($path);

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException(\sprintf('Cannot create directory: %s', $dir));
            }
        }

        if (!is_writable($dir)) {
            throw new \RuntimeException(\sprintf('Directory is not writable: %s', $dir));
        }

        if (false === file_put_contents($path, $xml)) {
            throw new \RuntimeException(\sprintf('Failed to write sitemap to: %s', $path));
        }

        $output->writeln(\sprintf('Generated sitemap with %d url(s): %s', \count($urls), $path));

        return Command::SUCCESS;
    }

    /**
     * @param array<string> $urls
     */
    private function renderXml(array $urls): string
    {
        return $this->twig->render('sitemap.xml.twig', [
            'urls' => $urls,
        ]);
    }
}
