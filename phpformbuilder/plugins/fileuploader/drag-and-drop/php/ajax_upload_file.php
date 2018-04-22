<?php
use fileuploader\server\FileUploader;

include('../../server/class.fileuploader.php');

// initialize FileUploader
$FileUploader = new FileUploader($_POST['input_name'], array(
    'limit'       => null,
    'maxSize'     => null,
    'fileMaxSize' => null,
    'extensions'  => null,
    'required'    => false,
    'uploadDir'   => $_POST['upload_dir'],
    'title'       => 'name',
    'replace'     => false,
    'listInput'   => true,
    'files'       => null
));

// call to upload the files
$data = $FileUploader->upload();

// export to js
echo json_encode($data);
exit;
