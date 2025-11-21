<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OptimizeMedicationImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-medication-images 
                            {--dry-run : Show what would be optimized without making changes}
                            {--force : Force optimization even if already optimized}
                            {--max-size=40 : Maximum target file size in KB}
                            {--max-dimension=512 : Maximum width/height in pixels}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize medication images to reduce memory footprint (target: 30-40KB, max 512x512px)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $medicationsPath = storage_path('app/public/medications');
        $maxSizeKB = $this->option('max-size');
        $maxDimension = $this->option('max-dimension');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (!File::exists($medicationsPath)) {
            $this->error("❌ Medications directory not found: {$medicationsPath}");
            return self::FAILURE;
        }

        // Check if ImageMagick is available
        if (!$this->isImageMagickAvailable()) {
            $this->error("❌ ImageMagick is not available. Please install ImageMagick to use this command.");
            return self::FAILURE;
        }

        $this->info("🔍 Scanning for images in: {$medicationsPath}");
        $this->info("📏 Target specs: max {$maxSizeKB}KB, max {$maxDimension}x{$maxDimension}px");

        $imageFiles = $this->getImageFiles($medicationsPath);
        $totalFiles = count($imageFiles);

        if ($totalFiles === 0) {
            $this->warn("⚠️ No image files found in the medications directory.");
            return self::SUCCESS;
        }

        $this->info("📊 Found {$totalFiles} image files to process");

        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->start();

        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'optimized' => 0,
            'original_size' => 0,
            'optimized_size' => 0,
            'errors' => 0
        ];

        foreach ($imageFiles as $imagePath) {
            try {
                $result = $this->processImage($imagePath, $maxSizeKB, $maxDimension, $isDryRun, $force);
                
                $stats['processed']++;
                $stats['original_size'] += $result['original_size'];
                
                if ($result['skipped']) {
                    $stats['skipped']++;
                } else {
                    $stats['optimized']++;
                    $stats['optimized_size'] += $result['optimized_size'];
                    
                    if ($isDryRun) {
                        $this->newLine();
                        $this->line("  📄 " . basename($imagePath));
                        $this->line("    Original: " . number_format($result['original_size'] / 1024, 2) . "KB");
                        $this->line("    Optimized: " . number_format($result['optimized_size'] / 1024, 2) . "KB");
                        $this->line("    Savings: " . number_format(($result['original_size'] - $result['optimized_size']) / 1024, 2) . "KB");
                    }
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("❌ Error processing " . basename($imagePath) . ": " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->newLine();

        // Display summary
        $this->info("📈 Optimization Summary:");
        $this->line("  Total files processed: {$stats['processed']}");
        $this->line("  Files skipped: {$stats['skipped']}");
        $this->line("  Files optimized: {$stats['optimized']}");
        $this->line("  Errors: {$stats['errors']}");

        if ($stats['original_size'] > 0) {
            $originalTotalMB = number_format($stats['original_size'] / 1024 / 1024, 2);
            $optimizedTotalMB = number_format($stats['optimized_size'] / 1024 / 1024, 2);
            $savingsMB = number_format(($stats['original_size'] - $stats['optimized_size']) / 1024 / 1024, 2);
            $savingsPercent = number_format((($stats['original_size'] - $stats['optimized_size']) / $stats['original_size']) * 100, 1);

            $this->line("  Original total size: {$originalTotalMB}MB");
            $this->line("  Optimized total size: {$optimizedTotalMB}MB");
            $this->line("  Space saved: {$savingsMB}MB ({$savingsPercent}%)");
        }

        if ($isDryRun) {
            $this->newLine();
            $this->warn("💡 This was a dry run. No files were actually modified.");
            $this->warn("   Run without --dry-run to apply the optimizations.");
        } else {
            $this->newLine();
            $this->info("✅ Image optimization completed successfully!");
        }

        return self::SUCCESS;
    }

    /**
     * Check if ImageMagick is available
     */
    private function isImageMagickAvailable()
    {
        try {
            $process = new Process(['convert', '-version']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all image files from the medications directory
     */
    private function getImageFiles($directory)
    {
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
        $files = [];

        foreach (File::allFiles($directory) as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $extensions)) {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }

    /**
     * Process a single image file using ImageMagick
     */
    private function processImage($imagePath, $maxSizeKB, $maxDimension, $isDryRun, $force)
    {
        $originalSize = filesize($imagePath);
        $originalSizeKB = $originalSize / 1024;
        
        // Skip if already under target size and not forced
        if (!$force && $originalSizeKB <= $maxSizeKB) {
            return [
                'original_size' => $originalSize,
                'optimized_size' => $originalSize,
                'skipped' => true
            ];
        }

        // Get image info using ImageMagick
        $imageInfo = $this->getImageInfo($imagePath);
        $originalWidth = $imageInfo['width'];
        $originalHeight = $imageInfo['height'];
        
        // Calculate new dimensions if needed
        $needsResize = false;
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;

        if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
            // Calculate aspect ratio
            $aspectRatio = $originalWidth / $originalHeight;
            
            if ($originalWidth > $originalHeight) {
                // Landscape or square
                $newWidth = min($originalWidth, $maxDimension);
                $newHeight = $newWidth / $aspectRatio;
            } else {
                // Portrait
                $newHeight = min($originalHeight, $maxDimension);
                $newWidth = $newHeight * $aspectRatio;
            }
            
            $needsResize = true;
        }

        if ($isDryRun) {
            // For dry run, estimate optimized size
            $estimatedOptimizedSize = $this->estimateOptimizedSize($imagePath, $newWidth, $newHeight, $needsResize, $maxSizeKB);
            
            return [
                'original_size' => $originalSize,
                'optimized_size' => $estimatedOptimizedSize,
                'skipped' => false
            ];
        }

        // Perform actual optimization using ImageMagick
        $optimizedSize = $this->optimizeImageWithImageMagick($imagePath, $newWidth, $newHeight, $needsResize, $maxSizeKB);

        return [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'skipped' => false
        ];
    }

    /**
     * Get image information using ImageMagick
     */
    private function getImageInfo($imagePath)
    {
        $process = new Process(['identify', '-format', '{"width": %w, "height": %h, "format": "%m"}', $imagePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $info = json_decode($output, true);

        if (!$info) {
            throw new \Exception("Failed to parse image information");
        }

        return $info;
    }

    /**
     * Estimate optimized file size for dry run
     */
    private function estimateOptimizedSize($imagePath, $newWidth, $newHeight, $needsResize, $maxSizeKB)
    {
        $originalSize = filesize($imagePath);
        
        if (!$needsResize) {
            // If no resize needed, estimate based on quality reduction
            return min($originalSize * 0.6, $maxSizeKB * 1024);
        }

        // Calculate size reduction from resize
        $imageInfo = $this->getImageInfo($imagePath);
        $originalPixels = $imageInfo['width'] * $imageInfo['height'];
        $newPixels = $newWidth * $newHeight;
        $resizeRatio = $newPixels / $originalPixels;
        
        // Estimate: resize reduction + quality reduction
        $estimatedSize = $originalSize * $resizeRatio * 0.6;
        
        // Cap at maximum target size
        return min($estimatedSize, $maxSizeKB * 1024);
    }

    /**
     * Optimize image using ImageMagick
     */
    private function optimizeImageWithImageMagick($imagePath, $newWidth, $newHeight, $needsResize, $maxSizeKB)
    {
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        // Create temporary file for optimization
        $tempPath = tempnam(sys_get_temp_dir(), 'img_opt_') . '.' . $extension;
        
        try {
            // Build ImageMagick command
            $command = ['convert', $imagePath];
            
            // Resize if needed
            if ($needsResize) {
                $command[] = '-resize';
                $command[] = sprintf('%dx%d', $newWidth, $newHeight);
            }
            
            // Optimize based on format
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $command[] = '-quality';
                    $command[] = '85';
                    $command[] = '-strip';
                    $command[] = '-interlace';
                    $command[] = 'Plane';
                    break;
                case 'png':
                    $command[] = '-strip';
                    $command[] = '-interlace';
                    $command[] = 'None';
                    break;
                case 'webp':
                    $command[] = '-quality';
                    $command[] = '85';
                    $command[] = '-strip';
                    break;
                default:
                    // Convert unknown formats to JPEG
                    $tempPath = preg_replace('/\.[^.]+$/', '.jpg', $tempPath);
                    $command[] = '-quality';
                    $command[] = '85';
                    $command[] = '-strip';
                    $command[] = '-interlace';
                    $command[] = 'Plane';
                    break;
            }
            
            $command[] = $tempPath;
            
            // Execute the command
            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Check if optimized file is within target size
            $optimizedSize = filesize($tempPath);
            $optimizedSizeKB = $optimizedSize / 1024;

            // If still too large, apply additional compression
            if ($optimizedSizeKB > $maxSizeKB) {
                $this->applyAdditionalCompression($tempPath, $extension, $maxSizeKB);
                $optimizedSize = filesize($tempPath);
            }

            // Replace original file
            if ($optimizedSize < filesize($imagePath)) {
                copy($tempPath, $imagePath);
            }

            return filesize($imagePath);

        } finally {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Apply additional compression to meet target size
     */
    private function applyAdditionalCompression($imagePath, $extension, $maxSizeKB)
    {
        $quality = 80;
        $step = 5;
        
        while ($quality > 20) {
            $tempPath = tempnam(sys_get_temp_dir(), 'img_compress_') . '.' . $extension;
            
            try {
                $command = ['convert', $imagePath];
                
                switch ($extension) {
                    case 'jpg':
                    case 'jpeg':
                    case 'webp':
                        $command[] = '-quality';
                        $command[] = (string)$quality;
                        break;
                    case 'png':
                        // For PNG, we'll use different compression methods
                        $command[] = '-depth';
                        $command[] = '8';
                        break;
                }
                
                $command[] = $tempPath;
                
                $process = new Process($command);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                $compressedSize = filesize($tempPath) / 1024;
                
                if ($compressedSize <= $maxSizeKB) {
                    // If this compression level works, replace the file
                    copy($tempPath, $imagePath);
                    unlink($tempPath);
                    break;
                }
                
                unlink($tempPath);
                $quality -= $step;
                
            } catch (\Exception $e) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                break;
            }
        }
    }
}
