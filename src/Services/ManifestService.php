<?php

declare(strict_types=1);

namespace SuperInteractive\SuperAdminToolbar\Services;

use Illuminate\Support\Facades\Log;
use Throwable;
use JsonException;

class ManifestService
{
    private const BUILD_DIR = 'build/';
    private const PUBLISHED_DIR_SLUG = 'statamic-super-admin-toolbar';

    private ?array $manifestData = null;
    private bool $manifestLoaded = false;
    private string $manifestPath;
    private string $basePublicPath;
    private string $basePublicUrl;

    public function __construct()
    {
        $vendorPath = 'vendor/' . self::PUBLISHED_DIR_SLUG . '/';
        $this->basePublicPath = public_path($vendorPath);
        $this->basePublicUrl = asset($vendorPath);
        $this->manifestPath = $this->basePublicPath . self::BUILD_DIR . 'manifest.json';
    }

    public function getJsAndCssUrls(string $jsEntryKey, string $cssEntryKey): array
    {
        $jsUrl = $this->getAssetUrl($jsEntryKey);
        $cssUrl = $this->getAssetUrl($cssEntryKey);

        if (is_null($cssUrl)) {
            $cssUrl = $this->resolveCssFromJsEntry($jsEntryKey);
        }

        return [
            'js' => $jsUrl,
            'css' => $cssUrl,
        ];
    }

    public function getAssetContent(string $entryKey): ?string
    {
        $relativePath = $this->getRelativePath($entryKey);

        if (is_null($relativePath)) {
            return null;
        }

        $filePath = $this->basePublicPath . self::BUILD_DIR . $relativePath;

        if (!file_exists($filePath)) {
            Log::warning('SuperAdminToolbar: Asset not found.', ['path' => $filePath]);
            return null;
        }

        try {
            return file_get_contents($filePath);
        } catch (Throwable $e) {
            Log::error('SuperAdminToolbar: Failed to read asset.', ['file' => $filePath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function getAssetUrl(string $entryKey): ?string
    {
        $relativePath = $this->getRelativePath($entryKey);

        return $relativePath
            ? rtrim($this->basePublicUrl, '/') . '/' . self::BUILD_DIR . ltrim($relativePath, '/')
            : null;
    }

    private function getRelativePath(string $entryKey): ?string
    {
        $manifest = $this->loadManifest();

        if (is_null($manifest)) {
            return null;
        }

        $entry = $manifest[$entryKey]['file'] ?? null;

        if (is_null($entry)) {
            Log::warning('SuperAdminToolbar: Missing entry key in manifest.', ['key' => $entryKey]);
        }

        return $entry;
    }

    private function resolveCssFromJsEntry(string $jsEntryKey): ?string
    {
        $manifest = $this->loadManifest();

        if (is_null($manifest)) {
            return null;
        }

        $cssPath = $manifest[$jsEntryKey]['css'][0] ?? null;

        return $cssPath
            ? rtrim($this->basePublicUrl, '/') . '/' . self::BUILD_DIR . ltrim($cssPath, '/')
            : null;
    }

    private function loadManifest(): ?array
    {
        if ($this->manifestLoaded) {
            return $this->manifestData;
        }

        $this->manifestLoaded = true;

        if (!file_exists($this->manifestPath)) {
            Log::error('SuperAdminToolbar: Manifest file not found.', ['path' => $this->manifestPath]);
            return null;
        }

        try {
            $this->manifestData = json_decode(
                file_get_contents($this->manifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $this->manifestData;
        } catch (JsonException|Throwable $e) {
            Log::error('SuperAdminToolbar: Failed to parse manifest.', [
                'path' => $this->manifestPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
