<?php
/**
 * laravel-uploader settings
 *
 * @author: tuanha
 * @last-mod: 15-06-2019
 */
return [
    'default_disk' => env('BKSTAR123_LARAVEL_UPLOADER_DEFAULT_DISK', 'public'),
    'default_directory' => env('BKSTAR123_LARAVEL_UPLOADER_DEFAULT_DIRECTORY', 'media'),
    'default_max_file_size' => env('BKSTAR123_LARAVEL_UPLOADER_DEFAULT_MAX_FILE_SIZE', 52428800), // 50 MB
    'default_allowed_extensions' => [
        'png','jpg','jpeg','mp4','doc','docx','ppt','pptx','xls','xlsx','txt','pdf'
    ],
];
