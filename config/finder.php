<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The default filesystem disk to use for the file manager.
    | This should match a disk defined in config/filesystems.php
    |
    */
    'default_disk' => env('FINDER_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Available Disks
    |--------------------------------------------------------------------------
    |
    | List of filesystem disks available in the file manager.
    | Each disk must be defined in config/filesystems.php
    |
    */
    'disks' => ['local'],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route' => [
        'prefix' => 'finder',
        'middleware' => ['web', 'auth'],
        'name' => 'finder.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        // Maximum file size in bytes (default: 50MB)
        'max_size' => env('FINDER_MAX_UPLOAD_SIZE', 52428800),

        // Allowed MIME types (empty = allow all)
        'allowed_mimes' => [],

        // Denied MIME types
        'denied_mimes' => [
            'application/x-php',
            'application/x-httpd-php',
            'text/x-php',
        ],

        // Allowed extensions (empty = allow all)
        'allowed_extensions' => [],

        // Denied extensions for security
        'denied_extensions' => ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd'],

        // Chunk size for chunked uploads in bytes (5MB)
        'chunk_size' => 5242880,
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Configuration
    |--------------------------------------------------------------------------
    */
    'thumbnails' => [
        'enabled' => true,
        'width' => 48,
        'height' => 48,
        'quality' => 80,
        'directory' => '.thumbs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Editing
    |--------------------------------------------------------------------------
    */
    'image_editing' => [
        'enabled' => true,
        'max_width' => 4096,
        'max_height' => 4096,
        'quality' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Archive Support
    |--------------------------------------------------------------------------
    */
    'archives' => [
        'enabled' => true,
        'extract' => true,
        'create' => true,
        'allowed_types' => ['zip', 'tar', 'tar.gz', 'tar.bz2'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Text Editing
    |--------------------------------------------------------------------------
    */
    'text_editing' => [
        'enabled' => true,
        'max_size' => 2097152, // 2MB
        'extensions' => [
            'txt', 'md', 'html', 'htm', 'css', 'js', 'json', 'xml',
            'yaml', 'yml', 'ini', 'conf', 'log', 'csv', 'svg',
            'php', 'py', 'rb', 'sh', 'sql',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions / Access Control
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        // Default permissions for all users
        'read' => true,
        'write' => true,
        'mkdir' => true,
        'rm' => true,
        'rename' => true,
        'upload' => true,
        'archive' => true,
        'extract' => true,
        'edit' => true,

        // Paths that are locked (cannot be deleted or renamed)
        'locked' => [],

        // Paths that are hidden from the user
        'hidden' => ['.thumbs', '.gitkeep', '.DS_Store', 'Thumbs.db'],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    'ui' => [
        // Default view: 'grid' or 'list'
        'default_view' => 'grid',

        // Number of items per page in list view
        'items_per_page' => 100,

        // Show hidden files (starting with .)
        'show_hidden' => false,

        // Default sort column: 'name', 'size', 'modified', 'type'
        'sort_by' => 'name',

        // Sort direction: 'asc' or 'desc'
        'sort_dir' => 'asc',

        // Language
        'locale' => 'en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark Configuration
    |--------------------------------------------------------------------------
    */
    'watermark' => [
        'enabled' => false,
        'image' => null, // path to watermark image
        'position' => 'bottom-right', // top-left, top-right, bottom-left, bottom-right, center
        'opacity' => 75,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Resize Configuration
    |--------------------------------------------------------------------------
    */
    'auto_resize' => [
        'enabled' => false,
        'max_width' => 1920,
        'max_height' => 1080,
        'quality' => 90,
    ],

];
