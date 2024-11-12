<?php

namespace Test;

use IKadar\SimplifiedNeo4Client\ClientBuilder;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private array $credentials;

    protected function setUp(): void
    {
        $this->credentials = [
        ];
    }

    /**
     * @dataProvider serveQueries
     *
     */
    public function testGetValueReturnsExpectedValue($cypherParts): void
    {
        $clientBuilder = new ClientBuilder();
        $client = $clientBuilder->create($this->credentials);

        $cypher = implode("\n", $cypherParts);

        $result = $client->run($cypher);

//        dump($result);

        $this->assertTrue($this->arrayContainsOnlyScalars($result));
    }

    private function arrayContainsOnlyScalars(array $array): bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                if (!$this->arrayContainsOnlyScalars($value)) {
                    return false;
                }
            } elseif (!is_scalar($value)) {
                return false;
            }
        }
        return true;
    }

    public static function serveQueries(): array
    {
        return [
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, COLLECT(a) AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z", www]} AS mapy',
                'SKIP 100 LIMIT 10',
                'RETURN *',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, a.fr AS sys_modified_at, COLLECT(a) AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z", www]} AS mapy',
                'SKIP 100 LIMIT 10',
                'RETURN *',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, a AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z"]} AS mapy',
                'LIMIT 2',
                'RETURN *',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, COLLECT(a) AS attributes',
                'LIMIT 2',
                'RETURN *',
            ]],

            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, COLLECT(a) AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z", www]} AS mapy',
                'SKIP 100 LIMIT 10',
                'RETURN attributes AS a',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, a.fr AS sys_modified_at, COLLECT(a) AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z", www]} AS mapy',
                'SKIP 100 LIMIT 10',
                'RETURN attributes AS a',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, n.fr AS fr, a AS attributes, "x" as txt, ["a", "b", "c"] AS ary, {x: ["y", "z"]} AS mapy',
                'LIMIT 2',
                'RETURN attributes AS a',
            ]],
            [[
                'MATCH (n:section)-->(a:attribute)',
                'WITH *, {www: "WWW"} as www',
                'WITH n, COLLECT(a) AS attributes',
                'LIMIT 2',
                'RETURN attributes AS a',
            ]],

        ];
    }

}
