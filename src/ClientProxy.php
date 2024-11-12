<?php

namespace IKadar\SimplifiedNeo4Client;

use Exception;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;

class ClientProxy
{
    protected Client $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array(array(&$this->client, $name), $arguments);
    }

    public function run(string $statement, iterable $parameters = [], ?string $alias = null): array
    {
        $summarizedResult = $this->client->runStatement(Statement::create($statement, $parameters), $alias);
        return $this->toArray($summarizedResult);
    }

    private function toArray(SummarizedResult $result): string|array|bool|int|float
    {
        $summary = $result->getSummary();
        $maps = [];

        foreach ($result as $record) { // 1 line of result
            $nodes = [];
            foreach ($record as $nodeLoop => $node) {  // 1 node of 1 line of result
                if ($node === null) {
                    continue;
                }

                $nodes[$nodeLoop] = $this->toRecursiveArray($node);
            }
            $maps[] = new CypherMap($nodes);
        }

        $result = new SummarizedResult($summary, new CypherList($maps));

        return $result->toRecursiveArray();
    }

    protected function toRecursiveArray(mixed $value): string|array|bool|int|float
    {
        if (is_scalar($value)) {
            return $value;
        }

        if (is_object($value) === false) {
            throw new Exception(sprintf("Unknown node type: %s.", gettype($value)));
        }

        if (is_a($value, Node::class)) {
            return $this->nodeToArray($value);
        } elseif (is_a($value, CypherList::class)) {
            return $this->listToArray($value);
        } elseif (is_a($value, CypherMap::class)) {
            return $value->toRecursiveArray();
        } else {
            throw new Exception(sprintf("Unknown node type: %s.", get_class($value)));
        }
    }

    private function nodeToArray(Node $node): array
    {
        $array = $node->getProperties()->toRecursiveArray();
        $array["id"] = $node->getId();
        return $array;
    }

    private function listToArray(CypherList $list): array
    {
        $y = $list->toRecursiveArray();
        $array = [];
        foreach ($y as $item) {
            $array[] = $this->toRecursiveArray($item);
        }
        return $array;
    }
}
