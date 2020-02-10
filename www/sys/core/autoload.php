<?php
//Автозагрузка классов
function autoload($way)
{
    global $root;
    if (!$root) {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $rootLastCharacter = substr($root, -1);
        if ($rootLastCharacter != '/') $root .= '/';
    }
    $way = ltrim($way, '\\');
    if ($lastNsPos = strrpos($way, '\\')) {
        $wayAR = explode('\\', $way);
        if ($wayAR) {
            krsort($wayAR);
            foreach ($wayAR as $wayPart) {
                if (!$className) $className = $wayPart;
                elseif (!$namespace) $namespace = $wayPart;
                else $namespace = $wayPart . '\\' . $namespace;
            }
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
    }
    if ($className) {
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fl = $root . 'sys/' . $fileName;
        if (strpos($fl, 'sys/module')) $fl = str_replace('sys/module', 'module', $fl);
        if ($className != 'PDO') {
            if (!file_exists($fl)) die('Не найден класс ' . $fl);
            require $fl;
        }
    }
}

spl_autoload_register('autoload');