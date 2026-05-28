<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Enums;

enum FileType: string
{
    case ALL = 'all';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case SPREADSHEET = 'spreadsheet';
    case PRESENTATION = 'presentation';
    case ARCHIVE = 'archive';
    case CODE = 'code';

    public function label(): string
    {
        return match ($this) {
            self::ALL          => 'All Files',
            self::IMAGE        => 'Images',
            self::VIDEO        => 'Videos',
            self::AUDIO        => 'Audio',
            self::DOCUMENT     => 'Documents',
            self::SPREADSHEET  => 'Spreadsheets',
            self::PRESENTATION => 'Presentations',
            self::ARCHIVE      => 'Archives',
            self::CODE         => 'Code Files',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ALL          => 'M4 6h16M4 10h16M4 14h16M4 18h16',
            self::IMAGE        => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            self::VIDEO        => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            self::AUDIO        => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
            self::DOCUMENT     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            self::SPREADSHEET  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            self::PRESENTATION => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            self::ARCHIVE      => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14',
            self::CODE         => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
        };
    }

    /**
     * @return array<string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::ALL          => [],
            self::IMAGE        => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'tif', 'avif'],
            self::VIDEO        => ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', 'flv', 'm4v'],
            self::AUDIO        => ['mp3', 'wav', 'aac', 'ogg', 'flac', 'm4a', 'wma', 'aiff'],
            self::DOCUMENT     => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'md', 'epub'],
            self::SPREADSHEET  => ['xls', 'xlsx', 'csv', 'ods', 'numbers'],
            self::PRESENTATION => ['ppt', 'pptx', 'odp', 'key'],
            self::ARCHIVE      => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'],
            self::CODE         => ['js', 'ts', 'php', 'html', 'css', 'scss', 'json', 'xml', 'yaml', 'yml', 'vue', 'jsx', 'tsx', 'py', 'rb', 'go', 'rs', 'java', 'c', 'cpp', 'h', 'sh', 'bash'],
        };
    }

    /**
     * @return array<string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::ALL          => [],
            self::IMAGE        => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/x-icon', 'image/tiff', 'image/avif'],
            self::VIDEO        => ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/x-ms-wmv', 'video/x-flv'],
            self::AUDIO        => ['audio/mpeg', 'audio/wav', 'audio/aac', 'audio/ogg', 'audio/flac', 'audio/mp4', 'audio/x-ms-wma', 'audio/aiff'],
            self::DOCUMENT     => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'application/rtf', 'application/vnd.oasis.opendocument.text', 'text/markdown', 'application/epub+zip'],
            self::SPREADSHEET  => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'application/vnd.oasis.opendocument.spreadsheet'],
            self::PRESENTATION => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.oasis.opendocument.presentation'],
            self::ARCHIVE      => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip', 'application/x-bzip2', 'application/x-xz'],
            self::CODE         => ['application/javascript', 'text/javascript', 'application/x-httpd-php', 'text/html', 'text/css', 'application/json', 'application/xml', 'text/yaml', 'text/x-vue', 'text/x-python', 'text/x-ruby', 'text/x-go', 'text/x-rust', 'text/x-java-source', 'text/x-c', 'text/x-c++', 'application/x-sh'],
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ALL          => '#6b7280',
            self::IMAGE        => '#10b981',
            self::VIDEO        => '#8b5cf6',
            self::AUDIO        => '#f59e0b',
            self::DOCUMENT     => '#ef4444',
            self::SPREADSHEET  => '#22c55e',
            self::PRESENTATION => '#f97316',
            self::ARCHIVE      => '#6366f1',
            self::CODE         => '#14b8a6',
        };
    }

    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        foreach (self::cases() as $type) {
            if ($type === self::ALL) {
                continue;
            }
            if (in_array($extension, $type->extensions())) {
                return $type;
            }
        }

        return self::ALL;
    }

    public static function fromMimeType(string $mimeType): self
    {
        $mimeType = strtolower($mimeType);

        foreach (self::cases() as $type) {
            if ($type === self::ALL) {
                continue;
            }
            if (in_array($mimeType, $type->mimeTypes())) {
                return $type;
            }
        }

        return self::ALL;
    }
}
