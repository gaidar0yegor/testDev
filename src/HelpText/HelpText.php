<?php

namespace App\HelpText;

use App\Entity\User;
use DirectoryIterator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment as Twig;

class HelpText
{
    /**
     * @var string Where help texts templates are stored in twig templates.
     */
    private const HELP_TEMPLATES_FOLDER = 'help/';

    private string $templatesDir;

    private Twig $twig;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        string $templatesDir,
        Twig $twig,
        TokenStorageInterface $tokenStorage
    ) {
        $this->templatesDir = $templatesDir;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
    }

    public function shouldRenderHelp(string $helpId, User $user): bool
    {
        if (null === $user->getHelpTexts()) {
            return true;
        }

        return in_array($helpId, $user->getHelpTexts(), true);
    }

    public function renderHelp(string $helpId, array $context = []): string
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return '';
        }

        $user = $token->getUser();

        if (null === $user || !$this->shouldRenderHelp($helpId, $user)) {
            return "<div class=\"help_text_to_review\" data-help-id=\"{$helpId}\"></div>";
        }

        $template = self::HELP_TEMPLATES_FOLDER.$helpId.'.html.twig';

        $context['_helpText'] = [
            'id' => $helpId,
        ];

        return "<div class=\"help_text_to_review\" data-help-id=\"{$helpId}\">{$this->twig->render($template, $context)}</div>";
    }

    /**
     * Mark a help text as acknowledged, so removes it from the user helpTexts array.
     */
    public function acknowledge(string $helpId, User $user): void
    {
        $helpTexts = $user->getHelpTexts();

        if (null === $helpTexts) {
            $helpTexts = $this->getAllHelpTexts();
        }

        $key = array_search($helpId, $helpTexts);

        if (false === $key) {
            return;
        }

        unset($helpTexts[$key]);
        $user->setHelpTexts(array_values($helpTexts));
    }

    /**
     * Reactive a Help text to review it.
     */
    public function reactive(string $helpId, User $user): void
    {
        $helpTexts = $user->getHelpTexts();

        if (null === $helpTexts) {
            $helpTexts = [];
        } elseif (false ==! array_search($helpId,$helpTexts)) {
            return;
        }

        array_push($helpTexts,$helpId);
        $user->setHelpTexts(array_values($helpTexts));
    }

    /**
     * Returns all existing help texts.
     *
     * @return string[]
     */
    public function getAllHelpTexts(): array
    {
        $dir = new DirectoryIterator($this->templatesDir.'/'.self::HELP_TEMPLATES_FOLDER);
        $helpTexts = [];

        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }

            if ($fileinfo->getFilename() === 'layout.html.twig') {
                continue;
            }

            if ('.html.twig' !== substr($fileinfo->getFilename(), -10)) {
                continue;
            }

            $helpTexts[] = substr($fileinfo->getFilename(), 0, -10);
        }

        return $helpTexts;
    }
}
