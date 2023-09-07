<?php

namespace Drupal\fuel_calculator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class FuelCalculatorForm extends FormBase {
  protected function getEditableConfigNames() {
    return ['fuel_calculator.settings'];
  }

  public function getFormId() {
    return 'fuel_calculator_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
  
  
    $config = $this->config('fuel_calculator.settings');

    $request = \Drupal::request();
    $distance = $request->query->get('distance');
    $fuelConsumption = $request->query->get('fuel_consumption');
    $pricePerLiter = $request->query->get('price_per_liter');
  
    // Set default values based on query parameters if they exist.
    $default_distance = !empty($distance) ? $distance : $config->get('default_distance');
    $default_consumption = !empty($fuelConsumption) ? $fuelConsumption : $config->get('default_consumption');
    $default_price = !empty($pricePerLiter) ? $pricePerLiter : $config->get('default_price');

    $form['distance'] = [
      '#type' => 'number',
      '#title' => $this->t('Distance travelled (km)'),
      '#required' => TRUE,
      '#default_value' => $default_distance,
      '#color' =>'red'
    ];
  
    $form['fuel_consumption'] = [
      '#type' => 'number',
      '#title' => $this->t('Fuel consumption (L/100km)'),
      '#required' => TRUE,
      '#step' => 'any',
      '#default_value' => $default_consumption,
    ];

    $form['price_per_liter'] = [
      '#type' => 'number',
      '#title' => $this->t('Price per Liter'),
      '#required' => TRUE,
      '#step' => 'any', // Allow decimal values.
      '#default_value' => $default_price,
    ];

    if ($form_state->has('fuel_spent') && $form_state->has('fuel_cost')) {
      $fuelSpent = $form_state->get('fuel_spent');
      $fuelCost = $form_state->get('fuel_cost');
  

      $form['fuel_spent'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Fuel Spent in liters'),
        '#default_value' => round($fuelSpent, 2),
        '#disabled' => TRUE, // Make it read-only.
        // ...,
      ];
      $form['fuel_cost'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Fuel Cost in €'),
        '#default_value' => round($fuelCost, 2,),
        '#disabled' => TRUE, // Make it read-only.
        // ...,
      ];
    }
  
    // Add a Calculate button.
    $form['calculate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Calculate'),
      '#submit' => ['::submitForm'],
    ];
  
    // Add a Reset button.
    $form['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => ['::resetForm'],
    ];
  
    return $form;
  }
  public function resetForm(array &$form, FormStateInterface $form_state) {
    // Reset the form values to default and remove the calculation results.
    $form_state->setValue('distance', '');
    $form_state->setValue('fuel_consumption', '');
    $form_state->setValue('price_per_liter', '');
  
    $form_state->clearErrors();
  
    $form_state->set('fuel_spent', NULL);
    $form_state->set('fuel_cost', NULL);
   
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $distance = $form_state->getValue('distance');
    $fuelConsumption = $form_state->getValue('fuel_consumption');
    $pricePerLiter = $form_state->getValue('price_per_liter');
  
    // Validate the Distance field.
    if ($distance <= 0) {
      $form_state->setErrorByName('distance', $this->t('Distance must be a positive number.'));
    }
  
    // Validate the Fuel Consumption field.
    if ($fuelConsumption <= 0) {
      $form_state->setErrorByName('fuel_consumption', $this->t('Fuel Consumption must be a positive number.'));
    }
  
    // Validate the Price per Liter field.
    if ($pricePerLiter <= 0) {
      $form_state->setErrorByName('price_per_liter', $this->t('Price per Liter must be a positive number.'));
    }
  }  

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $distance = $form_state->getValue('distance');
    $pricePerLiter = $form_state->getValue('price_per_liter');
    $fuelConsumptionPer100km = $form_state->getValue('fuel_consumption');

    // Convert L/100km to l/km.
    $fuelConsumptionPerKm = $fuelConsumptionPer100km;
    $fuelConsumptionPer100Km = $fuelConsumptionPerKm * 1;

    // Calculate fuel spent and fuel cost.
    $fuelSpent = ($distance * $fuelConsumptionPer100Km) / 100;
    $fuelCost = $fuelSpent * $pricePerLiter;

    // Save the calculation in Drupal logs.
    $this->logCalculation($distance, $fuelConsumptionPer100Km, $pricePerLiter, $fuelSpent, $fuelCost);

// Set the results in the form state.
$form_state->set('fuel_spent', $fuelSpent);
$form_state->set('fuel_cost', $fuelCost);

  // Rebuild the form to display the results.
  $form_state->setRebuild(TRUE);

    // Display the results.
    $result = $this->t('Fuel Spent: @fuel_spent liters. Fuel Cost: $@fuel_cost', [
      '@fuel_spent' => round($fuelSpent, 2),
      '@fuel_cost' => round($fuelCost, 2),
    ]);

    $form_state->set('result', $result);
  }

  protected function logCalculation($distance, $fuelConsumptionPer100Km, $pricePerLiter, $fuelSpent, $fuelCost) {
    // Get the current user.
    $user = \Drupal::currentUser();

    // Get the user's IP address.
    $ip_address = \Drupal::request()->getClientIp();


    // Log the calculation and user information.
    \Drupal::logger('fuel_calculator')->notice('Fuel Calculator Calculation - User: @username, IP: @ip, Distance: @distance km, Fuel Consumption: @consumption l/100km, Price per Liter: @price per liter, Fuel Spent: @fuel_spent liters, Fuel Cost: $@fuel_cost', [
      '@username' => $user->getAccountName(),
      '@ip' => $ip_address,
      '@distance' => $distance,
      '@consumption' => $fuelConsumptionPer100Km,
      '@price' => $pricePerLiter,
      '@fuel_spent' => round($fuelSpent, 2),
      '@fuel_cost' => round($fuelCost, 2),
    ]);
  }
}
