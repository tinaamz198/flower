<?php
define('ENCRYPTION_KEY', 'a-very-long-secret-key-that-is-32-chars-long!'); 
define('CIPHER', 'aes-256-cbc');

function encryptData($data) {
    if (empty($data)) return $data;
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHER));
    $encrypted = openssl_encrypt($data, CIPHER, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted) . '::' . base64_encode($iv);
}

function decryptData($data) {
    if (empty($data) || strpos($data, '::') === false) return $data;
    
    $parts = explode('::', $data, 2);
    $encrypted_data = base64_decode($parts[0]);
    $iv = base64_decode($parts[1]);
    
    $decrypted = openssl_decrypt($encrypted_data, CIPHER, ENCRYPTION_KEY, 0, $iv);
    return ($decrypted === false) ? $data : $decrypted;
}
?>