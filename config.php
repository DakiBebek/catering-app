<?php
$lifetime = 30 * 24 * 3600;
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'httponly'  => true,
    'samesite'  => 'Lax',
]);
session_start();

// ══════════════════════════════════════
// ⚠️ GANTI DENGAN PUNYA KAMU!
// ══════════════════════════════════════
$SUPABASE_URL = 'https://czmynuwrbxcgrlnhfwjx.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImN6bXludXdyYnhjZ3Jsbmhmd2p4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODkzNDk4NDcsImV4cCI6MjEwNDkyNTg0N30.7S9cSRYBeqYFNUVy7f2o7DiEfvgMZDdpm7uW0QqnwGA';
// ══════════════════════════════════════

// ══════════════════════════════════════
// FUNGSI CURL UNTUK SUPABASE REST API
// ══════════════════════════════════════

function supabaseRequest($method, $table, $data = null, $query = '') {
    global $SUPABASE_URL, $SUPABASE_KEY;
    
    $url = rtrim($SUPABASE_URL, '/') . '/rest/v1/' . $table;
    if ($query) {
        $url .= '?' . $query;
    }
    
    $ch = curl_init($url);
    
    $headers = [
        'apikey: ' . $SUPABASE_KEY,
        'Authorization: Bearer ' . $SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($response, true);
}

function supabaseSelect($table, $query = '') {
    return supabaseRequest('GET', $table, null, $query);
}

function supabaseInsert($table, $data) {
    return supabaseRequest('POST', $table, $data);
}

function supabaseUpdate($table, $data, $query) {
    return supabaseRequest('PATCH', $table, $data, $query);
}

function supabaseDelete($table, $query) {
    return supabaseRequest('DELETE', $table, null, $query);
}

// Auto-login kalau ada remember token
require_once __DIR__ . '/auth.php';
autoLoginIfRemembered();