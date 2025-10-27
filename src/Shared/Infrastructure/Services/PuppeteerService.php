<?php

namespace Src\Shared\Infrastructure\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

final class PuppeteerService
{
    public function getHtml(string $url): ?string
    {
        $hash = md5($url);
        $outputFile = storage_path("app/scraped/{$hash}.html");

        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        if (! is_dir(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0755, true);
        }

        $success = $this->runScript($url, $outputFile);

        if ($success && file_exists($outputFile)) {
            $content = file_get_contents($outputFile);
            unlink($outputFile); // Clean up after reading

            return $content;
        }

        return null;
    }

    // protected function runScript(string $url, string $outputFile): bool
    // {
    //     $scriptPath = base_path('node-scraper/playwright.mjs'); // Ensure this file exists
    //     $nodeBinary = App::environment('production')
    //         ? '/usr/bin/node' // Adjust if your production node path is different
    //         : 'node';

    //     $cmdParts = [
    //         escapeshellcmd($nodeBinary),
    //         escapeshellarg($scriptPath),
    //         escapeshellarg($url),
    //         escapeshellarg($outputFile),
    //     ];

    //     $command = implode(' ', $cmdParts).' 2>&1';
    //     $output = [];
    //     $exitCode = null;

    //     exec($command, $output, $exitCode);

    //     if ((int) $exitCode !== 0) {
    //         Log::error('Playwright script execution failed.', [
    //             'url' => $url,
    //             'exitCode' => $exitCode,
    //             'output' => implode("\n", $output),
    //         ]);

    //         return false;
    //     }

    //     return true;
    // }

    protected function runScript(string $url, string $outputFile): bool
    {
        $scriptPath = base_path('node-scraper/playwright.mjs');
        $nodeBinary = '/home/maxxmitchy/.nvm/versions/node/v25.0.0/bin/node';

        $cmdParts = [
            escapeshellcmd($nodeBinary),
            escapeshellarg($scriptPath),
            escapeshellarg($url),
            escapeshellarg($outputFile),
        ];

        $command = implode(' ', $cmdParts).' 2>&1';
        $output = [];
        $exitCode = null;

        exec($command, $output, $exitCode);

        if ((int) $exitCode !== 0) {
            \Log::error('Playwright script execution failed.', [
                'url' => $url,
                'exitCode' => $exitCode,
                'output' => implode("\n", $output),
            ]);

            return false;
        }

        return true;
    }
}
