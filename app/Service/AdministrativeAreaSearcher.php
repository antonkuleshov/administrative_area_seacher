<?php

namespace Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AdministrativeAreaSearcher
{
    public array $responseArray = ['error' => 'Do not found this place'];

    public function __construct(
        private readonly Client $client,
        private readonly string $apiLink,
        private readonly string $apiKey
    ) {}

    public function getName(
        string $placeName,
        string $countryCode = 'SE'
    ): string
    {
        $queryArray = [
            'key' => $this->apiKey,
            'components' => "country:$countryCode|locality:$placeName"
        ];
        try {
            $res = $this->client->get($this->apiLink, [
                'query' => $queryArray
            ]);
            $response = json_decode($res->getBody(), true);

            if (!$response['results'] || $response['status'] === "ZERO_RESULTS") {
                return json_encode($this->responseArray);
            }

            $administrativeAreaLevel1 = '';
            $administrativeAreaLevel2 = '';
            $this->findNeededAdministrativeAreaLevelRecursively(
                $response['results'],
                'short_name',
                'administrative_area_level_1',
                $administrativeAreaLevel1
            );
            $this->findNeededAdministrativeAreaLevelRecursively(
                $response['results'],
                'short_name',
                'administrative_area_level_2',
                $administrativeAreaLevel2
            );


            $prepareAdministrativeAreaLevel1 = $this->prepareAdministrativeAreaLevelName($administrativeAreaLevel1);
            $prepareAdministrativeAreaLevel2 = $this->prepareAdministrativeAreaLevelName($administrativeAreaLevel2);

            $responseStr = <<<EOT
                "locality": "$placeName",
                "administrative_area_level_1": "$prepareAdministrativeAreaLevel1",
                "administrative_area_level_2": "$prepareAdministrativeAreaLevel2"
EOT;

        } catch (GuzzleException $e) {
            return json_encode(['error' => $e->getMessage()]);
        }

        return $responseStr;
    }

    public function findNeededAdministrativeAreaLevelRecursively($array, $searchingShortNameKey, $searchingValueTypes, &$name): void
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if ($key === $searchingShortNameKey) {
                    $shortName = next($array);
                    $typesArray = next($array);
                    if ($typesArray[0] === $searchingValueTypes) {
                        $name = $shortName;
                        break;
                    }

                } else {
                    $this->findNeededAdministrativeAreaLevelRecursively($value, $searchingShortNameKey, $searchingValueTypes, $name);
                }
            }
        }
    }

    public function prepareAdministrativeAreaLevelName(string $name): string
    {
        $arrayAdministrativeAreaLevel = explode(' ', $name);
        array_splice($arrayAdministrativeAreaLevel, -1);
        return implode(' ', $arrayAdministrativeAreaLevel);
    }
}