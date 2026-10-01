<?php
// Run from the package root: php tools/build-snippet.php
$root = dirname(__DIR__);
$core = file_get_contents($root . '/includes/class-post-author-taxonomy.php');
file_put_contents($root . '/post-author-taxonomy.snippet.php', $core);
$code = preg_replace('/^<\?php\s*/', '', $core);
file_put_contents($root . '/ddw-post-author-taxonomy.code-snippets.json', json_encode(['generator'=>'Post Author Taxonomy 1.3.0','date'=>'2026-10-01','snippets'=>[['name'=>'DDW Post Author Taxonomy','code'=>$code,'scope'=>'global','active'=>false,'description'=>'Taxonomy and shortcodes only. Do not activate alongside the plugin.']]], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
