<?php

/**
 * PHP script to generate a PlantUML (.puml) class diagram for the secure login system.
 * - Includes AuthController, UserAuthenticator, FinanceOfficer, and Manager classes
 * - Includes Symfony and Doctrine components that these files interact with
 * - Outputs class names, properties, methods, and UML relationships: dependencies, uses, extends, implements
 * - Stacks root elements vertically for better readability on narrow screens
 * - Excludes anything from the DataFixtures folder
 */

$rootDir = realpath(__DIR__ . '/../src');
$outputFile = __DIR__ . '/structure-security.puml';

$classes = [];
$keywords = ['AuthController', 'UserAuthenticator', 'FinanceOfficer', 'Manager'];

function matchesKeyword($text, $keywords) {
    foreach ($keywords as $keyword) {
        if (stripos($text, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

function parsePhpFile($filePath) {
    $namespace = '';
    $className = '';
    $properties = [];
    $methods = [];
    $uses = [];
    $dependencies = [];
    $extends = null;
    $implements = [];

    $lines = file($filePath);
    foreach ($lines as $line) {
        if (preg_match('/^namespace (.*?);/', $line, $matches)) {
            $namespace = trim($matches[1]);
        }
        if (preg_match('/^use (.*?);/', $line, $matches)) {
            $uses[] = trim($matches[1]);
        }
        if (preg_match('/class (\w+)(?: extends (\w+))?(?: implements ([\w\\, ]+))?/', $line, $matches)) {
            $className = $matches[1];
            if (!empty($matches[2])) {
                $extends = trim($matches[2]);
            }
            if (!empty($matches[3])) {
                $implements = array_map('trim', explode(',', $matches[3]));
            }
        }
        if (preg_match('/function __construct\((.*?)\)/', $line, $matches)) {
            preg_match_all('/\\?(\w+\\[\w\\]+)\s+\$\w+/', $matches[1], $deps);
            if (!empty($deps[1])) {
                foreach ($deps[1] as $dep) {
                    $dependencies[] = $dep;
                }
            }
        }
        if (preg_match('/public \$(\w+)/', $line, $matches)) {
            $properties[] = '+$' . $matches[1];
        }
        if (preg_match('/public function (\w+)/', $line, $matches)) {
            $methods[] = '+' . $matches[1] . '()';
        }
    }

    if ($className) {
        $fqcn = $namespace ? "$namespace\\$className" : $className;
        return [
            'name' => $fqcn,
            'props' => $properties,
            'methods' => $methods,
            'uses' => $uses,
            'deps' => $dependencies,
            'extends' => $extends,
            'implements' => $implements
        ];
    }
    return null;
}

function scanDirectory($dir, $keywords) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir() || $file->getExtension() !== 'php') continue;

        $path = $file->getPathname();
        if (stripos($path, 'DataFixtures') !== false) continue; // Exclude fixtures

        if (!matchesKeyword($path, $keywords)) continue;

        $classInfo = parsePhpFile($path);
        if ($classInfo && matchesKeyword($classInfo['name'], $keywords)) {
            global $classes;
            $classes[$classInfo['name']] = $classInfo;
        }
    }
}

scanDirectory($rootDir, $keywords);

$fp = fopen($outputFile, 'w');
fwrite($fp, "@startuml\nskinparam classAttributeIconSize 0\nleft to right direction\n\n");

foreach ($classes as $class => $info) {
    fwrite($fp, "class \"$class\" {\n");
    foreach ($info['props'] as $prop) {
        fwrite($fp, "  $prop\n");
    }
    foreach ($info['methods'] as $method) {
        fwrite($fp, "  $method\n");
    }
    fwrite($fp, "}\n\n");
}

foreach ($classes as $class => $info) {
    if (!empty($info['extends'])) {
        fwrite($fp, "\"$class\" --|> \"{$info['extends']}\"\n");
    }
    foreach ($info['implements'] as $interface) {
        fwrite($fp, "\"$class\" ..|> \"$interface\"\n");
    }
    foreach ($info['deps'] as $dep) {
        fwrite($fp, "\"$class\" --> \"$dep\"\n");
    }
    foreach ($info['uses'] as $used) {
        fwrite($fp, "\"$class\" ..> \"$used\" : uses\n");
    }
}

fwrite($fp, "@enduml\n");
fclose($fp);

echo "✅ Security UML file generated: structure-security.puml\n";