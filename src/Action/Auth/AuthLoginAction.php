<?php

namespace App\Action\Auth;

use App\Domain\User\Service\UserCreator;
use App\Domain\User\Data\UserAuthData;
use App\Domain\User\Service\UserAuth;
use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Google\Client as Google;

/**
 * Action.
 */
final class AuthLoginAction
{
    /**
     * @var Responder
     */
    private $responder;
    
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserAuth
     */
    private $auth;
    
    
    /**
     * @var UserCreator
     */
    private $userCreator;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(Responder $responder, Session $session, UserAuth $auth, UserCreator $userCreator)
    {
        $this->responder = $responder;
        $this->session = $session;
        $this->auth = $auth;
        $this->userCreator = $userCreator;
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
        $data = (array)$request->getParsedBody();
        
        $clientId = (string)$data['client_id'] ?? '';
        $tokenId = (string)$data['token_id'] ?? '';
        $origin = (string)$data['origin'] ?? '';
        $access_token = (string)$data['access_token'] ?? '';
        $google = new Google(['client_id' => $clientId]);
        $payload = $google->verifyIdToken($tokenId);

        $flash = $this->session->getFlashBag();
        $flash->clear();

        if ($payload) {
            
            // try to authenticate the user 
            $user = $this->auth->authenticate($payload['email'], $payload['email']);
            
            if ($user) {
                //we got the user
                $user->access_token = $access_token;
                
                $this->startUserSession($user);
                $flash->set('success', __('Login successfully'));
                $url = 'push-view';
            } else {
                // Invoke the Domain with inputs and retain the result
                $user = [
                    'username' => $payload['email'],
                    'password' => password_hash($payload['email'], PASSWORD_DEFAULT),
                    'access_token' => $access_token,
                    'client_id' => $clientId,
                    'origin' => $origin,
                    'email' => $payload['email'],
                    'first_name' => $payload['given_name'],
                    'last_name' =>  $payload['family_name'],
                    'locale' => 'en_US',
                    'enabled' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
  
                $userId = $this->userCreator->createUserFromArray($user);
                        
                $user = $this->auth->authenticate($payload['email'], $payload['email']);
                if ($user) {
                    //we got the user
                    $this->startUserSession($user);
                    $flash->set('success', __('Login successfully'));
                    $url = 'push-view';
                }
                else{            
                    $flash->set('error', __('Login failed! User creation failed! '));
                    $url = 'login';
                }
            }
        } else {
            $flash->set('error', __('Login failed! \n Token verificatoin failed! '));
            $url = 'login';
        }
        return $this->responder->redirecturltojson($response, $url);
    }
    
    /**
     * Init user session.
     *
     * @param UserAuthData $user The user
     *
     * @return void
     */
    private function startUserSession(UserAuthData $user): void
    {
        // Clears all session data and regenerates session ID
        $this->session->invalidate();
        $this->session->start();

        $this->session->set('user', $user);

        // Store user settings in session
        $this->auth->setUser($user);
    }
}
