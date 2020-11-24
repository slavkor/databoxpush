<?php

namespace App\Action\Push;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Domain\User\Data\UserAuthData;
use App\Domain\Push\Data\WatchedObject;
use App\Domain\Push\Service\PushProvider;
use Fig\Http\Message\StatusCodeInterface;

class PushExecuteAction {
   /**
     * @var Responder
     */
    private $responder;
    
    /**
     * @var PushProvider
     */
    private $provider;

    /**
     * @var Session
     */
    private $session;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(Responder $responder, PushProvider $provider, Session $session)
    {
        $this->responder = $responder;
        $this->provider = $provider;
        $this->session = $session;
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /** @var UserAuthData|null $user */
        $user = $this->session->get('user');
        
        $requestdata = $request->getParsedBody();
        
        $object = new WatchedObject();
        $object->setObjectId($requestdata['objectId']);
        $object->setObjectProperties(explode(',',$requestdata['objectProperties']));
        $data = $this->provider->GetObjectMetrics($user, $object);

        $data->setPushkey($requestdata['databoxtoken']);
        
        $pushId = $this->provider->ExecutePush($user, $data);
        return $this->responder->json($response, ['push_id' => $pushId])->withStatus(StatusCodeInterface::STATUS_CREATED);
        
    }
}
