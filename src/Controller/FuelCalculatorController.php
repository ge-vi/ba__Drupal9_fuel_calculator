<?php

namespace Drupal\fuel_calculator\Controller;

use Drupal\Core\Controller\ControllerBase;

class FuelCalculatorController extends ControllerBase {

  public function calculateForm() {
    // Create the fuel calculator form.
    $form = \Drupal::formBuilder()->getForm('\Drupal\fuel_calculator\Form\FuelCalculatorForm');

    return $form;
  }
}
