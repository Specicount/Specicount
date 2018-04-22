<?php
use fileuploader\server\FileUploader;

include('../../server/class.fileuploader.php');

if (isset($_POST['filename']) && isset($_POST['upload_dir'])) {
    $filename   = $_POST['filename'];
    $upload_dir = $_POST['upload_dir'];
    $file       = $upload_dir . $filename;

    FileUploader::removeFile($file);
}
