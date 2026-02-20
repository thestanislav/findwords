<?php
declare(strict_types=1);

namespace PageCache\IdGenerator\IdGeneratorExample;

use PageCache\IdGenerator\IdGeneratorInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class IdGeneratorExample extends AbstractIdGenerator implements IdGeneratorInterface
{
    public function generate(ServerRequestInterface $request): string
    {
        /*
        // Available methods
        $request->getAttribute();
        $request->getAttributes();
        $request->getBody();
        $request->getCookieParams();
        $request->getHeader();
        $request->getHeaderLine();
        $request->getHeaders();
        $request->getMethod();
        $request->getParsedBody();
        $request->getProtocolVersion();
        $request->getQueryParams();
        $request->getRequestTarget();
        $request->getServerParams();
        $request->getUploadedFiles();
        $request->getUri();
        */

        $vars = [];

        // Access URI
        $vars[] = $request->getUri();

        // Access Attributes
        $vars[] = $request->getAttribute('attribute_key');

        // Access Session
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session instanceof SessionInterface && $session->has('session_key')) {
            $vars[] = $session->get('session_key');
        }

        return $this->getHash($vars);
    }
}
