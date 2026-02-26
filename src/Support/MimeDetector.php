<?php

namespace Giogiosw\Finder\Support;

class MimeDetector
{
    protected static array $mimeMap = [
        // Images
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/x-icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',

        // Video
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        'avi'  => 'video/x-msvideo',
        'mov'  => 'video/quicktime',
        'wmv'  => 'video/x-ms-wmv',
        'mkv'  => 'video/x-matroska',
        'flv'  => 'video/x-flv',

        // Audio
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'flac' => 'audio/flac',
        'aac'  => 'audio/aac',
        'ogg'  => 'audio/ogg',
        'm4a'  => 'audio/mp4',

        // Documents
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',

        // Text
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'html' => 'text/html',
        'htm'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'yaml' => 'text/yaml',
        'yml'  => 'text/yaml',
        'md'   => 'text/markdown',
        'ini'  => 'text/plain',
        'conf' => 'text/plain',
        'log'  => 'text/plain',
        'sh'   => 'application/x-sh',
        'bat'  => 'application/x-bat',
        'sql'  => 'application/sql',
        'php'  => 'application/x-php',
        'py'   => 'text/x-python',
        'rb'   => 'text/x-ruby',

        // Archives
        'zip'  => 'application/zip',
        'tar'  => 'application/x-tar',
        'gz'   => 'application/x-gzip',
        'bz2'  => 'application/x-bzip2',
        'rar'  => 'application/x-rar-compressed',
        '7z'   => 'application/x-7z-compressed',

        // Fonts
        'ttf'  => 'font/ttf',
        'otf'  => 'font/otf',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'eot'  => 'application/vnd.ms-fontobject',
    ];

    public static function fromExtension(string $extension): string
    {
        $ext = strtolower($extension);
        return static::$mimeMap[$ext] ?? 'application/octet-stream';
    }

    public static function fromPath(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return static::fromExtension($ext);
    }

    public static function fromFile(string $filePath): string
    {
        if (function_exists('mime_content_type') && file_exists($filePath)) {
            $mime = mime_content_type($filePath);
            if ($mime && $mime !== 'application/octet-stream') {
                return $mime;
            }
        }

        return static::fromPath($filePath);
    }

    public static function isImage(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    public static function isText(string $mime): bool
    {
        return str_starts_with($mime, 'text/') ||
               in_array($mime, ['application/json', 'application/xml', 'application/javascript']);
    }

    public static function isVideo(string $mime): bool
    {
        return str_starts_with($mime, 'video/');
    }

    public static function isAudio(string $mime): bool
    {
        return str_starts_with($mime, 'audio/');
    }

    public static function isArchive(string $mime): bool
    {
        return in_array($mime, [
            'application/zip',
            'application/x-tar',
            'application/x-gzip',
            'application/x-bzip2',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
        ]);
    }
}
