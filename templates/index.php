<?php

declare(strict_types=1);

use OCP\Util;

Util::addScript(OCA\EasyInstaller\AppInfo\Application::APP_ID, OCA\EasyInstaller\AppInfo\Application::APP_ID . '-main');
Util::addStyle(OCA\EasyInstaller\AppInfo\Application::APP_ID, OCA\EasyInstaller\AppInfo\Application::APP_ID . '-main');

?>

<div id="easy_installer"></div>
