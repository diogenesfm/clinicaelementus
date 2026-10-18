<?php
/**
 * Login do painel do blog (Decap CMS) com GitHub — passo 2: recebe o código e devolve o token ao painel.
 */
$cfgFile = dirname(__DIR__, 2) . '/elementus-oauth-config.php';
if (!is_file($cfgFile)) { http_response_code(500); exit('Configuração do login não encontrada.'); }
$cfg = require $cfgFile;
session_start();
$status = 'error';
$payload = ['message' => 'Falha no login'];
if (!empty($_GET['code']) && !empty($_GET['state']) && hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'])) {
  $ch = curl_init('https://github.com/login/oauth/access_token');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_POSTFIELDS => http_build_query([
      'client_id' => $cfg['client_id'],
      'client_secret' => $cfg['client_secret'],
      'code' => $_GET['code'],
      'redirect_uri' => 'https://clinicaelementus.com/oauth/callback.php',
    ]),
  ]);
  $res = json_decode(curl_exec($ch), true);
  curl_close($ch);
  if (!empty($res['access_token'])) { $status = 'success'; $payload = ['token' => $res['access_token'], 'provider' => 'github']; }
}
unset($_SESSION['oauth_state']);
$msg = 'authorization:github:' . $status . ':' . json_encode($payload);
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>Login</title></head><body>
<p>Concluindo login…</p>
<script>
(function () {
  var origin = 'https://clinicaelementus.com';
  function receive(e) {
    if (e.origin !== origin) return;
    window.opener.postMessage(<?php echo json_encode($msg); ?>, origin);
    window.removeEventListener('message', receive, false);
  }
  window.addEventListener('message', receive, false);
  window.opener.postMessage('authorizing:github', origin);
})();
</script>
</body></html>
