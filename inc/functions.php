<?php
function webdav_request($method, $url, $user, $pass, $headers = [], $body = null) {
    if (!is_array($headers)) {
        $headers = [];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => "$user:$pass",
        CURLOPT_HTTPAUTH       => CURLAUTH_ANY,   // <– wichtig für PHP 8/cURL
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,           // Redirects folgen
        CURLOPT_TIMEOUT        => 15,
        // SSL: zum Debuggen unsicher, später auf true stellen
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    if (strtoupper($method) === 'HEAD') {
        curl_setopt($ch, CURLOPT_NOBODY, true);
    }

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['status' => 0, 'body' => '', 'error' => $error];
    }

    $headerSize = $info['header_size'];
    $respBody   = substr($response, $headerSize);

    curl_close($ch);

    return ['status' => $info['http_code'], 'body' => $respBody];
}

function webdav_list($url, $user, $pass, $depth = 1) {
    $headers = [
        'Depth: ' . $depth,
        'Content-Type: application/xml',
    ];

    $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<d:propfind xmlns:d="DAV:">
  <d:allprop/>
</d:propfind>
XML;

    $res = webdav_request('PROPFIND', $url, $user, $pass, $headers, $xml);
    return $res;
}

function webdav_upload($url, $user, $pass, $localPath) {
    $body = file_get_contents($localPath);
    return webdav_request('PUT', $url, $user, $pass, [], $body);
}

function webdav_delete($url, $user, $pass) {
    return webdav_request('DELETE', $url, $user, $pass);
}

function webdav_mkdir($url, $user, $pass) {
    return webdav_request('MKCOL', $url, $user, $pass);
}

function webdav_move($sourceUrl, $targetUrl, $user, $pass) {
    $headers = ['Destination: ' . $targetUrl, 'Overwrite: F'];
    return webdav_request('MOVE', $sourceUrl, $user, $pass, $headers);
}

function webdav_copy($sourceUrl, $targetUrl, $user, $pass) {
    $headers = ['Destination: ' . $targetUrl, 'Overwrite: F'];
    return webdav_request('COPY', $sourceUrl, $user, $pass, $headers);
}

function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}
