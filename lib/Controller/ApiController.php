<?php
namespace OCA\EasyInstaller\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\EasyInstaller\Service\InstallerService;

class ApiController extends Controller {
    private InstallerService $installerService;

    public function __construct(string $appName, IRequest $request, InstallerService $installerService) {
        parent::__construct($appName, $request);
        $this->installerService = $installerService;
    }

    /**
     * @AdminRequired
     */
    public function uploadApp(): DataResponse {
        $file = $this->request->getUploadedFile('app_zip');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new DataResponse(['error' => 'File upload failed or no file provided'], 400);
        }

        try {
            $installedAppId = $this->installerService->installZip($file['tmp_name']);
            return new DataResponse([
                'status' => 'success',
                'appId' => $installedAppId,
                'message' => "App '$installedAppId' installed and enabled successfully."
            ]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }
}