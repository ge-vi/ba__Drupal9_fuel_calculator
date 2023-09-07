<?php

namespace Drupal\your_module\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource for Fuel Calculator.
 *
 * @RestResource(
 *   id = "fuel_calculator_resource",
 *   label = @Translation("Fuel Calculator Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/fuel-calculator"
 *   }
 * )
 */
class FuelCalculatorResource extends ResourceBase {

  /**
   * Responds to POST requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response.
   */
  public function post(Request $request) {
    // Get input values from the request.
    $data = json_decode($request->getContent(), TRUE);

    if (!isset($data['distance']) || !isset($data['consumption']) || !isset($data['price'])) {
      throw new BadRequestHttpException('Missing input values.');
    }

    // Calculate Fuel Spent and Fuel Cost.
    $distance = $data['distance'];
    $consumption = $data['consumption'];
    $price = $data['price'];

    $fuel_spent = ($distance / 100) * $consumption;
    $fuel_cost = $fuel_spent * $price;

    // Prepare the response.
    $response_data = [
      'fuel_spent' => $fuel_spent,
      'fuel_cost' => $fuel_cost,
    ];

    // Return the response as JSON.
    return new ModifiedResourceResponse($response_data);
  }
}
