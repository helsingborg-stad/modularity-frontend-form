<?php

namespace ModularityFrontendForm\Api;

use WP_REST_Request;
use ModularityFrontendForm\Api\RestApiParamEnums;

interface RestApiParamsInterface
{
  /**
   * Returns the values from the request for the given parameters.
   * 
   * @param WP_REST_Request $request The request to get the values from.
   * 
   * @return array The values for the given parameters.
   */
  public function getValuesFromRequest(WP_REST_Request $request): object;

  /**
   * Returns the specification for the given parameters.
   * 
   * @param RestApiParamEnums ...$enums The parameters to get the specification for.
   * 
   * @return array The specification for the given parameters.
   */
  public function getParamSpecification(RestApiParamEnums ...$enums): array;
}