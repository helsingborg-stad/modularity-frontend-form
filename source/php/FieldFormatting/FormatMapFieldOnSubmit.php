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
        $this->wpService->addFilter('acf/update_value/type=google_map', [$this, 'formatMapFieldValue'], 10, 3);
    }

    /**
     * Format map field value on submit
     *
     * @param string $value
     * @param int $post_id
     * @param array $field
     * @return string
     */
    public function formatMapFieldValue($value, $post_id, $field) : string|array
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
      return str_contains($string, 'https://schema.org') && json_decode($string) !== null;
    }
}