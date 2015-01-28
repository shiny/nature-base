<?php
if(isset($app) && is_callable(array($app, 'run'))) {
    $app->run();
} else {
    foreach (get_defined_vars() as $var) {
        if(is_a($var, 'nature') && is_callable(array($var, 'run'))) {
            $var->run();
        }
    }
}