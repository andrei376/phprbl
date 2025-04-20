<?php

namespace PDPhilip\Elasticsearch\Laravel\Compatibility\Schema;

use PDPhilip\Elasticsearch\Laravel\v11\Schema\BlueprintCompatibility as BlueprintCompatibility11;
use PDPhilip\Elasticsearch\Laravel\v12\Schema\BlueprintCompatibility as BlueprintCompatibility12;
use PDPhilip\Elasticsearch\Utils\Helpers;

$laravelVersion = Helpers::getLaravelCompatabilityVersion();

if ($laravelVersion == 12) {
    trait BlueprintCompatibility
    {
        use BlueprintCompatibility12;
    }
} else {
    trait BlueprintCompatibility
    {
        use BlueprintCompatibility11;
    }
}
