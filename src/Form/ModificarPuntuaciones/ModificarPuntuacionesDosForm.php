<?php

/*
 * Copyright (C) 2018 luis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * @file
 * Contains \Drupal\usevalia\Form\Multistep\MultistepTwoForm.
 */

namespace Drupal\usevalia\Form\ModificarPuntuaciones;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\usevalia\Classes\Aplicacion;

class ModificarPuntuacionesDosForm extends ModificarPuntuacionesFormBase {

  private $esquema;

  /**
   *
   * {@inheritdoc} .
   */
  public function getFormId() {
    return 'modificar_puntuaciones_dos_form';
  }

  /**
   *
   * {@inheritdoc} .
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#tree'] = TRUE;
    $form['#cache']['max-age'] = 0;
    $controlador = \Drupal::service('usevalia.controlador');
    //drupal_set_message($this->store->get('aplicaciones'));
    $this->esquema = $controlador->getEscalaPuntuacion($this->store->get('esquema_puntuacion'));
    //drupal_set_message(print_r($this->aplicacion, true));

    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de la escala de puntuacion'),
      '#default_value' => $this->esquema->__get('nombre'),
      '#required' => TRUE
    ];
    $form['descripcion'] = [
      '#type' => 'textarea',
      '#default_value' => $this->esquema->__get('descripcion'),
      '#title' => $this->t('Descripción')
    ];
    $form['opcionalidad'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Permitir directrices no evaluables'),
    ];
    // Campos de texto...
    $form['text_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => t('Campos a añadir'),
      '#prefix' => '<div id="textfieldset-wrapper">',
      '#suffix' => '</div>'
    ];
    
    if (in_array('No aplicable', $this->esquema->__get('valores'))) {
      $form['opcionalidad']['#default_value'] = true;
    }
    $valores_esquema = [];
    foreach (array_diff($this->esquema->__get('valores'), ['No aplicable']) as $valor) {
      array_push($valores_esquema, $valor);
    }

    $num_fields = $form_state->get('num_fields');
    if (empty($num_fields)) {
      $num_fields = count($valores_esquema);
      $form_state->set('num_fields', $num_fields);
    }
    for ($i = 0; $i < $num_fields; $i++) {
      $form['text_fieldset'][$i] = [
        '#type' => 'textfield',
        '#default_value' => $valores_esquema[$i],
      ];
    }
    $form['text_fieldset']['add_item'] = [
      '#type' => 'submit',
      '#value' => t('Otro elemento'),
      '#limit_validation_errors' => [],
      '#submit' => [
        '::addTextfield'
      ],
      '#ajax' => [
        'callback' => '::callbackTextfield',
        'wrapper' => 'textfieldset-wrapper'
      ]
    ];
    $form['actions']['previous'] = array(
      '#type' => 'link',
      '#title' => $this->t('Atrás'),
      '#attributes' => array(
        'class' => array(
          'button'
        )
      ),
      '#weight' => 0,
      '#url' => Url::fromRoute('usevalia.modificar_puntuaciones_uno')
    );
    $form_state->setCached(FALSE);
//    $form_state->setValue('nombre', $this->aplicacion->__get('nombre'));
//    $form_state->setValue('url', $this->aplicacion->__get('url'));
//    $form_state->setValue('descripcion', $this->aplicacion->__get('descripcion'));
//    $form_state->setRebuild();

    return $form;
  }

  public function addTextfield(array &$form, FormStateInterface $form_state) {
    $num_fields = $form_state->get('num_fields') + 1;
    $form_state->set('num_fields', $num_fields);
    $form_state->setRebuild(TRUE);
  }

  public function callbackTextfield(array &$form, FormStateInterface $form_state) {
    return $form['text_fieldset'];
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message(print_r($form_state->getFormObject()->getEntity()->toArray(), true));
    $num_fields = $form_state->get('num_fields');
    $campos_real = [];
    $campos_lwr = [];
    for ($i = 0; $i < $num_fields; $i++) {
      $campo = $form_state->getValue(['text_fieldset', $i]);
      if (!empty($campo) && !in_array(strtolower(trim($campo)), $campos_lwr)) {
        array_push($campos_real, trim($campo));
        array_push($campos_lwr, strtolower(trim($campo)));
      }
    }
    drupal_set_message('home');
    /*
    if (count($campos_real) < 2) {
      drupal_set_message('Deben haber 2 campos como mínimo. Hay ' . count($campos_real), 'error');
    }
    else {
      if ($form_state->getValue('opcionalidad') === 1) {
        array_push($campos_real, 'No aplicable');
      }
      $controlador = \Drupal::service('usevalia.controlador');
      $cambio = $controlador->updateEsquemaPuntuacion($this->esquema, $form_state->getValue('nombre'), $form_state->getValue('descripcion'), $campos_real);
      if ($cambio == true) {
        drupal_set_message('Se ha realizado el cambio del esquema "' . $this->esquema . '"');
      }
      else {
        drupal_set_message('No se ha realizado ningun cambio');
      }
      parent::saveData();
      $form_state->setRedirect('usevalia');
    }
     * 
     */
  }

}
