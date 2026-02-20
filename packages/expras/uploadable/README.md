# ExprAs Uploadable Package

Simple and automatic file upload handling for Mezzio applications. Files are automatically parsed, stored, and made available in your request body as Doctrine entities - no manual handling required!

## Installation

```bash
composer require expras/uploadable
```

## Key Benefits

- **Zero Configuration** - Works out of the box with sensible defaults
- **Automatic Handling** - Files are automatically parsed and stored
- **Database Integration** - File information is automatically stored as Doctrine entities
- **Request Body Access** - Uploaded files are available in `$request->getParsedBody()` as `Uploaded` entities
- **Ready to Use** - No complex setup or manual file handling needed

## Quick Start

1. Install the package
2. Add middleware to your pipeline
3. Start uploading files - that's it!

```php
use Expras\Uploadable\Entity\Uploaded;

// In your pipeline configuration:
$app->pipe(Expras\Uploadable\Middleware\UploadMiddleware::class);

// In your handler - files are automatically available as Uploaded entities:
public function handle(ServerRequestInterface $request): ResponseInterface
{
    /** @var Uploaded[] $files */
    $files = $request->getParsedBody()['form_name'];
    
    foreach ($files as $file) {
        // Access file information through entity methods
        $id = $file->getId();          // Database ID
        $path = $file->getPath();      // Storage path
        $url = $file->getUrl();        // Public URL
        $name = $file->getName();      // Original filename
        $size = $file->getSize();      // File size
        $type = $file->getMimeType();  // Mime type
        
        // Additional entity features
        $extension = $file->getExtension();    // File extension
        $hash = $file->getHash();             // File hash
        $createdAt = $file->getCreatedAt();   // Upload timestamp
    }
    
    return new JsonResponse([
        'message' => 'Files uploaded successfully',
        'files' => array_map(fn(Uploaded $file) => $file->toArray(), $files)
    ]);
}
```

## Features

### Automatic File Management
- Files are automatically:
  - Parsed from requests
  - Validated for security
  - Stored in configured location
  - Persisted as Doctrine entities
  - Added to request body as `Uploaded` entities

### Storage Options
- Local filesystem (default)
- Amazon S3 (in progress)
- Google Cloud Storage (in progress)
- FTP/SFTP (in progress)
- Memory storage (for testing)
- Custom provider support (in progress)

### Image Handling
- Automatic image processing:
  - Thumbnail generation
  - Resizing
  - Format conversion
  - Optimization

### Security
- Automatic security measures:
  - File type validation
  - Size restrictions
  - Antivirus scanning
  - Secure file names

## Configuration (Optional)

Most features work out of the box, but you can customize if needed:

```php
// config/autoload/uploadable.global.php
return [
    'uploadable' => [
        // Basic settings
        'path' => 'data/uploads',    // Storage location
        'public_url' => '/uploads',  // Public URL prefix
        'max_size' => '10M',         // Maximum file size
        
        // Allowed file types (defaults to common safe types)
        'allowed_types' => [
            'image/*',
            'application/pdf',
            'application/msword',
        ],
        
        // Image processing (optional)
        'image' => [
            'thumbnails' => true,     // Enable thumbnail generation
            'max_width' => 1920,      // Maximum image width
            'max_height' => 1080,     // Maximum image height
        ],
        
        // Database settings (optional)
        'db' => [
            'table' => 'files',       // Table name
            'enabled' => true,        // Enable database storage
        ],
    ],
];
```

## Usage Examples

### HTML Form Upload
```html
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="document">
    <button type="submit">Upload</button>
</form>
```

### Handling Uploads in Controller
```php
use Expras\Uploadable\Entity\Uploaded;

class UploadController
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Uploaded[] $files */
        $files = $request->getParsedBody()['files'];
        
        foreach ($files as $file) {
            // Work with Uploaded entity
            if ($file->isImage()) {
                // Handle image file
                $dimensions = $file->getImageDimensions();
                $width = $dimensions['width'];
                $height = $dimensions['height'];
            }
            
            // Access metadata
            $metadata = $file->getMetadata();
            
            // Get file URL
            $publicUrl = $file->getUrl();
            
            // Check file properties
            if ($file->getMimeType() === 'application/pdf') {
                // Handle PDF file
            }
        }
        
        return new JsonResponse([
            'message' => 'Files uploaded successfully',
            'files' => array_map(fn(Uploaded $file) => $file->toArray(), $files)
        ]);
    }
}
```

### Doctrine Entity Integration
```php
use Doctrine\ORM\Mapping as ORM;
use Expras\Uploadable\Entity\UploadableTrait;
use Expras\Uploadable\Entity\Uploaded;

#[ORM\Entity]
class Document
{
    use UploadableTrait;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[Uploadable]
    private ?Uploaded $file = null;

    // Getter and setter for file
    public function getFile(): ?Uploaded
    {
        return $this->file;
    }

    public function setFile(?Uploaded $file): self
    {
        $this->file = $file;
        return $this;
    }
}
```

## Console Commands

```bash
# Clean old temporary files
php bin/console uploadable:cleanup

# Generate missing thumbnails
php bin/console uploadable:thumbnails:generate
```

## Requirements

- PHP 8.0 or higher
- Mezzio 3.x
- PDO PHP Extension (for database storage)
- GD or Imagick PHP Extension (for image processing)

## Documentation

For detailed documentation, please visit the [official documentation](https://docs.expras.com/uploadable).

## Contributing

Please read [CONTRIBUTING.md](../CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests. 