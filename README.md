# Easy App Installer for Nextcloud

A Nextcloud administrator app that completely bypasses the command line by allowing you to upload and install custom Nextcloud apps directly from the web UI.

## 🚀 The Problem it Solves
Installing apps that aren't listed in the official Nextcloud store is traditionally a tedious process. It usually involves:
1. Uploading a ZIP file to the server.
2. Executing into the web server or Docker pod.
3. Moving the ZIP to the `/custom_apps` directory.
4. Unzipping it manually.
5. Fiddling with `chown -R www-data:www-data` to fix permissions.
6. Enabling the app.

**Easy App Installer** does all of this for you in a single click using Nextcloud's native PHP backend.

## ✨ Features
* **UI-Based ZIP Uploads:** Install any custom app without touching a terminal.
* **Smart ID Detection:** Automatically parses the uploaded ZIP's `appinfo/info.xml` to determine the exact App ID. It extracts the files into the correct folder name, regardless of what the ZIP file is called.
* **Seamless Updates:** If you upload a ZIP for an app that is already installed, it will safely replace the old version and re-enable it.
* **Automatic Permissions:** Because the extraction is handled by Nextcloud's PHP worker, files are automatically owned by `www-data` (or your equivalent web user).

## 🔒 Security Warning & Access
Allowing arbitrary ZIP uploads and extraction on a web server is a sensitive operation. 
* **Strictly Admin Only:** The interface and the backend API endpoints use Nextcloud's `@AdminRequired` protections. 
* **Only Server Administrators** can access the page or trigger the installation service. 
* *Never remove the `@AdminRequired` annotation from the API controller.*

## 📦 Installation

To install this app on your Nextcloud instance:

1. Go to the [Releases](../../releases) page of this repository.
2. Download the latest `easy_installer-release.zip` artifact (Do **not** download the Source Code zip, as it does not contain the compiled frontend assets).
3. Extract the ZIP into your Nextcloud `apps` or `custom_apps` directory:
   ```bash
   unzip easy_installer-release.zip -d /var/www/html/custom_apps/