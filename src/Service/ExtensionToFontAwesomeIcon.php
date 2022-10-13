<?php

namespace App\Service;

class ExtensionToFontAwesomeIcon
{
    public const DEFAULT_ICON = 'fa-file-o';

    public const EXTERNAL_LINK_ICON = 'fa-link';

    public const MAPPER = [
        'fa-file-audio-o' => [
            'aif',
            'cda',
            'mid',
            'midi',
            'mp3',
            'mpa',
            'ogg',
            'wav',
            'wma',
            'wpl',
        ],
        'fa-file-archive-o' => [
            '7z',
            'arj',
            'deb',
            'pkg',
            'rar',
            'rpm',
            'tar',
            'gz',
            'tar.gz',
            'z',
            'zip',
            'bin',
            'dmg',
            'iso',
            'toast',
            'vcd',
        ],
        'fa-file-image-o' => [
            'ai',
            'bmp',
            'gif',
            'ico',
            'jpeg',
            'jpg',
            'png',
            'ps',
            'psd',
            'svg',
            'tif',
            'tiff',
        ],
        'fa-file-code-o' => [
            'asp',
            'aspx',
            'cer',
            'cfm',
            'cgi',
            'pl',
            'css',
            'htm',
            'html',
            'js',
            'jsp',
            'part',
            'php',
            'py',
            'rss',
            'xhtml',
            'c',
            'cgi',
            'pl',
            'class',
            'cpp',
            'cs',
            'h',
            'java',
            'php',
            'py',
            'sh',
            'swift',
            'vb',
        ],
        'fa-file-powerpoint-o' => [
            'key',
            'odp',
            'pps',
            'ppt',
            'pptx',
        ],
        'fa-file-excel-o' => [
            'ods',
            'xls',
            'xlsm',
            'xlsx',
        ],
        'fa-file-video-o' => [
            '3g2',
            '3gp',
            'avi',
            'flv',
            'h264',
            'm4v',
            'mkv',
            'mov',
            'mp4',
            'mpg',
            'mpeg',
            'rm',
            'swf',
            'vob',
            'wmv',
        ],
        'fa-file-word-o' => [
            'doc',
            'docx',
            'odt',
            'wpd',
        ],
        'fa-file-pdf-o' => [
            'pdf',
        ],
        'fa-file-text-o' => [
            'md',
            'rtf',
            'tex',
            'txt',
        ],
    ];

    private array $extensionToIcon;

    public function __construct()
    {
        $this->initExtensionToIcon();
    }

    private function initExtensionToIcon(): void
    {
        $this->extensionToIcon = [];

        foreach (self::MAPPER as $icon => $extensions) {
            foreach ($extensions as $extension) {
                $this->extensionToIcon[$extension] = $icon;
            }
        }
    }

    public function getIconForExtension(string $extension): string
    {
        if (!$extension){
            return self::EXTERNAL_LINK_ICON;
        }
        if (!isset($this->extensionToIcon[$extension])) {
            return self::DEFAULT_ICON;
        }

        return $this->extensionToIcon[$extension];
    }

    public function getIconForFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return $this->getIconForExtension($extension);
    }
}
