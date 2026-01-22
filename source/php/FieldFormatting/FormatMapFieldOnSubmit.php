<?php

namespace ModularityFrontendForm\FieldFormatting;

use ModularityFrontendForm\Hookable\Hookable;
use WpService\WpService;

/**
 * Class FormatMapFieldOnSubmit
 * 
 * This class formats ACF Google Map fields submitted via the Frontend API.
 * It converts schema.org formatted location data into the structure expected by ACF.
 * This is done due to discrepancies in how location data is represented in schema.org 
 * (open street map frontend rendering) versus ACF's requirements.
 * 
 * @package ModularityFrontendForm\FieldFormatting
 */

class FormatMapFieldOnSubmit implements Hookable
{
    public function __construct(private WpService $wpService){}

    /**
     * Add hooks
     */
    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/update_value/type=google_map', [$this, 'formatMapFieldValue'], 10, 1);
    }

    /**
     * Format map field value on submit
     *
     * @param string $value
     * 
     * @return string
     */
    public function formatMapFieldValue($value) : string|array
    {
      $isFrontendRequest  = $this->isFrontendApi();
      $isSchemaFormat     = $this->isSchemaFormat($value);

      if($isFrontendRequest && $isSchemaFormat) {
        return $this->formatSchemaObjectToAcfGoogleMapsGeoData($value);
      }

      return $value;
    }

    /**
     * Format schema.org object to ACF Google Maps field format
     *
     * @param string $schemaString
     * @return string
     */
    private function formatSchemaObjectToAcfGoogleMapsGeoData(string $schemaString) : array
    {
      $schemaObject = json_decode($schemaString, true);

      $streetNumber = null;
      $streetName   = null;

      if (!empty($schemaObject['address']['name'])) {
          $parts = array_map('trim', explode(',', $schemaObject['address']['name']));
          $streetNumber = $parts[0] ?? null;
          $streetName   = $parts[1] ?? null;
      }

      $formattedValueFromSchema = [
          'address'       => $schemaObject['address']['name'] ?? null,
          'lat'           => isset($schemaObject['latitude']) ? (float) $schemaObject['latitude'] : null,
          'lng'           => isset($schemaObject['longitude']) ? (float) $schemaObject['longitude'] : null,
          'zoom'          => 15,
          'place_id'      => null,
          'street_number' => $streetNumber,
          'street_name'   => $streetName,
          'city'          => '',
          'state'         => $schemaObject['address']['addressRegion'] ?? null,
          'post_code'     => $schemaObject['address']['postalCode'] ?? null,
          'country'       => $schemaObject['address']['addressCountry'] ?? null,
          'country_short' => 'SE',
      ];

      //Convert all items to utf8
      array_walk_recursive($formattedValueFromSchema, function (&$item) {
          if (is_string($item)) {
              $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
          }
      });

      return $formattedValueFromSchema;
    }

    /**
     * Check if the request is from the Frontend API
     *
     * @return bool
     */
    private function isFrontendApi() : bool
    {
      if (defined('REST_REQUEST') && REST_REQUEST) {
        return true;
      }
      return false;
    }

    /**
     * Check if string is a schema.org format
     *
     * @param string $string
     * @return bool
     */
    private function isSchemaFormat(string $string) : bool
    {
        $data = json_decode($string, true);
        if (!is_array($data)) {
          return false;
        }

        $hasContext = false;
        if (isset($data['@context'])) {
          if (is_array($data['@context'])) {
            foreach ($data['@context'] as $ctx) {
              if (is_string($ctx) && str_contains($ctx, 'schema.org')) {
                $hasContext = true;
                break;
              }
            }
            foreach ($data['@context'] as $key => $ctx) {
              if (is_string($ctx) && str_contains($ctx, 'schema.org')) {
                $hasContext = true;
                break;
              }
            }
          } elseif (is_string($data['@context']) && str_contains($data['@context'], 'schema.org')) {
            $hasContext = true;
          }
        }

        $hasType    = isset($data['@type']) && $data['@type'] === 'Place';
        $hasLat     = isset($data['latitude']);
        $hasLng     = isset($data['longitude']);
        $hasAddress = isset($data['address']) && is_array($data['address']);

        return $hasContext && $hasType && $hasLat && $hasLng && $hasAddress;
    }
}