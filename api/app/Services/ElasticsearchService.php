<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ElasticsearchService
{
    protected string $baseUrl = 'http://searcher:9200';
    protected string $index = 'customers';

    public function indexCustomer(array $data, int $id)
    {
        return Http::put("{$this->baseUrl}/{$this->index}/_doc/{$id}", $data);
    }

    public function deleteCustomer(int $id)
    {
        return Http::delete("{$this->baseUrl}/{$this->index}/_doc/{$id}");
    }

    public function search(string $query)
    {
        $response = Http::post("{$this->baseUrl}/{$this->index}/_search", [
            'query' => [
                'multi_match' => [
                    'query' => $query,
                    'type' => 'bool_prefix',
                    'fields' => [
                        'first_name',
                        'first_name._2gram',
                        'first_name._3gram',
                        'last_name',
                        'last_name._2gram',
                        'last_name._3gram',
                        'email',
                        'contact_number'
                    ]
                ]
            ]
        ])->json();

        if (!isset($response['hits']['hits'])) {
            return [];
        }

        return collect($response['hits']['hits'])
            ->pluck('_source')
            ->values();
    }



}
