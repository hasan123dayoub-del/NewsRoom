<?php

namespace Predis {
    class Client {
        public function __construct($parameters = null, $options = null) {}
        public function __call($method, $args) { return null; }
        public function connect() { return; }
        public function disconnect() { return; }
    }
}

namespace {
    use Tests\TestCase;
    use Illuminate\Foundation\Testing\RefreshDatabase;

    uses(TestCase::class, RefreshDatabase::class)->in('Feature');

    beforeEach(function () {
        config(['database.redis' => [
            'client' => 'predis',
            'default' => [
                'host' => '127.0.0.1',
                'password' => null,
                'port' => 6379,
                'database' => 0,
            ],
        ]]);
    });
}
