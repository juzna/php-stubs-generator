<?php
$outDir = getcwd() . "/out";
if (!file_exists($outDir)) {
    $result = mkdir($outDir, 0755, true);
}

$loaded_extensions = get_loaded_extensions();

foreach ($loaded_extensions as $extensionName) {
    handleExtension($extensionName);
}

function handleExtension($extensionName)
{

    $out = fopen("out\\$extensionName.php", 'w');
    fwrite($out, "<?php\n");
    $reflectionExtension = new ReflectionExtension($extensionName);

    //constants
    $constants = $reflectionExtension->getConstants();
    foreach ($constants as $constantName => $constantValue) {
        if (is_string($constantValue)) {
            $constantValue = "'$constantValue'";
        }
        fwrite($out, "const $constantName = $constantValue;\n");
    }

    //functions
    $defined_functions = $reflectionExtension->getFunctions();
    foreach ($defined_functions as $definedFunction) {
        /** @var $definedFunction ReflectionFunction */
        $definedFunctionName = $definedFunction->getName();
        fwrite($out, "function $definedFunctionName(");
        $parameters = $definedFunction->getParameters();
        $isFirstParameter = true;
        foreach ($parameters as $parameter) {
            if ($isFirstParameter) {
                $isFirstParameter = false;
            } else {
                fwrite($out, ", ");
            }
            /** @var $parameter ReflectionParameter */
            if ($parameter->isPassedByReference()) {
                fwrite($out, "&");
            }
            $parameterName = $parameter->getName();
            fwrite($out, "$$parameterName");
            if ($parameter->isDefaultValueAvailable()) {
                fwrite($out, " = ");
                print_r($parameter->getDefaultValue());
            }
        }
        fwrite($out, "){ }\n");
    }

    //classes
    $classes = $reflectionExtension->getClasses();
    foreach ($classes as $class) {
        /** @var $class ReflectionClass */
        $className = $class->getName();

        fwrite($out, "class $className {\n");
        fwrite($out, "}\n");
    }
    fclose($out);
}