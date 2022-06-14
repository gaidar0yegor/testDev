<?php

namespace App\Twig;

use App\MultiSociete\UserContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CodeColorSocieteExtension extends AbstractExtension
{
    public const CORP_APP_COLOR = '#ce352c';
    public const BLACK_COLOR = '#000000';
    public const WHITE_COLOR = '#ffffff';

    public function getFilters(): array
    {
        return [
            new TwigFilter('apply_code_color_societe', [$this, 'applyCodeColorSociete']),
        ];
    }

    public function applyCodeColorSociete(UserContext $userContext, string $cssAttribute) :string
    {
        $hexColor = $userContext->hasSocieteUser() ? $userContext->getSocieteUser()->getSociete()->getColorCode() : $this::CORP_APP_COLOR;

        switch ($cssAttribute){
            case 'background-image':
                return "background-image: linear-gradient(90deg, ".$this::CORP_APP_COLOR." 65%, ".$hexColor." 100%);";
            case 'background-color':
                return "background-color: ".$hexColor.";color: ".($this->hexIsLight($hexColor) ? $this::BLACK_COLOR : $this::WHITE_COLOR);
            default:
                return '';
        }
    }

    private function hexIsLight(string $hexColor)
    {
        $hex = str_replace( '#', '', $hexColor );

        $c_r = hexdec( substr( $hex, 0, 2 ) );
        $c_g = hexdec( substr( $hex, 2, 2 ) );
        $c_b = hexdec( substr( $hex, 4, 2 ) );

        $brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

        return $brightness > 155;
    }
}
