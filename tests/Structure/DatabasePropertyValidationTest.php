<?php

namespace Tests\Structure;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class DatabasePropertyValidationTest extends TestCase
{
    use DatabaseTransactions;

    private static $providerDbObjectsList = null;

    const IGNORE_COLUMNS_ALL = [User::COL_CREATED_AT, User::COL_UPDATED_AT];
    const IGNORE_COLUMNS_VAL = ['id'];
    const IGNORE_FILE_NAMES = ['User.php', 'Role.php', 'Permission.php'];

    /**
     * @throws ReflectionException
     */
    public function testDatabasePropertyAndValidation()
    {
        $finder = new Finder();
        $finder->depth('== 0');
        $finder->files()->in(app_path().DIRECTORY_SEPARATOR.'Models');

        foreach ($finder as $reflection) {
            $class = app()->getNamespace().'Models\\'.$reflection->getBasename('.php');
            $reflectionClass = new ReflectionClass($class);

            if ($reflectionClass->isInterface() || $reflectionClass->isAbstract()) {
                // we cannot check interfaces or abstract classes
                continue;
            }
            $object = resolve($class);
            if (!($object instanceof Model)) {
                // this is no model. Therefore we cannot check stuff
                continue;
            }

            $dbSchema = $object->getConnection()->getSchemaBuilder();
            $dbColumns = $dbSchema->getColumnListing($object->getTable());
            $propertyAnnotations = $this->readPropertiesFromClassAnnotation($reflectionClass);
            $errors = [];
            if (!$reflectionClass->hasProperty('table')) {
                $errors[] = ' - missing property $table ('.$class.')'.PHP_EOL;
            } else {
                $tableReflection = $reflectionClass->getProperty('table');
                $tableReflection->setAccessible(true);
                $tableName = $tableReflection->getValue($object);
                if (!Str::startsWith(strtolower($reflection->getBasename('.php')), strtolower($tableName))) {
                    $errors[] = ' - the tablename and the classname are not matching'.PHP_EOL;
                }
            }

            foreach ($dbColumns as $dbColumn) {
                if (substr($dbColumn, 0, 2) == 'fk') {
                    // do nothing since this is a foreign key
                } elseif (in_array($dbColumn, self::IGNORE_COLUMNS_ALL)) {
                    //do nothing -- ignore column
                } elseif ($propertyAnnotations->has($dbColumn)) {
                    unset($propertyAnnotations[$dbColumn]);
                } else {
                    $errors[] = ' - missing annotation @property datatype '.$dbColumn.PHP_EOL;
                }
            }

            if (!empty($errors)) {
                $this->fail('Problems in Class '.$reflection->getFileName()." :\r\n".implode($errors));
            }
        }
        $this->assertTrue(true);
    }

    private function readPropertiesFromClassAnnotation(ReflectionClass $reflection)
    {
        $parentReflection = $reflection->getParentClass();
        $resultArray = null;
        if ($parentReflection && $parentReflection->getName() !== 'App\BaseModel') {
            $resultArray = $this->readPropertiesFromClassAnnotation($parentReflection);
        } else {
            $resultArray = collect();
        }
        $annotations = $reflection->getDocComment();
        $annotations = preg_split('/($|\n|\r)/', $annotations);

        foreach ($annotations as $line) {
            if (strpos($line, '@property') !== false) {
                $typeAndName = preg_split('/(?!(\w|\|))\b/', $line);
                $type = ltrim($typeAndName[1], '[] ');
                $name = str_replace('$', '', ltrim($typeAndName[2], '[] '));

                $resultArray->put($name, [
                    'name' => $name,
                    'typ' => $type,
                ]);
            }
        }

        return $resultArray;
    }

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    public function providerDbObjects(): array
    {
        if (!is_null(self::$providerDbObjectsList)) {
            return self::$providerDbObjectsList;
        }
        $result = [];
        $baseModelReflection = new ReflectionClass(User::class);
        $namespace = $baseModelReflection->getNamespaceName().'\\';
        $path = dirname($baseModelReflection->getFileName()).DIRECTORY_SEPARATOR;
        $fileNames = scandir($path);
        foreach ($fileNames as $fileName) {
            if (substr($fileName, -4) === '.php' && ! in_array($fileName, self::IGNORE_FILE_NAMES)) {
                $class = substr($fileName, 0, strlen($fileName) - 4);
                $classNs = $namespace.$class;
                $reflection = new ReflectionClass($classNs);
                if (!$reflection->isAbstract()
                    && !$reflection->isInterface()
                    && $reflection->isSubclassOf(Model::class)
                    && strpos($fileName, 'Abstract') === false) {
                    $result[$class] = [$reflection];
                }
            }
        }
        self::$providerDbObjectsList = $result;
        return $result;
    }
}
