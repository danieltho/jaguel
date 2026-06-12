<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Corta antes de que RefreshDatabase ejecute migrate:fresh contra una base
     * real. Dentro de Docker, una config cacheada (deploy.sh corre config:cache
     * en cada arranque) hace que Laravel ignore los env de phpunit.xml y los
     * tests apunten a la MySQL de desarrollo. Usar `composer run test`, que
     * limpia la config primero.
     */
    protected function setUpTraits()
    {
        if (config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException(
                'Los tests deben correr sobre sqlite :memory: y la conexión actual es "'
                .config('database.default').'". Ejecuta `composer run test` (limpia la config cacheada).'
            );
        }

        return parent::setUpTraits();
    }
}
