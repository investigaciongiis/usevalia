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
 *
 * @file
 * Contains \Drupal\usevalia\Form\Multistep\MultistepOneForm.
 */

namespace Drupal\usevalia\Form\ModificarAplicacion;

use Drupal;
use Drupal\Core\Form\FormStateInterface;

class ModificarAplicacionUnoForm extends ModificarAplicacionFormBase
{

  private $aplicaciones;

  /**
   *
   * {@inheritdoc} .
   */
  public function getFormId()
  {
    return 'modificar_aplicacion_uno_form';
  }

  /**
   *
   * {@inheritdoc} .
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $controlador = Drupal::service('usevalia.controlador');
    $form['#cache']['max-age'] = 0;
    $form = parent::buildForm($form, $form_state);
    $header = [
      'nombre' => t('Nombre'),
      'categoria' => t('Categoría'),
      'url' => t('URL'),
      'descripcion' => t('Descripción'),
    ];
    $output = [];
    $this->aplicaciones = $controlador->getAllAplicaciones();
    foreach ($this->aplicaciones as $aplicacion) {
      $output[$aplicacion->__get('id')] = [
        'nombre' => $aplicacion->__get('nombre'),
        'categoria' => t($aplicacion->__get('categoria')->__get('nombre')),
        'url' => $aplicacion->__get('url'),
        'descripcion' => $aplicacion->__get('descripcion'),
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No tienes aplicaciones...'),
      '#multiple' => FALSE
    ];
    $form['actions']['submit']['#value'] = $this->t('Editar');
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    if (empty($form_state->getValue('table'))) {
      $messenger->addMessage(t('Debes seleccionar una aplicación.'), 'error');
    } else if (!$controlador->seUsaAplicacion($this->aplicaciones[$form_state->getValue('table')])) {
      $aplicacion = $this->aplicaciones[$form_state->getValue('table')];
      $id = $aplicacion->__get('id');
      $this->store->set('aplicaciones', $id);
      $form_state->setRedirect('usevalia.modificar_aplicacion_dos');
    } else {
      $aplicacion = $this->aplicaciones[$form_state->getValue('table')];
      $messenger->addMessage(t('La aplicación está siendo usada.'), 'error');
    }
  }
}
