<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SSLVerificationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255], // Priorité maximale
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Ne rien faire en production
        if ($_SERVER['APP_ENV'] !== 'dev') {
            return;
        }

        // Désactiver la vérification SSL pour cURL
        $this->disableSSLVerification();
    }

    private function disableSSLVerification(): void
    {
        // Définir le chemin d'accès au certificat CA
        $cacertPath = dirname(__DIR__, 2) . '/public/cacert.pem';
        ini_set('curl.cainfo', $cacertPath);
        
        // Configurer la vérification SSL pour les contextes de flux
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ],
        ]);
        
        // Pour PHP 7.0+ et les applications qui utilisent Guzzle
        if (!isset($GLOBALS['_DISABLE_SSL_SET'])) {
            $GLOBALS['_DISABLE_SSL_SET'] = true;
            
            // Définir la variable d'environnement pour cURL
            putenv('CURL_CA_BUNDLE=');
            
            // Désactiver la vérification du certificat pour les clients HTTP
            if (class_exists('\GuzzleHttp\Client')) {
                $GLOBALS['GUZZLE_SSL_VERIFY'] = false;
            }
        }
    }
} 