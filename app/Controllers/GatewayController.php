<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class GatewayController extends BaseController
{
    private string $gatewayUrl = 'http://127.0.0.1:3001';

    public function index(Request $request): Response
    {
        return $this->render('gateway/index', [
            'pageTitle' => 'Link WhatsApp Device (QR Gateway) - WACM',
        ], 'layouts/main');
    }

    public function status(Request $request): Response
    {
        $ch = curl_init($this->gatewayUrl . '/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($body)) {
            $data = json_decode($body, true);
            return $this->json($data);
        }

        return $this->json([
            'status' => 'offline',
            'error' => 'Gateway daemon is starting or offline on port 3001.',
        ]);
    }

    public function disconnect(Request $request): Response
    {
        $ch = curl_init($this->gatewayUrl . '/disconnect');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $body = curl_exec($ch);
        curl_close($ch);

        return $this->json(['success' => true, 'message' => 'Disconnected successfully.']);
    }
}
