<?php

namespace Sodium;

if (!function_exists("\\sodium_crypto_box")) {
	printf("lib-sodium is not installed for this php version\n");
	exit(1);
}

function crypto_aead_aes256gcm_is_available(...$p) { return \sodium_crypto_aead_aes256gcm_is_available(...$p); }
function crypto_aead_aes256gcm_decrypt(...$p) { return \sodium_crypto_aead_aes256gcm_decrypt(...$p); }
function crypto_aead_aes256gcm_encrypt(...$p) { return \sodium_crypto_aead_aes256gcm_encrypt(...$p); }
function crypto_aead_aes256gcm_keygen(...$p) { return \sodium_crypto_aead_aes256gcm_keygen(...$p); }
function crypto_aead_chacha20poly1305_decrypt(...$p) { return \sodium_crypto_aead_chacha20poly1305_decrypt(...$p); }
function crypto_aead_chacha20poly1305_encrypt(...$p) { return \sodium_crypto_aead_chacha20poly1305_encrypt(...$p); }
function crypto_aead_chacha20poly1305_keygen(...$p) { return \sodium_crypto_aead_chacha20poly1305_keygen(...$p); }
function crypto_aead_chacha20poly1305_ietf_decrypt(...$p) { return \sodium_crypto_aead_chacha20poly1305_ietf_decrypt(...$p); }
function crypto_aead_chacha20poly1305_ietf_encrypt(...$p) { return \sodium_crypto_aead_chacha20poly1305_ietf_encrypt(...$p); }
function crypto_aead_chacha20poly1305_ietf_keygen(...$p) { return \sodium_crypto_aead_chacha20poly1305_ietf_keygen(...$p); }
function crypto_aead_xchacha20poly1305_ietf_decrypt(...$p) { return \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(...$p); }
function crypto_aead_xchacha20poly1305_ietf_keygen(...$p) { return \sodium_crypto_aead_xchacha20poly1305_ietf_keygen(...$p); }
function crypto_aead_xchacha20poly1305_ietf_encrypt(...$p) { return \sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(...$p); }
function crypto_auth(...$p) { return \sodium_crypto_auth(...$p); }
function crypto_auth_keygen(...$p) { return \sodium_crypto_auth_keygen(...$p); }
function crypto_auth_verify(...$p) { return \sodium_crypto_auth_verify(...$p); }
function crypto_box(...$p) { return \sodium_crypto_box(...$p); }
function crypto_box_keypair(...$p) { return \sodium_crypto_box_keypair(...$p); }
function crypto_box_seed_keypair(...$p) { return \sodium_crypto_box_seed_keypair(...$p); }
function crypto_box_keypair_from_secretkey_and_publickey(...$p) { return \sodium_crypto_box_keypair_from_secretkey_and_publickey(...$p); }
function crypto_box_open(...$p) { return \sodium_crypto_box_open(...$p); }
function crypto_box_publickey(...$p) { return \sodium_crypto_box_publickey(...$p); }
function crypto_box_publickey_from_secretkey(...$p) { return \sodium_crypto_box_publickey_from_secretkey(...$p); }
function crypto_box_seal(...$p) { return \sodium_crypto_box_seal(...$p); }
function crypto_box_seal_open(...$p) { return \sodium_crypto_box_seal_open(...$p); }
function crypto_box_secretkey(...$p) { return \sodium_crypto_box_secretkey(...$p); }
function crypto_kx_keypair(...$p) { return \sodium_crypto_kx_keypair(...$p); }
function crypto_kx_publickey(...$p) { return \sodium_crypto_kx_publickey(...$p); }
function crypto_kx_secretkey(...$p) { return \sodium_crypto_kx_secretkey(...$p); }
function crypto_kx_seed_keypair(...$p) { return \sodium_crypto_kx_seed_keypair(...$p); }
function crypto_kx_client_session_keys(...$p) { return \sodium_crypto_kx_client_session_keys(...$p); }
function crypto_kx_server_session_keys(...$p) { return \sodium_crypto_kx_server_session_keys(...$p); }
function crypto_generichash(...$p) { return \sodium_crypto_generichash(...$p); }
function crypto_generichash_keygen(...$p) { return \sodium_crypto_generichash_keygen(...$p); }
function crypto_generichash_init(...$p) { return \sodium_crypto_generichash_init(...$p); }
function crypto_generichash_update(...$p) { return \sodium_crypto_generichash_update(...$p); }
function crypto_generichash_final(...$p) { return \sodium_crypto_generichash_final(...$p); }
function crypto_kdf_derive_from_key(...$p) { return \sodium_crypto_kdf_derive_from_key(...$p); }
function crypto_kdf_keygen(...$p) { return \sodium_crypto_kdf_keygen(...$p); }
function crypto_pwhash(...$p) { return \sodium_crypto_pwhash(...$p); }
function crypto_pwhash_str(...$p) { return \sodium_crypto_pwhash_str(...$p); }
function crypto_pwhash_str_verify(...$p) { return \sodium_crypto_pwhash_str_verify(...$p); }
function crypto_pwhash_str_needs_rehash(...$p) { return \sodium_crypto_pwhash_str_needs_rehash(...$p); }
function crypto_pwhash_scryptsalsa208sha256(...$p) { return \sodium_crypto_pwhash_scryptsalsa208sha256(...$p); }
function crypto_pwhash_scryptsalsa208sha256_str(...$p) { return \sodium_crypto_pwhash_scryptsalsa208sha256_str(...$p); }
function crypto_pwhash_scryptsalsa208sha256_str_verify(...$p) { return \sodium_crypto_pwhash_scryptsalsa208sha256_str_verify(...$p); }
function crypto_scalarmult(...$p) { return \sodium_crypto_scalarmult(...$p); }
function crypto_secretbox(...$p) { return \sodium_crypto_secretbox(...$p); }
function crypto_secretbox_keygen(...$p) { return \sodium_crypto_secretbox_keygen(...$p); }
function crypto_secretbox_open(...$p) { return \sodium_crypto_secretbox_open(...$p); }
function crypto_secretstream_xchacha20poly1305_keygen(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_keygen(...$p); }
function crypto_secretstream_xchacha20poly1305_init_push(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_init_push(...$p); }
function crypto_secretstream_xchacha20poly1305_push(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_push(...$p); }
function crypto_secretstream_xchacha20poly1305_init_pull(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_init_pull(...$p); }
function crypto_secretstream_xchacha20poly1305_pull(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_pull(...$p); }
function crypto_secretstream_xchacha20poly1305_rekey(...$p) { return \sodium_crypto_secretstream_xchacha20poly1305_rekey(...$p); }
function crypto_shorthash(...$p) { return \sodium_crypto_shorthash(...$p); }
function crypto_shorthash_keygen(...$p) { return \sodium_crypto_shorthash_keygen(...$p); }
function crypto_sign(...$p) { return \sodium_crypto_sign(...$p); }
function crypto_sign_detached(...$p) { return \sodium_crypto_sign_detached(...$p); }
function crypto_sign_ed25519_pk_to_curve25519(...$p) { return \sodium_crypto_sign_ed25519_pk_to_curve25519(...$p); }
function crypto_sign_ed25519_sk_to_curve25519(...$p) { return \sodium_crypto_sign_ed25519_sk_to_curve25519(...$p); }
function crypto_sign_keypair(...$p) { return \sodium_crypto_sign_keypair(...$p); }
function crypto_sign_keypair_from_secretkey_and_publickey(...$p) { return \sodium_crypto_sign_keypair_from_secretkey_and_publickey(...$p); }
function crypto_sign_open(...$p) { return \sodium_crypto_sign_open(...$p); }
function crypto_sign_publickey(...$p) { return \sodium_crypto_sign_publickey(...$p); }
function crypto_sign_secretkey(...$p) { return \sodium_crypto_sign_secretkey(...$p); }
function crypto_sign_publickey_from_secretkey(...$p) { return \sodium_crypto_sign_publickey_from_secretkey(...$p); }
function crypto_sign_seed_keypair(...$p) { return \sodium_crypto_sign_seed_keypair(...$p); }
function crypto_sign_verify_detached(...$p) { return \sodium_crypto_sign_verify_detached(...$p); }
function crypto_stream(...$p) { return \sodium_crypto_stream(...$p); }
function crypto_stream_keygen(...$p) { return \sodium_crypto_stream_keygen(...$p); }
function crypto_stream_xor(...$p) { return \sodium_crypto_stream_xor(...$p); }
function add(...$p) { return \sodium_add(...$p); }
function compare(...$p) { return \sodium_compare(...$p); }
function increment(...$p) { return \sodium_increment(...$p); }
function memcmp(...$p) { return \sodium_memcmp(...$p); }
function memzero(...$p) { return \sodium_memzero(...$p); }
function pad(...$p) { return \sodium_pad(...$p); }
function unpad(...$p) { return \sodium_unpad(...$p); }
function bin2hex(...$p) { return \sodium_bin2hex(...$p); }
function hex2bin(...$p) { return \sodium_hex2bin(...$p); }
function bin2base64(...$p) { return \sodium_bin2base64(...$p); }
function base642bin(...$p) { return \sodium_base642bin(...$p); }
function crypto_scalarmult_base(...$p) { return \sodium_crypto_scalarmult_base(...$p); }
