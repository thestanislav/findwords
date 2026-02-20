<?php

namespace ExprAs\User\MezzioAuthentication;

use ExprAs\User\Entity\User;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stdlib\SplPriorityQueue;
use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use Laminas\Stratigility\Next;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdapterChain extends AbstractAdapter
{
    /**
     * @var SplPriorityQueue SplPriorityQueue
     */
    protected $adapterQueue;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * @var User
     */
    protected $user;


    /**
     * AdapterChain constructor.
     */
    public function __construct(callable $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->adapterQueue = new SplPriorityQueue();


    }

    /**
     * @param int $priority
     */
    public function inertAdapter(AbstractAdapter $adapter, $priority = 1)
    {
        $this->adapterQueue->insert($adapter, $priority);
    }


    /**
     * Authenticate the PSR-7 request and return a valid user,
     * or null if not authenticated
     *
     * @param ServerRequestInterface $request
     *
     * @return UserInterface|null
     */
    #[\Override]
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $user = parent::authenticate($request);

        $queue = clone $this->adapterQueue;
        $queue->rewind();
        do {
            /**
             * @var AuthenticationInterface $adapter 
             */
            $adapter = $queue->current();
            $user = $adapter->authenticate($request->withAttribute(User::class, $user));

            $queue->next();

        } while ($queue->valid());


        return $this->user = $user;
    }

    #[\Override]
    public function complete(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $adapterQueue = new \SplQueue();
        $queue = clone $this->adapterQueue;
        $queue->rewind();
        do {
            /**
             * @var AuthenticationInterface $adapter 
             */
            $adapter = $queue->current();
            $adapterQueue->enqueue(new CallableMiddlewareDecorator([$adapter, 'complete']));
            $queue->next();

        } while ($queue->valid());

        $adapterChainHandler = new Next($adapterQueue, $handler);

        return $adapterChainHandler->handle($request->withAttribute(User::class, $this->user));
    }


    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = ($this->responseFactory)();

        $queue = clone $this->adapterQueue;
        $queue->rewind();

        do {
            /**
             * @var AuthenticationInterface $adapter 
             */
            $adapter = $queue->current();
            $_response = $adapter->unauthorizedResponse($request);
            foreach ($_response->getHeaders() as $_name => $_header) {
                $response = $response->withAddedHeader($_name, $_header);
            }

            $queue->next();

        } while ($queue->valid());

        return $response->withStatus(401);
    }
}
