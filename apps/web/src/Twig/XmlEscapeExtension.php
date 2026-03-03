<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class XmlEscapeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('xml_escape', static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8')),
        ];
    }
}
