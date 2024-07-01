<?php

function encryptData(string $data, string $key): string
{
    $cipher = "aes-256-cbc";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData(string $encryptedData, string $key): string|false
{
    $cipher = "aes-256-cbc";
    list($encrypted, $iv) = explode('::', base64_decode($encryptedData), 2);
    return openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
}

function encryptCookie(string $value): string
{
    $key = 'BcuIght/79AqsÜ??ßßnbgthrj-;'; // Change this to a secure key
    return encryptData($value, $key);
}

function decryptCookie(string $cookie): string|false
{
    $key = 'BcuIght/79AqsÜ??ßßnbgthrj-;'; // Use the same key as for encryption
    return decryptData($cookie, $key);
}
?>