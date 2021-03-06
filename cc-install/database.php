<?php

// Send user to appropriate step
if (!isset ($settings->completed)) {
    header ("Location: " . HOST . '/cc-install/');
    exit();
} else if (!in_array ('ftp', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?ftp');
    exit();
} else if (in_array ('database', $settings->completed)) {
    header ("Location: " . HOST . '/cc-install/?site-details');
    exit();
}


// Establish needed vars.
$page_title = 'CumulusClips - Database Setup';
$errors = array();
$error_msg = null;


// Handle form if submitted
if (isset ($_POST['submitted'])) {

    // Validate hostname
    $pattern = '/^[a-z0-9][a-z0-9\.\-]*$/i';
    if (!empty ($_POST['hostname']) && !ctype_space ($_POST['hostname']) && preg_match ($pattern, $_POST['hostname'])) {
        $hostname = trim ($_POST['hostname']);
    } else {
        $errors['hostname'] = "A valid hostname is needed";
    }


    // Validate port
    if (!empty($_POST['name']) && is_numeric($_POST['port'])) {
        $port = trim ($_POST['port']);
    } else {
        $errors['port'] = "A valid database port is needed";
    }


    // Validate name
    if (!empty ($_POST['name']) && !ctype_space ($_POST['name'])) {
        $name = trim ($_POST['name']);
    } else {
        $errors['name'] = "A valid database name is needed";
    }


    // Validate username
    if (!empty ($_POST['username']) && !ctype_space ($_POST['username'])) {
        $username = trim ($_POST['username']);
    } else {
        $errors['username'] = "A valid username is needed";
    }


    // Validate password
    if (!empty ($_POST['password']) && !ctype_space ($_POST['password'])) {
        $password = trim ($_POST['password']);
    } else {
        $errors['password'] = "A valid password is needed";
    }


    // Validate prefix
    if (!empty ($_POST['prefix']) && !ctype_space ($_POST['prefix'])) {
        $prefix = trim ($_POST['prefix']);
    } else {
        $prefix = '';
    }


    // Execute queries if no form errors were found
    if (empty ($errors)) {

        // Include required files
        include_once (INSTALL . '/includes/queries.php');
        include_once (INSTALL . '/includes/FilesystemInstaller.php');

        try {

            // Connect to user's database server
            $pdo = new PDO(
                'mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $name,
                $username,
                $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );

            // Perform install queries
            foreach ($install_queries as $query) {
                $sql = str_replace('{DB_PREFIX}', $prefix, str_replace("\n", '', ($query)));
                $pdoStatement = $pdo->prepare($sql);
                $pdoStatement->execute();
            }


            // Open temp config file and replace placeholders with actual values
            $config_file = INSTALL . '/includes/config.default.php';
            $config_content = @file_get_contents ($config_file);

            // DB Values
            $config_content = preg_replace ('/{DB_HOST}/i', $hostname, $config_content);
            $config_content = preg_replace ('/{DB_PORT}/i', $port, $config_content);
            $config_content = preg_replace ('/{DB_NAME}/i', $name, $config_content);
            $config_content = preg_replace ('/{DB_USER}/i', $username, $config_content);
            $config_content = preg_replace ('/{DB_PASS}/i', $password, $config_content);
            $config_content = preg_replace ('/{DB_PREFIX}/i', $prefix, $config_content);

            // FTP Values
            $config_content = preg_replace ('/{FTP_HOST}/i', $settings->ftp_hostname, $config_content);
            $config_content = preg_replace ('/{FTP_USER}/i', $settings->ftp_username, $config_content);
            $config_content = preg_replace ('/{FTP_PASS}/i', $settings->ftp_password, $config_content);
            $config_content = preg_replace ('/{FTP_PATH}/i', $settings->ftp_path, $config_content);
            $config_content = preg_replace ('/{FTP_SSL}/i', ($settings->ftp_ssl) ? 'true' : 'false', $config_content);


            // Save database settings to config file in permanent location
            $perm_config_file = DOC_ROOT . '/cc-core/system/config.php';
            FilesystemInstaller::Create ($perm_config_file);
            FilesystemInstaller::Write ($perm_config_file, $config_content);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/ffmpeg', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-32-bit/qt-faststart', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/ffmpeg', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/system/bin/ffmpeg-64-bit/qt-faststart', 0755);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-core/logs', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/temp', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/h264', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/mobile', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/thumbs', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/theora', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/webm', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/files/attachments', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/files/library', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/avatars', 0777);
            FilesystemInstaller::setPermissions(DOC_ROOT . '/cc-content/uploads/import', 0777);


            // Store information & redirect user
            $settings->db_hostname = $hostname;
            $settings->db_port = $port;
            $settings->db_name = $name;
            $settings->db_username = $username;
            $settings->db_password = $password;
            $settings->db_prefix = $prefix;
            $settings->completed[] = 'database';
            $_SESSION['settings'] = serialize ($settings);
            header ("Location: " . HOST . '/cc-install/?site-details');
            exit();

        } catch (Exception $e) {
            $error_msg = '<p>Unable to perform database portion of installation. The following error occured: <br><br>'
                . $e->getMessage()
                . '</p>';
        }

    } else {
        $error_msg = '<p>Errors were found. Please correct them and try again.<br /><br /> - ';
        $error_msg .= implode ('<br / >- ', $errors);
    }

}


// Output page
include_once (INSTALL . '/views/database.tpl');
