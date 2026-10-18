<?php
/**
 * Login do painel do blog (Decap CMS) com GitHub — passo 1: redireciona para o GitHub.
 * As credenciais ficam em um arquivo FORA da pasta pública (ver config.example.php).
 */
$cfgFile = dirname(__DIR__, 2) . '/elementus-oauth-config.php';
if (!is_file($cfgFile)) { http_response_code(500); exit('Configuração do login não encontrada.'); }
$cfg = require $cfgFile;
session_start();
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$params = http_build_query([
  'client_id' => $cfg['client_id'],
  'redirect_uri' => 'https://clinicaelementus.com/oauth/callback.php',
  'scope' => 'repo,user',
  'state' => $state,
]);
header('Location: https://github.com/login/oauth/authorize?' . $params);
