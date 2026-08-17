<?php
namespace OCA\EasyInstaller\Service;

use OCP\App\IAppManager;
use OCP\IConfig;
use Exception;
use ZipArchive;

class InstallerService {
    private IAppManager $appManager;
    private IConfig $config;

    public function __construct(IAppManager $appManager, IConfig $config) {
        $this->appManager = $appManager;
        $this->config = $config;
    }

    public function installZip(string $tmpFilePath, string $appId): void {
        // 1. Find the writable custom_apps directory
        $appsPaths = $this->config->getSystemValue('apps_paths', []);
        $targetPath = null;
        
        foreach ($appsPaths as $path) {
            if (isset($path['writable']) && $path['writable'] === true) {
                $targetPath = $path['path'];
                break;
            }
        }
        
        if (!$targetPath) {
            throw new Exception("No writable apps directory found in Nextcloud configuration.");
        }

        // 2. Unzip directly into the custom_apps folder
        $zip = new ZipArchive();
        if ($zip->open($tmpFilePath) === true) {
            $zip->extractTo($targetPath);
            $zip->close();
        } else {
            throw new Exception("Failed to open or extract the uploaded ZIP file.");
        }

        // 3. Remove the temporary ZIP file
        if (file_exists($tmpFilePath)) {
            unlink($tmpFilePath);
        }

        // 4. Activate or update the app in Nextcloud
        $this->appManager->enableApp($appId);
    }
}