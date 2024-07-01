<?php

function encryptCookie($value): string
{
    $key = 'BcuIght/79AqsÜ??ßßnbgthrj-;'; // Ändern Sie dies zu einem sicheren Schlüssel
    $cipher = "aes-256-cbc";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($value, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}
function decryptCookie($cookie): false|string
{
    $key = 'BcuIght/79AqsÜ??ßßnbgthrj-;'; // Verwenden Sie denselben Schlüssel wie beim Verschlüsseln
    list($encrypted_data, $iv) = explode('::', base64_decode($cookie), 2);
    return openssl_decrypt($encrypted_data, "aes-256-cbc", $key, 0, $iv);
}
?>