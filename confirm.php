<?php
include 'dbconfig.php';

function getRandomOrSpecificFile($dir, $specificFileName = null) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $fileList = [];
    foreach ($files as $file) {
        if ($file->isFile()) {
            if ($specificFileName) {
                if (basename($file) == $specificFileName) {
                    return $file;
                }
            } else {
                $fileList[] = $file;
            }
        }
    }

    if (empty($fileList)) {
        return null;
    }

    return $specificFileName ? null : $fileList[array_rand($fileList)];
}

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Connect to the database using PDO
    $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check the email and token
    $sql = "SELECT id FROM email_requests WHERE email = :email AND token = :token AND confirmed = FALSE";
    $stmt = $connection->prepare($sql);
    $stmt->execute(['email' => $email, 'token' => $token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        // Update the confirmed status
        $sql = "UPDATE email_requests SET confirmed = TRUE WHERE email = :email AND token = :token";
        $stmt = $connection->prepare($sql);
        $stmt->execute(['email' => $email, 'token' => $token]);

        // Get the zip file (either random or specific)
        $dir = "wallpaper/posters/watermarked/"; // Change to the path of your folder
        $zipFile = getRandomOrSpecificFile($dir); // Add a filename as the second argument to get a specific file

        // Send the zip file by email
        if ($zipFile) {
            $subject = "Your Requested bonus wallpaper poster";
            $body = "Here is the bounus wallpaper poster you requested: " . basename($zipFile);
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"my_boundary\"\r\n";
            $body .= "\r\n--my_boundary\r\n";
            $body .= "Content-Type: application/zip; name=\"" . basename($zipFile) . "\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"" . basename($zipFile) . "\"\r\n";
            $body .= chunk_split(base64_encode(file_get_contents($zipFile)));

            mail($email, $subject, $body, $headers);

            echo "Your request has been confirmed. Check your email for the zip file.";
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid confirmation link.";
    }
}

/**
 * Create and send a zip file containing a specified number of randomly selected files from a source folder.
 *
 * @param int $numFiles The number of files to select.
 * @param string $sourceFolder The path to the source folder.
 * @param string $destinationFolder The path to the destination folder.
 * @param string $email The email address to send the zip file to.
 * @throws Exception If there is any error in creating or sending the zip file.
 * @return void
 */
function createAndSendZip($numFiles, $sourceFolder, $destinationFolder, $email) {
    // Get all .txt files from the source folder, including nested folders
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceFolder),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $fileList = [];
    foreach ($files as $file) {
        if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'txt') {
            $fileList[] = $file->getPathname();
        }
    }

    // Shuffle and select the specified number of files
    shuffle($fileList);
    $selectedFiles = array_slice($fileList, 0, $numFiles);

    // Create a randomly named folder that does not currently exist
    do {
        $randomFolderName = bin2hex(random_bytes(8));
        $randomFolderPath = $destinationFolder . '/' . $randomFolderName;
    } while (file_exists($randomFolderPath));

    mkdir($randomFolderPath);

    // Copy the selected files into the created folder
    foreach ($selectedFiles as $file) {
        copy($file, $randomFolderPath . '/' . basename($file));
    }

    // Zip the folder
    $zipFile = $randomFolderPath . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        foreach ($selectedFiles as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        // Send the zip file by email
        $subject = "Your Requested Zip Files";
        $body = "Here are the zip files you requested.";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"my_boundary\"\r\n";
        $body .= "\r\n--my_boundary\r\n";
        $body .= "Content-Type: application/zip; name=\"" . basename($zipFile) . "\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"" . basename($zipFile) . "\"\r\n";
        $body .= chunk_split(base64_encode(file_get_contents($zipFile)));

        mail($email, $subject, $body, $headers);

        echo "Your request has been confirmed. Check your email for the zip file.";
    } else {
        echo "Failed to create zip file.";
    }
}

$numFiles = 5; // Number of files to select
$sourceFolder = "plr"; // Source folder containing files
$destinationFolder = "zips"; // Destination folder for the zip file
$email = "recipient@example.com"; // Email address to send the zip file to

createAndSendZip($numFiles, $sourceFolder,$destinationFolder, $email);

?>

