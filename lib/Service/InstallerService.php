<?php
namespace OCA\EasyInstaller\Service;

use OCP\App\IAppManager;
use OCP\IConfig;
use Exception;
use ZipArchive;
use SimpleXMLElement;

class InstallerService {
    private IAppManager $appManager;
    private IConfig $config;

    public function __construct(IAppManager $appManager, IConfig $config) {
        $this->appManager = $appManager;
        $this->config = $config;
    }

    public function installZip(string $tmpFilePath): string {
        $zip = new ZipArchive();
        if ($zip->open($tmpFilePath) !== true) {
            throw new Exception("Failed to open the uploaded ZIP archive.");
        }

        // 1. Locate and parse appinfo/info.xml within the ZIP
        $xmlContent = null;
        $innerPrefix = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat && str_ends_with($stat['name'], 'appinfo/info.xml')) {
                $xmlContent = $zip->getFromIndex($i);
                // Detect if the ZIP wraps everything inside a top-level folder (e.g., "msofficeonline34/")
                $innerPrefix = substr($stat['name'], 0, -strlen('appinfo/info.xml'));
                break;
            }
        }

        if (!$xmlContent) {
            $zip->close();
            throw new Exception("Invalid Nextcloud app ZIP: Could not find appinfo/info.xml");
        }

        // 2. Read the true app ID registered in info.xml
        $xml = new SimpleXMLElement($xmlContent);
        $appId = (string)$xml->id;

        if (empty($appId)) {
            $zip->close();
            throw new Exception("Could not determine the app ID from appinfo/info.xml.");
        }

        // 3. Resolve the writable custom_apps directory path
        $appsPaths = $this->config->getSystemValue('apps_paths', []);
        $targetPath = null;
        foreach ($appsPaths as $path) {
            if (isset($path['writable']) && $path['writable'] === true) {
                $targetPath = $path['path'];
                break;
            }
        }

        if (!$targetPath) {
            $zip->close();
            throw new Exception("No writable apps directory found in Nextcloud configuration.");
        }

        $appDir = $targetPath . '/' . $appId;

        // 4. Clean up existing folder if updating an existing installation
        if (file_exists($appDir)) {
            $this->deleteDirectory($appDir);
        }

        // 5. Extract files directly into $targetPath/$appId
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Skip files outside the main app path
            if (!empty($innerPrefix) && !str_starts_with($filename, $innerPrefix)) {
                continue;
            }

            // Strip the archive's root folder prefix
            $relativePath = empty($innerPrefix) ? $filename : substr($filename, strlen($innerPrefix));
            if (empty($relativePath)) {
                continue;
            }

            $destination = $appDir . '/' . $relativePath;

            if (str_ends_with($filename, '/')) {
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }
            } else {
                $dir = dirname($destination);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                copy("zip://" . $tmpFilePath . "#" . $filename, $destination);
            }
        }

        $zip->close();

        // Remove temp upload file
        if (file_exists($tmpFilePath)) {
            unlink($tmpFilePath);
        }

        // 6. Enable the app via Nextcloud's AppManager
        $this->appManager->enableApp($appId);

        return $appId;
    }

    private function deleteDirectory(string $dir): void {
        if (!file_exists($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}