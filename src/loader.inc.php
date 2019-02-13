<?php declare(strict_types = 1);

namespace Netmosfera\Opal;

use Closure;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use function Netmosfera\Opal\InternalTools\File\fileRead;
use function Netmosfera\Opal\InternalTools\File\fileRequire;
use function Netmosfera\Opal\InternalTools\File\fileWrite;

function loader(){
    static $instance;

    if($instance !== NULL){
        return $instance;
    }

    $sourceToNodes = function(String $source) use(&$parser): array{
        $parser = $parser ?? (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        return $parser->parse($source);
    };

    $nodesToSource = function(array $tree) use(&$stringifier): String{
        $stringifier = $stringifier ?? new Standard();
        return $stringifier->prettyPrintFile($tree);
    };

    $readFile = function(String $path){
        return fileRead($path, 5.0, 0.0);
    };

    $writeAndImportFile = function(
        String $path, Int $dirMode, Int $fileMode, String $source, Bool $doImportIt
    ){
        $requireFile = !$doImportIt ? NULL : function() use($path){ fileRequire($path); };
        return fileWrite(
            $path, $source, $dirMode, 5.0, 0.0, NULL, NULL, NULL, $requireFile
        );
    };

    $instance = new Loader(
        $sourceToNodes,
        $nodesToSource,
        Closure::fromCallable("Netmosfera\\Opal\\InternalTools\\File\\dirRead"),
        $readFile,
        Closure::fromCallable("Netmosfera\\Opal\\InternalTools\\File\\fileRequire"),
        $writeAndImportFile
    );

    return $instance;
}
