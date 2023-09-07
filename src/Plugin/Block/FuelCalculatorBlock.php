<?php

namespace Drupal\fuel_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Fuel Calculator block.
 *
 * @Block(
 *   id = "fuel_calculator_block",
 *   admin_label = @Translation("Fuel Calculator Block"),
 * )
 */
class FuelCalculatorBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Create and return your form here.
    $form = \Drupal::formBuilder()->getForm('Drupal\fuel_calculator\Form\FuelCalculatorForm');
    return $form;
  }
}
