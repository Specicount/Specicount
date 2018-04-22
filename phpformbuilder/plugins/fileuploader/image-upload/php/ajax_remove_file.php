<?php
use fileuploader\server\FileUploader;

include('../../server/class.fileuploader.php');

if (isset($_POST['filename']) && isset($_POST['upload_dir'])) {
    $filename   = $_POST['filename'];
    $upload_dir = $_POST['upload_dir'];
    $thumbnails = $_POST['thumbnails'];
    $file       = $upload_dir . $filename;

    FileUploader::removeFile($file);

    if ($thumbnails == 'true') {
        $thumb_lg   = $upload_dir . 'thumbs/lg/' . $filename;
        $thumb_md   = $upload_dir . 'thumbs/md/' . $filename;
        $thumb_sm   = $upload_dir . 'thumbs/sm/' . $filename;

        FileUploader::removeFile($thumb_lg);
        FileUploader::removeFile($thumb_md);
        FileUploader::removeFile($thumb_sm);
    }
}
