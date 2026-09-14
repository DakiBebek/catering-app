<?php

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
    ];
}

function registerUser($username, $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $check = supabaseSelect('users', 'username=eq.' . urlencode($username));
    if (!empty($check)) {
        return ['ok' => false, 'msg' => 'Username sudah dipakai!'];
    }

    supabaseInsert('users', [
        'username' => $username,
        'password' => $hash,
    ]);

    return ['ok' => true, 'msg' => 'Berhasil daftar!'];
}

function loginUser($username, $password, $rememberMe = false) {
    $users = supabaseSelect('users', 'username=eq.' . urlencode($username));
    
    if (empty($users)) {
        return ['ok' => false, 'msg' => 'Username tidak ditemukan!'];
    }
    
    $user = $users[0];

    if (!password_verify($password, $user['password'])) {
        return ['ok' => false, 'msg' => 'Password salah!'];
    }

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];

    if ($rememberMe) {
        createRememberToken($user['id']);
    }

    return ['ok' => true, 'msg' => 'Login berhasil!'];
}

function createRememberToken($userId) {
    $rawToken  = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    supabaseDelete('remember_tokens', 'user_id=eq.' . $userId);

    supabaseInsert('remember_tokens', [
        'user_id'    => $userId,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
    ]);

    setcookie('remember_token', $rawToken, [
        'expires'  => strtotime('+30 days'),
        'path'     => '/',
        'httponly'  => true,
        'samesite'  => 'Lax',
    ]);
}

function autoLoginIfRemembered() {
    if (isLoggedIn()) return;
    if (empty($_COOKIE['remember_token'])) return;

    $rawToken  = $_COOKIE['remember_token'];
    $tokenHash = hash('sha256', $rawToken);

    $tokens = supabaseSelect('remember_tokens', 
        'token_hash=eq.' . urlencode($tokenHash) . '&select=*,users(id,username)');

    if (empty($tokens)) {
        unsetCookie();
        return;
    }

    $row = $tokens[0];

    if (strtotime($row['expires_at']) < time()) {
        supabaseDelete('remember_tokens', 'id=eq.' . $row['id']);
        unsetCookie();
        return;
    }

    $_SESSION['user_id']  = $row['users']['id'];
    $_SESSION['username'] = $row['users']['username'];

    $newExpiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    supabaseUpdate('remember_tokens', ['expires_at' => $newExpiry], 'id=eq.' . $row['id']);
}

function unsetCookie() {
    setcookie('remember_token', '', time() - 3600, '/');
}

function logoutUser() {
    if (isset($_SESSION['user_id'])) {
        supabaseDelete('remember_tokens', 'user_id=eq.' . $_SESSION['user_id']);
    }
    unsetCookie();
    session_destroy();
}