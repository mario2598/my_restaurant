<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FactuXService
{
    // URL base de la API de FactuX
    private const BASE_URL = 'https://www.api-factux.spacesoftwarecr.com';
    
    // Credenciales de autenticación
    private const EMAIL = 'mario.flores2598@gmail.com';
    private const PASSWORD = 'factuxstage';
    
    // Cache key para el token
    private const TOKEN_CACHE_KEY = 'factux_auth_token';
    private const TOKEN_CACHE_TTL = 86400; // 24 horas en segundos (el token expira en 24 horas)
    
    /**
     * Obtiene el token de autenticación (desde cache o renovándolo)
     */
    public function getAuthToken()
    {
        // Intentar obtener el token del cache
        $token = Cache::get(self::TOKEN_CACHE_KEY);
        
        if ($token) {
            return $token;
        }
        
        // Si no hay token en cache, obtener uno nuevo
        return $this->obtenerNuevoToken();
    }
    
    /**
     * Obtiene un nuevo token de autenticación desde la API
     */
    private function obtenerNuevoToken()
    {
        $endpoint = self::BASE_URL . '/api/v1/auth/login';
        
        $payload = [
            'correo' => self::EMAIL,
            'password' => self::PASSWORD
        ];
        
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $curlError = curl_error($ch);
            curl_close($ch);
            
            throw new \Exception('Error al obtener token de autenticación: ' . $curlError);
        }
        
        curl_close($ch);
        
        if ($httpCode == 200 || $httpCode == 201) {
            $resultado = json_decode($response, true);
            
            // El token viene en el campo 'token' según la documentación
            $token = $resultado['token'] ?? null;
            
            if (!$token) {
                throw new \Exception('Token no encontrado en la respuesta de FactuX');
            }
            
            // Obtener tiempo de expiración si está disponible (en milisegundos, convertir a segundos)
            $expiraEn = $resultado['expiraEn'] ?? null;
            $ttl = self::TOKEN_CACHE_TTL; // Por defecto 24 horas
            
            if ($expiraEn) {
                // Convertir de milisegundos a segundos y restar 5 minutos de margen
                $ttl = (int)($expiraEn / 1000) - 300; // Restar 5 minutos (300 segundos) como margen de seguridad
                if ($ttl < 60) {
                    $ttl = 60; // Mínimo 1 minuto
                }
            }
            
            // Guardar el token en cache
            Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);
            
            return $token;
        } else {
            $errorData = json_decode($response, true);
            
            throw new \Exception('Error al autenticar con FactuX (HTTP ' . $httpCode . '): ' . ($errorData['message'] ?? $response));
        }
    }
    
    /**
     * Realiza una petición autenticada a la API de FactuX
     * 
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $endpoint Endpoint relativo (ej: '/ElectricPosWs/wsPos/ComprobanteExterno')
     * @param array|null $payload Datos a enviar (para POST/PUT)
     * @return array Respuesta de la API
     */
    public function hacerPeticion($method, $endpoint, $payload = null)
    {
        // Si el endpoint ya es una URL completa, usarla directamente
        if (strpos($endpoint, 'http://') === 0 || strpos($endpoint, 'https://') === 0) {
            $url = $endpoint;
        } else {
            // Asegurar que el endpoint empiece con /
            if (!empty($endpoint) && $endpoint[0] !== '/') {
                $endpoint = '/' . $endpoint;
            }
            $url = self::BASE_URL . $endpoint;
        }
        
        // Obtener token de autenticación
        $token = $this->getAuthToken();
        
        // Preparar headers
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $ch = curl_init($url);
        
        // Configurar método HTTP
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        // Si hay payload, agregarlo
        if ($payload !== null) {
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            $headers[] = 'Content-Length: ' . strlen($jsonPayload);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            return [
                'exito' => false,
                'mensaje' => 'Error al ejecutar petición cURL (código ' . $curlErrno . '): ' . $curlError,
                'http_code' => 0
            ];
        }
        
        curl_close($ch);
        
        // Decodificar respuesta
        $resultado = json_decode($response, true);
        
        // Si el token expiró (401), intentar renovarlo y reintentar
        if ($httpCode == 401) {
            // Limpiar token del cache
            Cache::forget(self::TOKEN_CACHE_KEY);
            
            // Obtener nuevo token
            try {
                $newToken = $this->obtenerNuevoToken();
                
                // Reintentar la petición con el nuevo token
                return $this->hacerPeticion($method, $endpoint, $payload);
            } catch (\Exception $e) {
                return [
                    'exito' => false,
                    'mensaje' => 'Error de autenticación: ' . $e->getMessage(),
                    'http_code' => $httpCode
                ];
            }
        }
        
        return [
            'exito' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'respuesta' => $resultado,
            'raw_response' => $response
        ];
    }
    
    /**
     * Realiza una petición GET autenticada
     */
    public function get($endpoint)
    {
        return $this->hacerPeticion('GET', $endpoint);
    }
    
    /**
     * Realiza una petición POST autenticada
     */
    public function post($endpoint, $payload)
    {
        return $this->hacerPeticion('POST', $endpoint, $payload);
    }
    
    /**
     * Realiza una petición PUT autenticada
     */
    public function put($endpoint, $payload)
    {
        return $this->hacerPeticion('PUT', $endpoint, $payload);
    }
    
    /**
     * Realiza una petición DELETE autenticada
     */
    public function delete($endpoint)
    {
        return $this->hacerPeticion('DELETE', $endpoint);
    }
    
    /**
     * Limpia el token del cache (útil para forzar renovación)
     */
    public function limpiarToken()
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }
}

