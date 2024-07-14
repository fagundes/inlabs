<?php
$login = "email@dominio.com";
$senha = "minha_senha";

$tipo_dou = "DO1 DO2 DO3 DO1E DO2E DO3E"; // Seções separadas por espaço

$url_login = "https://inlabs.in.gov.br/logar.php";
$url_download = "https://inlabs.in.gov.br/index.php?p=";

$payload = [
    'email' => $login,
    'password' => $senha
];
$headers = [
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
];

function download($session_cookie) {
    global $tipo_dou, $url_download;

    $data_completa = date('Y-m-d');

    $tipo_dou_array = explode(' ', $tipo_dou);
    foreach ($tipo_dou_array as $dou_secao) {
        echo "Aguarde Download...\n";
        $url_arquivo = $url_download . $data_completa . "&dl=" . $data_completa . "-" . $dou_secao . ".zip";
        $cabecalho_arquivo = [
            'Cookie: inlabs_session_cookie=' . $session_cookie,
            'origem: 736372697074'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_arquivo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $cabecalho_arquivo);
        $response_arquivo = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            file_put_contents($data_completa . "-" . $dou_secao . ".zip", $response_arquivo);
            echo "Arquivo " . $data_completa . "-" . $dou_secao . ".zip salvo.\n";
        } elseif ($http_code == 404) {
            echo "Arquivo não encontrado: " . $data_completa . "-" . $dou_secao . ".zip\n";
        }
    }

    echo "Aplicação encerrada\n";
    exit(0);
}

function login() {
    global $url_login, $payload, $headers;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_login);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        if (preg_match('/inlabs_session_cookie=([^;]+)/', $response, $matches)) {
            $session_cookie = $matches[1];
            download($session_cookie);
        } else {
            echo "Falha ao obter cookie. Verifique suas credenciais\n";
            exit(37);
        }
    } else {
        echo "Falha ao conectar. Tentando novamente...\n";
        login();
    }
}

login();
?>
