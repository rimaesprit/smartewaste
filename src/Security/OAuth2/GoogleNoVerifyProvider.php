<?php

namespace App\Security\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use GuzzleHttp\Client;

class GoogleNoVerifyProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    protected $accessType;

    /**
     * @var string
     */
    protected $prompt;

    /**
     * @var string
     */
    protected $hostedDomain;

    /**
     * @var string
     */
    protected $openIdRealm;

    /**
     * @var string
     */
    protected $hd;

    /**
     * @var array Scopes d'accès
     */
    protected $defaultScopes = [
        'email',
        'profile',
        'openid',
    ];

    /**
     * @var string URL d'autorisation
     */
    protected $authorizationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * @var string URL pour obtenir un token
     */
    protected $tokenUrl = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     * @var string URL pour le endpoint userinfo
     */
    protected $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';

    /**
     * @var string URL pour révoquer un token
     */
    protected $revokeTokenUrl = 'https://accounts.google.com/o/oauth2/revoke';

    /**
     * Constructor personnalisé
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        // Configuration SSL désactivée
        if (!isset($collaborators['httpClient'])) {
            $collaborators['httpClient'] = new Client([
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 30
            ]);
        }
        
        // Options personnalisées
        $this->accessType = $options['accessType'] ?? null;
        $this->prompt = $options['prompt'] ?? null;
        $this->hostedDomain = $options['hostedDomain'] ?? null;
        $this->openIdRealm = $options['openIdRealm'] ?? null;
        $this->hd = $options['hd'] ?? null;
        
        parent::__construct($options, $collaborators);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->tokenUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->userInfoUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationParameters(array $options)
    {
        // Récupérer les paramètres de base
        $baseParams = parent::getAuthorizationParameters($options);
        
        // Ajouter les paramètres spécifiques à Google
        $params = array_merge(
            $baseParams,
            [
                'access_type' => $this->accessType,
                'prompt' => $this->prompt,
                'hd' => $this->hd ?: $this->hostedDomain,
                'openid.realm' => $this->openIdRealm
            ]
        );
        
        // Désactiver la vérification d'état en modifiant l'état
        if (isset($params['state'])) {
            $params['state'] = 'dev_' . $params['state'];
        }
        
        // Supprimer les paramètres nuls
        return array_filter($params, function ($value) {
            return $value !== null;
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $code = 0;
            $error = $data['error'];

            if (is_array($error)) {
                $code = $error['code'] ?? 0;
                $error = $error['message'] ?? $error['error'];
            }

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GoogleUser($response);
    }

    /**
     * Surcharge pour les requêtes
     */
    protected function prepareRequest($method, $url, $token, array $options)
    {
        $request = parent::prepareRequest($method, $url, $token, $options);
        
        // Ajouter des en-têtes personnalisés
        return $request->withHeader('Accept', 'application/json')
                      ->withHeader('User-Agent', 'SmartWaste-Symfony-App');
    }

    /**
     * Override pour désactiver la vérification d'état en mode développement
     */
    protected function verifyState($state, $storedState)
    {
        // En mode développement, on accepte tous les états
        // IMPORTANT: Cette désactivation est temporaire et pour le développement uniquement
        return true; // Accepter tous les états pour le développement
    }
}

/**
 * Classe utilisateur Google basique (comme ResourceOwner)
 */
class GoogleUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['sub'] ?? null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?? null;
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->response['given_name'] ?? null;
    }

    /**
     * Get user last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['family_name'] ?? null;
    }

    /**
     * Get user avatar
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->response['picture'] ?? null;
    }

    /**
     * Get user data as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
} 