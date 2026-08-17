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
     * 
     * The @AdminRequired annotation ensures that only Nextcloud admins 
     * can hit this endpoint, which is crucial for security.
     */
    public function uploadApp(string $appId): DataResponse {
        $file = $this->request->getUploadedFile('app_zip');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new DataResponse(['error' => 'File upload failed or no file provided'], 400);
        }

        try {
            // Hand the temporary PHP upload path to our installer service
            $this->installerService->installZip($file['tmp_name'], $appId);
            return new DataResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }
}