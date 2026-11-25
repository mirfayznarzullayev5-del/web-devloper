<?php
function users_file(): string {
    $d = __DIR__ . '/storage';
    if (!is_dir($d)) mkdir($d, 0755, true);
    return $d . '/users.json';
}
function users_all(): array {
    $f = users_file();
    if (!is_file($f)) return ['users'=>[], 'tokens'=>[]];
    $j = json_decode((string)file_get_contents($f), true);
    return is_array($j) ? $j + ['users'=>[], 'tokens'=>[]] : ['users'=>[], 'tokens'=>[]];
}
function users_save(array $d): void {
    file_put_contents(users_file(), json_encode($d, JSON_UNESCAPED_UNICODE));
}
function pending_file($id): string {
    $d = __DIR__ . '/storage/pending';
    if (!is_dir($d)) mkdir($d, 0755, true);
    return $d . '/' . preg_replace('/[^a-zA-Z0-9_\-]/','', $id) . '.json';
}

function tgmap_file(){ return __DIR__.'/storage/tgmap.json'; }
function tgmap_all(){ $f=tgmap_file(); if(!is_file($f)) return []; $j=json_decode((string)file_get_contents($f),true); return is_array($j)?$j:[]; }
function tgmap_set($username, $chatId){
    $username = strtolower(ltrim((string)$username,'@'));
    if ($username==='' || $chatId==='') return;
    $m = tgmap_all(); $m[$username]=(string)$chatId;
    if(!is_dir(__DIR__.'/storage')) mkdir(__DIR__.'/storage',0755,true);
    file_put_contents(tgmap_file(), json_encode($m, JSON_UNESCAPED_UNICODE));
}
function tgmap_get($username){
    $username = strtolower(ltrim((string)$username,'@'));
    $m = tgmap_all();
    return $m[$username] ?? '';
}
