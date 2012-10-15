<?php

require 'CoffeeScript/Init.php';

function catdir($a, $b) {
    $a = preg_replace('/\/+$/', '', $a);
    $b = preg_replace('/^\/+/', '', $b);
    return $a . '/' . $b;
}

function comment() {
    $lines = func_get_args();
    echo "/**\n";
    foreach ( $lines as $line ) {
        echo " * $line\n";
    }
    echo "**/\n";
}

function lock_file($path) {
    if ( file_exists($path) ) {
        $perms = fileperms($path);
        $perms &= 0555;
        chmod($path, $perms);
    }
}

function main() {
    header('Content-Type: application/javascript');
    $document_root = $_SERVER['DOCUMENT_ROOT'];

    if ( isset($_GET['js']) ) {
        $request_js = $_GET['js'];
    } else if ( isset($_SERVER['PATH_INFO']) ) {
        $request_js = $_SERVER['PATH_INFO'];
    }

    $lock_js = true;
    if ( isset($_GET['lock']) ) {
        $lock_js = preg_match('/^(no|off|false|0)$/i', $_GET['lock'])? false: true;
    }

    if ( isset($request_js) ) {
        header("x-autocoffee-request-js: $request_js");
    }

    $request_js_pi = pathinfo($request_js);
    if ( !isset($request_js)
      || substr($request_js, 0, 1) != '/'
      || $request_js_pi['extension'] != 'js'
      || $request_js_pi['basename'] == $request_js_pi['extension'] ) {

        // Invalid request
        $file = explode(DIRECTORY_SEPARATOR, __FILE__);
        $file = array_pop($file);
        $js = isset($request_js)? $request_js: '';
        return comment(
            'Usage:',
            "$file?js=/URL-FULL-PATH/TO/COFFEE-FILE-NAME.js",
            'or',
            "$file/URL-FULL-PATH/TO/COFFEE-FILE-NAME.js",
            "Requested: $js"
        );
    }

    // Normalize paths
    $dir_path = $request_js_pi['dirname'];
    $dir_full_path = catdir($document_root, $dir_path);

    $js_basename = $request_js_pi['basename'];
    $request_min = preg_match('/[\-\.]min\.js$/i', $js_basename);
    $filename = preg_replace('/([\-\.]min)?\.js$/i', '', $js_basename);

    $coffee_basename = $filename . '.js.coffee';
    $js_basename = $filename . '.js';
    $js_min_basename = $filename . '.min.js';

    $coffee_full_path = catdir($dir_full_path, $coffee_basename);
    $js_full_path = catdir($dir_full_path, $js_basename);
    $js_min_full_path = catdir($dir_full_path, $js_min_basename);

    // Check existence
    if ( !file_exists($coffee_full_path) ) {
        if ( file_exists($js_full_path) ) {
            readfile($js_full_path);
            return;
        }

        if ( file_exists($js_min_full_path) ) {
            readfile($js_full_path);
            return;
        }

        header('HTTP/1.1 404 Not Found');
        return;
    }


    // Parse COFFEE
    try {
        CoffeeScript\Init::load();

        $coffee = file_get_contents($coffee_full_path);
        $source = CoffeeScript\Compiler::compile($coffee, array('filename' => $coffee_full_path));;
        @unlink($js_full_path);
        file_put_contents($js_full_path, $source);
        if ( $lock_js ) lock_file($js_full_path);

        // min
        // @unlink($js_min_full_path);
        // file_put_contents($js_min_full_path, $source);
        // if ( $lock_js ) lock_file($js_min_full_path);

        // Release source
        $source = null;
    } catch(Exctption $ex) {
        return comment(
            'Coffee fatal error:',
            $ex->getMessage()
        );
    }

    // Output
    if ( $request_min ) {
        readfile($js_min_full_path);
    } else {
        readfile($js_full_path);
    }
}

main();
