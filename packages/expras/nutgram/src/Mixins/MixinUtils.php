<?php

namespace ExprAs\Nutgram\Mixins;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SergiX44\Nutgram\Telegram\Types\Media\File;

class MixinUtils
{
    /**
     * Download a file and save it to a specified local directory.
     *
     * @param  File        $file
     * @param  string      $path      Relative path or filename within the disk directory.
     * @param  string|null $disk      Path to the local directory where the file will be saved.
     * @param  array       $clientOpt Additional client options for downloading.
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function saveFileToDisk(File $file, string $path, ?string $disk = null, array $clientOpt = []): bool
    {
        $bot = $file->getBot();

        if ($bot === null) {
            throw new RuntimeException('Bot instance not found.');
        }

        // Construct the full path to save the file
        if ($disk !== null) {
            // Ensure the disk path ends with a directory separator
            $fullPath = rtrim($disk, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        } else {
            $fullPath = $path;
        }

        // Append the file name if the path ends with a slash
        if (in_array(substr($fullPath, -1), ['/', '\\'])) {
            $fullPath .= basename($file->file_path ?? $file->file_id);
        }

        // Local mode: Download directly to the specified full path
        if ($bot->getConfig()->isLocal ?? false) {
            return copy($bot->downloadUrl($file), $fullPath);
        }

        // Create a temporary file with a 20MB memory limit
        $maxMemory = 20 * 1024 * 1024;
        $tmpFile = fopen("php://temp/maxmemory:$maxMemory", 'wb+');

        if ($tmpFile === false) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        // Download the file using the HTTP client
        $http = $bot->getContainer()->get(ClientInterface::class);
        /**
 * @var ResponseInterface $response
*/
        $response = $http->get($bot->downloadUrl($file), array_merge(['sink' => $tmpFile], $clientOpt));

        if ($response->getStatusCode() !== 200) {
            fclose($tmpFile);
            throw new RuntimeException('Failed to download file. HTTP status: ' . $response->getStatusCode());
        }

        // Write the downloaded file to the specified path
        rewind($tmpFile);
        $result = file_put_contents($fullPath, $tmpFile) !== false;

        // Close the temporary file
        fclose($tmpFile);

        return $result;
    }
}
