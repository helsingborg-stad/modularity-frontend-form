<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

/** Test-only loopback server; consumes public-handler bytes, without WordPress or a receiver. */
final class PhpMultipartParser
{
    public static function parse(array $request): array
    {
        $directory = sys_get_temp_dir() . '/v3-parser-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        $address = '127.0.0.1:' . (getenv('SENDER_PARSER_PORT') ?: '18733');
        $reservation = @stream_socket_server('tcp://' . $address, $errno, $error);
        if ($reservation === false) {
            rmdir($directory);
            throw new \RuntimeException('Parser port is already in use; select an isolated port.');
        }
        fclose($reservation);
        $process = proc_open([PHP_BINARY, '-d', 'upload_tmp_dir=' . $directory,
            '-d', 'post_max_size=12M', '-d', 'upload_max_filesize=8M', '-S', $address,
            __DIR__ . '/multipart-parser.php'], [0 => ['pipe', 'r'],
            1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes);
        if (!is_resource($process)) {
            throw new \RuntimeException('Could not start the isolated PHP parser.');
        }
        fclose($pipes[0]);
        $curl = curl_init('http://' . $address);
        try {
            curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 1, CURLOPT_TIMEOUT => 5]);
            $ready = false;
            for ($attempt = 0; $attempt < 40; $attempt++) {
                if (!proc_get_status($process)['running']) {
                    throw new \RuntimeException('Parser port is unavailable; no existing process was changed.');
                }
                $probe = curl_exec($curl);
                if ($probe !== false && (json_decode($probe, true)['pid'] ?? null) === proc_get_status($process)['pid']) {
                    $ready = true;
                    break;
                }
                usleep(50000);
            }
            if (!$ready) {
                throw new \RuntimeException('PHP parser did not become ready.');
            }
            $headers = [];
            foreach ($request['headers'] as $name => $value) {
                $headers[] = "$name: $value";
            }
            curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $request['body'], CURLOPT_HTTPHEADER => $headers]);
            $response = curl_exec($curl);
            if ($response === false || curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
                throw new \RuntimeException('PHP multipart parsing request failed.');
            }
            return json_decode($response, true, flags: JSON_THROW_ON_ERROR);
        } finally {
            curl_close($curl);
            proc_terminate($process);
            fclose($pipes[1]);
            proc_close($process);
            rmdir($directory);
        }
    }
}
