<?php

namespace App\Security\OAuth2;

use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Exception\InvalidStateException;
use KnpU\OAuth2ClientBundle\Exception\MissingAuthorizationCodeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Version personnalisée du client OAuth2 qui contourne la vérification d'état
 * ATTENTION: Cette classe est conçue uniquement pour le développement local
 */
class CustomOAuth2Client extends OAuth2Client
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var AbstractProvider
     */
    protected $provider;

    public function __construct(AbstractProvider $provider, RequestStack $requestStack)
    {
        $this->provider = $provider;
        $this->requestStack = $requestStack;
        parent::__construct($provider, $requestStack);
        
        // Désactiver la vérification d'état en rendant le client stateless
        $this->setAsStateless();
    }

    /**
     * Surcharge pour désactiver la vérification d'état
     */
    public function getAccessToken(array $options = []): AccessToken
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \LogicException('No request available.');
        }

        if (null === $code = $request->get('code')) {
            throw new MissingAuthorizationCodeException('No "code" parameter was found (usually this is a query parameter).');
        }

        // Nous ne vérifions pas l'état ici, contrairement à la classe parent
        // ATTENTION: Ceci est un risque de sécurité en production (attaques CSRF)

        return $this->provider->getAccessToken('authorization_code', array_merge([
            'code' => $code,
        ], $options));
    }
} 