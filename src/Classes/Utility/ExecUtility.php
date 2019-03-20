<?php

namespace Helio\Invest\Utility;

use Psr\Http\Message\ResponseInterface;

class ExecUtility
{



    /**
     * @param string $file
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function downloadFile(string $file, ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withoutHeader('Content-Description')->withHeader('Content-Description', 'File Transfer')
            ->withoutHeader('Content-Type')->withHeader('Content-Type', 'application/octet-stream')
            ->withoutHeader('Content-Disposition')->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
            ->withoutHeader('Expires')->withHeader('Expires', '0')
            ->withoutHeader('Cache-Control')->withHeader('Cache-Control', 'must-revalidate')
            ->withoutHeader('Pragma')->withHeader('Pragma', 'public')
            ->withoutHeader('Content-Length')->withHeader('Content-Length', filesize($file))
            ->withBody(new \GuzzleHttp\Psr7\LazyOpenStream($file, 'r'));
    }
}