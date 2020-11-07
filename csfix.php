<?php
if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('Test can run only from command line.');
        }

        

function fixit(string $phpfile): string {
    
    $replaces = [
        '/\*\s*\@param\s+(.+)\s+\$([a-zA-Z0-9_]+)\s*\n/' => '* @param $1 \$$2 $2\n',
        '/\barray<([a-zA-Z0-9_\[\]]+)>/' => '$1[]',
        '/\/\*\*[\s*\*]*\@([.\s\*\@\w\d_\-\[\]\$\|\<\>\\\\]*\/\s*)(public|protected|private) function ([\w\d_]+)\(/' => '/**\n     * Method $3\n     * @$1$2 function $3(',
        '/([\w\d_])\n     \* @return/' => '$1\n     *\n     * @return',
        '/\bprivate\b/' => 'protected',
        '/<\?php declare\(strict_types = 1\);\s*namespace ([\w\d_\\\\]+);/' => '<?php declare(strict_types = 1);\n\n/**\n *\n *\n * PHP version 7.4\n *\n * @category  PHP\n * @package   $1\n * @author    Gyula Madarasz <gyula.madarasz@gmail.com>\n * @copyright 2020 Gyula Madarasz\n * @license   Copyright (c) all right reserved.\n * @link      this\n */\n\nnamespace $1;',
        '/;\s*class\s+([\w\d_]+)\s*\{/' => ';\n\n/**\n * $1\n *\n * @category  PHP\n * @package   \n * @author    Gyula Madarasz <gyula.madarasz@gmail.com>\n * @copyright 2020 Gyula Madarasz\n * @license   Copyright (c) all right reserved.\n * @link      this\n */\nclass $1{',
    ];
    
    foreach ($replaces as &$replace) {
        $replace = str_replace('\n', "\n", $replace);
    }
    
    if (false === ($phpcode = file_get_contents($phpfile))) {
        return 'File reading error.';
    }
    while(true) {
        $replaced = preg_replace(array_keys($replaces), array_values($replaces), $phpcode);        
        if (null === $replaced) {
            return 'Regex replace error.';
        }
        if ($replaced === $phpcode) {
            break;
        }
        echo '.';
        $phpcode = $replaced;
    };
    
    if (false === file_put_contents($phpfile, $replaced)) {
        return 'File write error.';
    }
    return '';
}

function csfix($path, $ignores, $includes) {

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
            echo "\nCS Fixing: $pathname ";
            $error = fixit($pathname);
            echo $error ? "ERROR: $error" : 'OK';
        }

    }
}
    
$ignores = ['/\bconfig\b/', '/\.html.php$/'];
$includes = ['/\.php$/'];
csfix('src', $ignores, $includes);
csfix('tests', $ignores, $includes);

echo "\n";
