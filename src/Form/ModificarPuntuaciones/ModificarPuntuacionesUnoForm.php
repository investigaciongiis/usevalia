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
 * Contains \Drupal\usevalia\Form\Multistep\MultistepOneForm.
 */

namespace Drupal\usevalia\Form\ModificarPuntuaciones;

use Drupal\Core\Form\FormStateInterface;

class ModificarPuntuacionesUnoForm extends ModificarPuntuacionesFormBase {

  private $puntuaciones;

  /**
   *
   * {@inheritdoc} .
   */
  public function getFormId() {
    return 'modificar_puntuaciones_uno_form';
  }

  /**
   *
   * {@inheritdoc} .
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $controlador = \Drupal::service('usevalia.controlador');
    $form = parent::buildForm($form, $form_state);
    $form['#cache']['max-age'] = 0;
    $header = [
      'nombre' => t('Nombre del esquema'),
      'descripcion' => t('Descripción'),
      'valores' => t('Lista de valores')
    ];
    $this->puntuaciones = $controlador->getAllEscalasPuntuaciones();
    $output = [];
    foreach ($this->puntuaciones as $puntuacion) {
      $output[$puntuacion->__get('id')] = [
        'nombre' => $puntuacion->__get('nombre'),
        'descripcion' => $puntuacion->__get('descripcion'),
        'valores' => implode(', ', $puntuacion->__get('valores'))
      ];
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No hay esquemas de puntuacion creados...'),
      '#multiple' => FALSE
    ];
    $form['actions']['submit']['#value'] = $this->t('Editar');
    return $form;
  }

  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $controlador = \Drupal::service('usevalia.controlador');
    $esquema = $this->puntuaciones[$form_state->getValue('table')];
    if (!$controlador->seUsaEsquemaPuntuacion($esquema)) {
      $id = $esquema->__get('id');
      $this->store->set('esquema_puntuacion', $id);
      $form_state->setRedirect('usevalia.modificar_puntuaciones_dos');
    }
    else {
      drupal_set_message('El esquema de puntuación "' . $esquema->__get('nombre') . '" se está usando', 'error');
    }
  }

}
