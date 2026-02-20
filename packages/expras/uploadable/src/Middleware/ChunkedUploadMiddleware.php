<?php


namespace ExprAs\Uploadable\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Use a PSR-7 implementation

class ChunkedUploadMiddleware implements MiddlewareInterface
{
    private readonly string $uploadDir;

    public function __construct(string $uploadDir = '/tmp/uploads')
    {
        $this->uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $headers = $request->getHeaders();
        $fileName = $headers['x-chunk-name'][0] ?? null;
        $chunkIndex = $headers['x-chunk-index'][0] ?? null;
        $totalChunks = $headers['x-chunk-length'][0] ?? null;
        $uploadId = $headers['x-chunk-id'][0] ?? md5($fileName ?? uniqid());

        if ( $chunkIndex !== null && $totalChunks !== null) {
            $chunkIndex = (int)$chunkIndex;
            $totalChunks = (int)$totalChunks;

            $this->handleChunk($request, $uploadId, $chunkIndex);

            if ($this->allChunksReceived("{$this->uploadDir}/{$uploadId}_chunks", $totalChunks)) {
                // All chunks received; proceed with the request
                $this->assembleChunks("{$this->uploadDir}/{$uploadId}_chunks", "{$this->uploadDir}/{$uploadId}", $totalChunks);

                // Inform the client that the file upload is complete
                return new Response\EmptyResponse(204, [
                    'x-chunk-status' => 'complete',
                    'x-upload-id' => $uploadId,
                ]);
            }

            // Acknowledge the chunk
            return new Response\EmptyResponse(204, [
                'x-chunk-status' => 'ack',
                'X-Upload-ID' => $uploadId,
            ]);
        }

        if ($fileName && $uploadId) {

            $uploadedFilPath = "{$this->uploadDir}/{$uploadId}";

            $clientMediaType = $headers['X-Chunk-Type'][0] ?? (function_exists('mime_content_type') ? mime_content_type($uploadedFilPath) : null);


            return $this->handleRequestWithUploadedFiles(
                $request,
                $handler,
                new UploadedFile(fopen($uploadedFilPath, 'r'), filesize($uploadedFilPath), 0, $fileName, $clientMediaType),
                $uploadId
            );
        }

        // If no chunking headers, process normally
        return $handler->handle($request);
    }

    private function handleRequestWithUploadedFiles(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        UploadedFile $uploadedFile,
        $uploadId
    ): ResponseInterface {
        // Get the parsed body (usually an associative array or object)
        $body = $request->getParsedBody();

        // Replace placeholders and find the correct keys for the uploaded files
        [$newBody, $newFiles] = $this->processBodyAndFiles($body, $uploadedFile, $uploadId);

        // Create a new request with the updated parsed body and uploaded files
        $modifiedRequest = $request->withParsedBody($newBody)->withUploadedFiles($newFiles);

        // Pass the modified request to the handler
        return $handler->handle($modifiedRequest);
    }

    private function processBodyAndFiles($body, UploadedFile $uploadedFile, $uploadId): array
    {
        $newBody = [];
        $newFiles = [];

        foreach ($body as $key => $value) {
            // Check if the value exactly matches the placeholder {{$uploadId}}
            if ($value === sprintf('{{%s}}', $uploadId)) {
                // If the value matches, associate the uploaded file with this key
                $newFiles[$key] = $uploadedFile;
            } else {
                // If the value is not the placeholder, handle as usual
                if (is_array($value)) {
                    // If the value is an array, recursively process it
                    $newBody[$key] = $this->processBodyAndFiles($value, $uploadedFile, $uploadId);
                } else {
                    // Otherwise, just copy the value to the new body
                    $newBody[$key] = $value;
                }
            }
        }

        return [$newBody, $newFiles];
    }

    private function handleChunk(
        ServerRequestInterface $request,
        string $uploadId,
        int $chunkIndex
    ): void {
        $chunkDir = "{$this->uploadDir}/{$uploadId}_chunks";

        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0777, true);
        }

        // Write the chunk to a temporary file
        $tempChunkPath = "{$chunkDir}/chunk_{$chunkIndex}.tmp";
        $finalChunkPath = "{$chunkDir}/chunk_{$chunkIndex}";

        if (count($request->getUploadedFiles()) > 0) {
            $file = $request->getUploadedFiles()[0];
            $body = new Stream($file['tmp_name']);
        } else {
            $body = $request->getBody();

        }
        $body->rewind();
        $dest = fopen($tempChunkPath, 'w');

        while (!$body->eof()) {
            fwrite($dest, $body->read(8192));
        }

        fclose($dest);

        // Atomically rename the file to indicate it's completely written
        rename($tempChunkPath, $finalChunkPath);
    }

    private function allChunksReceived(string $chunkDir, int $totalChunks): bool
    {
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = "{$chunkDir}/chunk_{$i}";
            if (!is_file($chunkPath)) {
                return false;
            }
        }

        return true;
    }

    private function assembleChunks(string $chunkDir, string $finalPath, int $totalChunks): void
    {
        $output = fopen($finalPath, 'w');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = "{$chunkDir}/chunk_{$i}";
            $input = fopen($chunkPath, 'r');

            while ($data = fread($input, 8192)) {
                fwrite($output, $data);
            }

            fclose($input);
            unlink($chunkPath);
        }

        fclose($output);

    }
}
