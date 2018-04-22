<?php
use fileuploader\server\FileUploader;

include('../../server/class.fileuploader.php');
$filename   = $_POST['input_name'];
$upload_dir = $_POST['upload_dir'];
$thumbnails = $_POST['thumbnails'];
$width      = $_POST['width'];
$height     = $_POST['height'];
$crop       = $_POST['crop'];

$fileuploader_title = 'name';
$fileuploader_replace = false;

// if after editing
if (isset($_POST['_namee']) && isset($_POST['_editorr'])) {
    $fileuploader_title = $_POST['_namee'];
    $fileuploader_replace = true;
}

// initialize FileUploader
$FileUploader = new FileUploader($filename, array(
    'limit'       => null,
    'maxSize'     => null,
    'fileMaxSize' => null,
    'extensions'  => null,
    'required'    => false,
    'uploadDir'   => $upload_dir,
    'title'       => $fileuploader_title,
    'replace'     => $fileuploader_replace,
    'listInput'   => true,
    'files'       => null
));

// call to upload the files
$data = $FileUploader->upload();

// generate thumbnails
if ($data['isSuccess']) {
    $filename = $data['files'][0]['name'];
    $source = $data['files'][0]['file'];
    $thumbs = array();
    $original = array(
        // original resized
        array(
            'width' => $width,
            'height' => $height,
            'destination' => $upload_dir . $filename,
            'crop' => $crop
        )
    );
    if ($thumbnails == 'true') {
        $thumbs = array(
            // large thumb
            array(
                'width' => 310,
                'height' => 310,
                'destination' => $upload_dir . 'thumbs/lg/' . $filename,
                'crop' => true
            ),
            // medium thumb
            array(
                'width' => 160,
                'height' => 160,
                'destination' => $upload_dir . 'thumbs/md/' . $filename,
                'crop' => true
            ),
            // small thumb
            array(
                'width' => 70,
                'height' => 70,
                'destination' => $upload_dir . 'thumbs/sm/' . $filename,
                'crop' => true
            )
        );
    }
    $thumbs = array_merge($original, $thumbs);
    foreach ($thumbs as $thumb) {
        FileUploader::resize($source, $thumb['width'], $thumb['height'], $thumb['destination'], $thumb['crop'], 90, 0);
    }
}

// export to js
echo json_encode($data);
exit;
