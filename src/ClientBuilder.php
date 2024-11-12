<?php

namespace IKadar\SimplifiedNeo4Client;

use Laudis\Neo4j\ClientBuilder as Base;
use Laudis\Neo4j\Databags\SessionConfiguration;

class ClientBuilder
{
    public function create($credentials)
    {

        return new ClientProxy(
            Base::create()
                ->withDriver('bolt', sprintf(
                    'bolt://neo4j:%s@%s:%s',
                    $credentials["password"],
                    $credentials["host"],
                    $credentials["boltPort"]
                )) // creates a bolt driver
                ->withDefaultDriver('bolt')
                ->withDefaultSessionConfiguration(SessionConfiguration::default()->withDatabase($credentials["database"]))
                ->build()
        );
    }
}
