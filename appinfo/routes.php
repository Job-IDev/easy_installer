<?php
return [
    'routes' => [
        // Renders your Vue frontend
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        
        // Handles the ZIP file upload
        ['name' => 'api#upload_app', 'url' => '/api/upload', 'verb' => 'POST'],
    ]
];