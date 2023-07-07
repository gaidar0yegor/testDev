<?php

namespace App\HelpText\Twig;

use App\HelpText\HelpText;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension to display a help text
 * at the first time a new user come to a specific page.
 */
class HelpTextExtension extends AbstractExtension
{
    private HelpText $helpText;

    public function __construct(HelpText $helpText)
    {
        $this->helpText = $helpText;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('helpText', [$this, 'helpText'], ['is_safe' => ['html' => true]]),
        ];
    }

    public function helpText(string $helpId, array $context = []): string
    {
        return $this->helpText->renderHelp($helpId, $context);
    }
}
