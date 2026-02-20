<?php

namespace ExprAs\Core\Helper\BodyParams;

use Laminas\Diactoros\UploadedFile;
use Mezzio\Helper\BodyParams\StrategyInterface;
use Psr\Http\Message\ServerRequestInterface;

class MultipartFormDataParser implements StrategyInterface
{
    /**
     * @var int upload file max size in bytes.
     */
    private $uploadFileMaxSize;

    /**
     * @var int maximum upload files count.
     */
    private $uploadFileMaxCount;

    /**
     * @var resource[] resources for temporary file, created during request parsing.
     */
    private $tmpFileResources = [];

    private $boundaryMatch;

    /**
     * @return int upload file max size in bytes.
     */
    public function getUploadFileMaxSize(): int
    {
        if ($this->uploadFileMaxSize === null) {
            $size = ini_get('upload_max_filesize');
            $s = ['g' => 1 << 30, 'm' => 1 << 20, 'k' => 1 << 10];
            $this->uploadFileMaxSize = intval($size) * ($s[strtolower(substr($size, -1))] ?: 1);
        }

        return $this->uploadFileMaxSize;
    }

    /**
     * @param  int $uploadFileMaxSize upload file max size in bytes.
     * @return static self reference.
     */
    public function setUploadFileMaxSize(int $uploadFileMaxSize): self
    {
        $this->uploadFileMaxSize = $uploadFileMaxSize;

        return $this;
    }

    /**
     * @return int maximum upload files count.
     */
    public function getUploadFileMaxCount(): int
    {
        if ($this->uploadFileMaxCount === null) {
            $this->uploadFileMaxCount = intval(ini_get('max_file_uploads'));
        }

        return $this->uploadFileMaxCount;
    }

    /**
     * @param  int $uploadFileMaxCount maximum upload files count.
     * @return static self reference.
     */
    public function setUploadFileMaxCount(int $uploadFileMaxCount): self
    {
        $this->uploadFileMaxCount = $uploadFileMaxCount;

        return $this;
    }


    public function match(string $contentType): bool
    {
        return (stripos($contentType, 'multipart/form-data') !== false)
            && preg_match('/boundary=(.*)$/is', $contentType, $this->boundaryMatch);

    }

    public function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getMethod() === 'POST' || count($request->getUploadedFiles()) > 0) {
            return $request;
        }

        return $this->_parse($request);
    }

    /**
     * Parses given request in case it holds 'multipart/form-data' content.
     * This method is immutable: it leaves passed request object intact, creating new one for parsed results.
     * This method returns original request in case it does not hold appropriate content type or has empty body.
     *
     * @param  ServerRequestInterface $request request to be parsed.
     * @return ServerRequestInterface parsed request.
     */
    protected function _parse(ServerRequestInterface $request): ServerRequestInterface
    {

        $rawBody = (string)$request->getBody();
        if (empty($rawBody)) {
            return $request
                ->withAttribute('rawBody', $rawBody)
                ->withParsedBody(null);
        }
        $boundary = $this->boundaryMatch[1];

        $bodyParts = preg_split('/\\R?-+' . preg_quote((string) $boundary, '/') . '/s', $rawBody);
        array_pop($bodyParts); // last block always has no data, contains boundary ending like `--`

        $bodyParams = [];
        $uploadedFiles = [];
        $filesCount = 0;
        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }

            [$headers, $value] = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers = $this->parseHeaders($headers);

            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }

            if (isset($headers['content-disposition']['filename'])) {
                // file upload:
                if ($filesCount >= $this->getUploadFileMaxCount()) {
                    continue;
                }

                $clientFilename = $headers['content-disposition']['filename'];
                $clientMediaType = $headers['content-type'] ?? 'application/octet-stream';
                $size = mb_strlen($value, '8bit');
                $error = UPLOAD_ERR_OK;
                $tmpResource = tmpfile();

                if ($size > $this->getUploadFileMaxSize()) {
                    $error = UPLOAD_ERR_INI_SIZE;
                } else {
                    if ($tmpResource === false) {
                        $error = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];

                        if (empty($tmpFileName)) {
                            $error = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            fseek($tmpResource, 0);
                            $this->tmpFileResources[] = $tmpResource; // save file resource, otherwise it will be deleted
                        }
                    }
                }

                $this->addValue(
                    $uploadedFiles,
                    $headers['content-disposition']['name'],
                    $this->createUploadedFile(
                        $tmpResource,
                        $clientFilename,
                        $clientMediaType,
                        $error
                    )
                );

                $filesCount++;
            } else {
                // regular parameter:
                $this->addValue($bodyParams, $headers['content-disposition']['name'], $value);
            }
        }
        return $request
            ->withAttribute('rawBody', $rawBody)
            ->withParsedBody($bodyParams)
            ->withUploadedFiles($uploadedFiles);
    }


    /**
     * Creates new uploaded file instance.
     *
     * @param  resource    $stream
     * @param  string      $clientFilename
     * @param  string|null $clientMediaType
     * @param  int|null    $error
     * @return UploadedFile
     */
    protected function createUploadedFile($stream, string $clientFilename, ?string $clientMediaType = null, ?int $error = null)
    {
        return new UploadedFile($stream, fstat($stream)['size'], $error, $clientFilename, $clientMediaType);
    }

    /**
     * Parses content part headers.
     *
     * @param  string $headerContent headers source content
     * @return array parsed headers.
     */
    private function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($headerParts as $headerPart) {
            if (!str_contains($headerPart, ':')) {
                continue;
            }

            [$headerName, $headerValue] = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);

            if (!str_contains($headerValue, ';')) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (!str_contains($part, '=')) {
                        $headers[$headerName][] = $part;
                    } else {
                        [$name, $value] = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    /**
     * Adds value to the array by input name, e.g. `Item[name]`.
     *
     * @param array  $array array which should store value.
     * @param string $name  input name specification.
     * @param mixed  $value value to be added.
     */
    private function addValue(&$array, $name, $value): void
    {
        $nameParts = preg_split('/\\]\\[|\\[/s', $name);
        $current = &$array;
        foreach ($nameParts as $namePart) {
            $namePart = trim($namePart, ']');
            if ($namePart === '') {
                $current[] = [];
                $keys = array_keys($current);
                $lastKey = array_pop($keys);
                $current = &$current[$lastKey];
            } else {
                if (!isset($current[$namePart])) {
                    $current[$namePart] = [];
                }
                $current = &$current[$namePart];
            }
        }
        $current = $value;
    }

    /**
     * Closes all temporary files associated with this parser instance.
     *
     * @return static self instance.
     */
    public function closeTmpFiles(): self
    {
        foreach ($this->tmpFileResources as $resource) {
            if (is_resource($resource)) {
                @fclose($resource);
            }
        }

        $this->tmpFileResources = [];

        return $this;
    }

    /**
     * Destructor.
     * Ensures all possibly created during parsing temporary files are gracefully closed and removed.
     */
    public function __destruct()
    {
        $this->closeTmpFiles();
    }

}
