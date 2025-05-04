<?php

// Désactiver la vérification SSL en développement local uniquement
if (getenv('APP_ENV') === 'dev') {
    // Définir le chemin d'accès au certificat CA
    ini_set('curl.cainfo', __DIR__ . '/cacert.pem');
    
    // Si vous avez des problèmes avec les certificats, vous pouvez désactiver complètement la vérification
    // Code activé pour résoudre les problèmes de certificats persistants
    if (!defined('CURLOPT_SSL_VERIFYPEER')) {
        define('CURLOPT_SSL_VERIFYPEER', 32);
    }
    if (!defined('CURLOPT_SSL_VERIFYHOST')) {
        define('CURLOPT_SSL_VERIFYHOST', 81);
    }
    // Configuration globale pour le contexte de flux
    stream_context_set_default([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
}

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
