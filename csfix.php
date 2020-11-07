<?php
if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Test can run only from command line.');
        }

        
$ignores = ['/\bconfig\b/', '/\.html.php$/'];
$includes = ['/\.php$/'];
$path = 'src';

function fixit(string $phpfile): string {
    
    $replaces = [
        '\barray<([a-zA-Z0-9_\[\]]+)>' => '$1[]',
        '\*\s*\@param\s+(.+)\s+\$([a-zA-Z0-9_]+)\s*\n' => '* @param $1 \$$2 $2\n',
        '\/\*\*\s*([.\s\*\@\w\d_\-\[\]\$\|\<\>]*\/\s*)(public|protected|private) function ([\w\d_]+)\(' => '/**\n     * Method $3\n     *\n     $1$2 function $3(',
        '([\w\d_])\n     \* @return' => '$1\n     *\n     * @return',
    ];
    
    if (false === ($replaced = $phpcode = file_get_contents($phpfile))) {
        return 'File reading error.';
    }
    while($replaced === $phpcode) {        
        $replaced = preg_replace(array_keys($replaces), array_values($replaces), $phpfile);
        if ($replaced) {
            return 'Regex replace error.';
        }
    }
    if (!file_put_contents($phpfile, $replaced)) {
        return 'File write error.';
    }
    return '';
}
        
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

foreach ($rii as $file) {

    if ($file->isDir()){ 
        continue;
    }
    $pathname = $file->getPathname();
    $included = false;
    foreach ($includes as $include) {
        if (preg_match($include, $pathname)) {
            $included = true;
            break;
        }
    }
    $ignored = false;
    foreach ($ignores as $ignore) {
        if (preg_match($ignore, $pathname)) {
            $ignored = true;
            break;
        }
    }
    if ($included && !$ignored) {
        echo "\nCS Fixing: $pathname ...";
        $error = fixit($pathname);
        echo $error ? "ERROR: $error" : 'OK';
    }

}

echo '\n';
