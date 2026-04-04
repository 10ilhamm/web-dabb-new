<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleDriveStreamController extends Controller
{
    public function stream(Request $request, string $fileId)
    {
        $apiKey = config('services.google.drive_api_key');

        if (!$apiKey) {
            abort(503, 'Google Drive API key not configured.');
        }

        // Validate fileId format
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $fileId)) {
            abort(400, 'Invalid file ID.');
        }

        // Get file metadata (cached 1 hour)
        $meta = Cache::remember("gdrive_meta_{$fileId}", 3600, function () use ($fileId, $apiKey) {
            $resp = Http::get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'fields' => 'size,mimeType,name',
                'key' => $apiKey,
            ]);

            if (!$resp->successful()) {
                return null;
            }

            return $resp->json();
        });

        if (!$meta || empty($meta['size'])) {
            Cache::forget("gdrive_meta_{$fileId}");
            abort(404, 'File not found or not accessible.');
        }

        $fileSize = (int) $meta['size'];
        $mimeType = $meta['mimeType'] ?? 'video/mp4';

        // Parse Range header
        $rangeHeader = $request->header('Range');
        $start = 0;
        $end = $fileSize - 1;
        $statusCode = 200;
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
        ];

        if ($rangeHeader && preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            $start = (int) $matches[1];
            $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;

            if ($start > $end || $start >= $fileSize) {
                return response('', 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                ]);
            }

            $statusCode = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $headers['Content-Length'] = $end - $start + 1;

        // Fetch video binary with Range header forwarded
        $fetchHeaders = ['key' => $apiKey];
        if ($statusCode === 206) {
            $fetchHeaders = ['Range' => "bytes={$start}-{$end}"];
        }

        $response = Http::withHeaders($fetchHeaders)
            ->withOptions(['stream' => true])
            ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'alt' => 'media',
                'key' => $apiKey,
            ]);

        if (!$response->successful() && $response->status() !== 206) {
            Cache::forget("gdrive_meta_{$fileId}");
            abort(502, 'Failed to fetch video from Google Drive.');
        }

        $body = $response->toPsrResponse()->getBody();

        return new StreamedResponse(function () use ($body) {
            while (!$body->eof()) {
                echo $body->read(8192);
                flush();
            }
        }, $statusCode, $headers);
    }
}
