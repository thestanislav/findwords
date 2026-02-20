<?php

$adminConfig = include 'admin.php';
$basePath = $adminConfig['exprass_admin']['basePath'];
return [
    'ckeditor' => [
        'editorConfig' => [
            'filebrowserBrowseUrl' => '#',
            'filebrowserUploadUrl' => $basePath . '/elfinder/connector',
            'imageUploadUrl'       => $basePath . '/elfinder/connector',
            //'filebrowserBrowseUrl' => $basePath . '/elfinder/ckeditor'
            /*
            'filebrowserBrowseUrl'      => $basePath . '/kcfinder/browse.php?opener=ckeditor&type=files&kcprofile=default',
            'filebrowserImageBrowseUrl' => $basePath .'/kcfinder/browse.php?opener=ckeditor&type=images&kcprofile=default',
            'filebrowserFlashBrowseUrl' => $basePath .'/kcfinder/browse.php?opener=ckeditor&type=flash&kcprofile=default',
            'filebrowserUploadUrl'      => $basePath .'/kcfinder/upload.php?opener=ckeditor&type=files&kcprofile=default',
            'filebrowserImageUploadUrl' => $basePath .'/kcfinder/upload.php?opener=ckeditor&type=images&kcprofile=default',
            'filebrowserFlashUploadUrl' => $basePath .'/kcfinder/upload.php?opener=ckeditor&type=flash&kcprofile=default'
            */
            'extraPlugins'         => ['uploadimage', 'image2'],
        ]
    ]
];
