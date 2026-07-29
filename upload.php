<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;

$bucket = getenv('S3_BUCKET');
$region = getenv('AWS_REGION') ?: 'us-east-1';

// Updated to match name="image_file" from your custom index.php
if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES["image_file"]["name"]);
    $fileTmp = $_FILES["image_file"]["tmp_name"];

    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => $region
    ]);

    try {
        $s3->putObject([
            'Bucket'     => $bucket,
            'Key'        => 'uploads/' . time() . '_' . $fileName,
            'SourceFile' => $fileTmp
        ]);

        echo "<p>✅ Image uploaded successfully to S3!</p>";
    } catch (Exception $e) {
        echo "❌ Error uploading to S3: " . $e->getMessage();
    }
} else {
    echo "❌ File upload error.";
}
?>
<br><br>
<a href="index.php">Go Back</a>