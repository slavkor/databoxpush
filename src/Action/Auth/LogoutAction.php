<?php

namespace App\Action\Auth;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Google\Client as Google;

/**
 * Action.
 */
final class LogoutAction
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param SessionInterface $session The session handler
     * @param Responder $responder The responder
     */
    public function __construct(SessionInterface $session, Responder $responder)
    {
        $this->session = $session;
        $this->responder = $responder;
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
        
        /*
        $user = $this->session->get('user');
        if($user){
            $google = new Google(['client_id' => $user->client_id]);
            $revoke = $google->revokeToken($user->access_token);
            
        }
        */
        
        // Logout user
        $this->session->invalidate();

        return $this->responder->redirect($response, 'login');
    }
}
