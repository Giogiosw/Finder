# Finder - File Manager for Laravel 12 + Livewire

A full-featured file manager package for Laravel 12 and Livewire 3, inspired by [elFinder](https://github.com/Studio-42/elFinder).

## Features

- **File Operations**: Copy, move, delete, rename, create folders and files
- **Upload**: Drag & drop multi-file upload with progress feedback
- **Views**: Grid view and list view with sortable columns
- **Search**: Real-time file search within any directory
- **Archives**: Create ZIP / TAR.GZ archives and extract ZIP archives
- **Text Editing**: Built-in text/code editor with Tab key support
- **Image Thumbnails**: Auto-generated thumbnails for images
- **Image Editing**: Resize, rotate, flip, and crop images via Intervention Image
- **Multi-disk**: Supports multiple Laravel filesystem disks (local, S3, etc.)
- **Keyboard Shortcuts**: Ctrl+C/X/V, Delete, F2, F5 and more
- **Context Menu**: Right-click context menu on files and folders
- **Folder Tree**: Expandable sidebar tree navigation
- **Dark Mode**: Automatic dark mode based on OS preference
- **Responsive**: Mobile-friendly UI
- **Favorites / Places** (DB): Pin frequently-used directories
- **Share Links** (DB): Generate public share links

## Requirements

- PHP 8.2+
- Laravel 12.x
- Livewire 3.x
- `ext-zip`
- `ext-gd`

## Installation

```bash
composer require giogiosw/finder
```

Publish assets and run migrations:

```bash
php artisan vendor:publish --tag=finder-config
php artisan vendor:publish --tag=finder-assets
php artisan migrate
```

## Configuration

Edit `config/finder.php` after publishing.

```php
'default_disk' => env('FINDER_DISK', 'local'),
'disks' => ['local'],

'route' => [
    'prefix'     => 'finder',
    'middleware' => ['web', 'auth'],
],

'upload' => [
    'max_size'          => 52428800,
    'denied_extensions' => ['php', 'exe', 'sh'],
],
```

## Usage

Embed in any Blade view:

```blade
@livewire('finder::file-manager')
```

Or visit `/finder` (protected by `auth` by default).

### Finder Facade

```php
use Giogiosw\Finder\Facades\Finder;

$items = Finder::driver()->ls('/');
$info  = Finder::driver('s3')->info('uploads/image.jpg');
$info  = Finder::driver()->upload('/uploads', $request->file('file'));
```

## Architecture

```
src/
├── FinderServiceProvider.php
├── FinderManager.php
├── Facades/Finder.php
├── Drivers/
│   ├── DriverInterface.php
│   ├── AbstractDriver.php
│   ├── LocalDriver.php
│   └── S3Driver.php
├── Support/
│   ├── FileInfo.php
│   ├── MimeDetector.php
│   ├── ImageHandler.php
│   └── ArchiveHandler.php
└── Http/
    ├── Controllers/
    │   ├── FinderController.php
    │   └── ThumbnailController.php
    └── Livewire/
        ├── FileManager.php
        ├── FileTree.php
        ├── FileList.php
        ├── Toolbar.php
        └── Modals/
            ├── UploadModal.php
            ├── CreateFolderModal.php
            ├── RenameModal.php
            ├── PropertiesModal.php
            ├── ArchiveModal.php
            └── EditTextModal.php
```

## Keyboard Shortcuts

| Shortcut | Action |
|---|---|
| Ctrl/Cmd+A | Select all |
| Ctrl/Cmd+C | Copy |
| Ctrl/Cmd+X | Cut |
| Ctrl/Cmd+V | Paste |
| Delete | Delete selected |
| F2 | Rename |
| F5 | Refresh |
| Escape | Close modal |

## License

MIT
