<?php

declare(strict_types=1);

namespace App\Shared\Kernel\Http;

use App\Shared\Kernel\Response;
use RuntimeException;

final class FileDownloadResponse extends Response
{
    public function __construct(
        private readonly string $filePath,
        string $downloadName,
    ) {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('Download file is unavailable.');
        }

        parent::__construct(200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => $this->contentDisposition($downloadName),
            'Content-Length' => (string) filesize($filePath),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function sendBody(): void
    {
        readfile($this->filePath);
    }

    private function contentDisposition(string $downloadName): string
    {
        $fallback = preg_replace('/[^A-Za-z0-9._-]/', '_', $downloadName) ?: 'download';

        return sprintf(
            "attachment; filename=\"%s\"; filename*=UTF-8''%s",
            $fallback,
            rawurlencode($downloadName),
        );
    }
}
