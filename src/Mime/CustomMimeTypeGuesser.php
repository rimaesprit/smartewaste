<?php

namespace App\Mime;

use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Mime\MimeTypes;

class CustomMimeTypeGuesser implements MimeTypeGuesserInterface
{
    private $extensionToMimeType = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
        'zip' => 'application/zip',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
    ];

    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (isset($this->extensionToMimeType[$extension])) {
            return $this->extensionToMimeType[$extension];
        }

        return 'application/octet-stream';
    }

    public function isGuesserSupported(): bool
    {
        return true;
    }
} 