<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Exception;
use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackageDirectory;

/**
 * Creates a {@see PackageComponent} from a local file path.
 *
 * The directory's path in `$directory` is taken out of `$path`. If `$path` has a
 * different directory prefix, the function will throw an error. The remaining path is
 * split in file-names and each one is checked to be a valid PHP identifier. Anything that
 * appears after the first `.` in the last identifier is collected in
 * {@see PackageComponent::$extension}. If all file-names in the path are valid PHP
 * identifiers a {@see PackageComponent} object is returned, otherwise `NULL`.
 *
 * @param           String $path
 *
 * @param           PackageDirectory $directory
 *
 * @return          PackageComponent|NULL
 *
 * @throws
 */
function componentFromPath(
    PackageDirectory $directory,
    String $path
): ?PackageComponent{

    assert(isNormalizedPath($path));

    if(
        substr($path, 0, $directory->pathLength) !== $directory->path ||
        ($path[$directory->pathLength] !== "/" && $path[$directory->pathLength] !== "\\")
    ){
        throw new Exception("The file is not located in the provided directory");
    }

    $relativePath = substr($path, $directory->pathLength);

    $identifiers = preg_split("@[\\\\/]+@", $relativePath);
    // Remove the first because string starts with one or more directory
    // separators, therefore the first is empty
    array_shift($identifiers);

    $fileName = $identifiers[count($identifiers) - 1];
    $fileNamePieces = explode(".", $fileName, 2);

    $componentIdentifier = $fileNamePieces[0];
    $extension = $fileNamePieces[1] ?? NULL;
    $extension = $extension === NULL ? "" : "." . $extension;

    $identifiers[count($identifiers) - 1] = $componentIdentifier;

    foreach($identifiers as $identifier){
        if(isValidIdentifier($identifier) === FALSE){
            // Possibly a valid file but since it contains pieces that won't make
            // valid PHP identifiers, we can only ignore it.
            return NULL;
        }
    }

    return new PackageComponent($directory->package, $identifiers, $extension);
}
