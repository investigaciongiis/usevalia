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

namespace Drupal\usevalia\Form\ModificarAplicacion;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\usevalia\Classes\Aplicacion;

class ModificarAplicacionDosForm extends ModificarAplicacionFormBase {

  private $aplicacion;
  private $categorias;

  public function getFormId() {
    return 'modificar_aplicacion_dos_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $this->aplicacion = $controlador->getAplicacion($this->store->get('aplicaciones'));

    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de la aplicación'),
      '#default_value' => $this->aplicacion->__get('nombre'),
      '#required' => TRUE,
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#default_value' => $this->aplicacion->__get('url'),
      '#title' => $this->t('URL de la aplicación')
    ];
    $this->categorias = $controlador->getAllCategorias();
    $output_categorias = [];
    foreach ($this->categorias as $categoria) {
      $output_categorias[$categoria->__get('id')] = t($categoria->__get('nombre'));
    }
    $form['categoria'] = [
      '#type' => 'select',
      '#title' => $this->t('Selecciona la categoría web'),
      '#options' => $output_categorias,
      '#default_value' =>$this->aplicacion->__get('categoria')->__get('id'),
      '#required' => TRUE
    ];
    $form['descripcion'] = [
      '#type' => 'textarea',
      '#default_value' => $this->aplicacion->__get('descripcion'),
      '#title' => $this->t('Descripción')
    ];

    $form['actions']['previous'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Atrás'),
      '#weight' => 0,
      '#submit' => [
        '::goBack'
      ]
    );
    return $form;
  }

  public function goBack(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('usevalia.modificar_aplicacion_uno');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = Drupal::messenger();
    $categoria = $this->categorias[$form_state->getValue('categoria')];
    if ($form_state->getValue('nombre') != $this->aplicacion->__get('nombre')
      || $form_state->getValue('url') != $this->aplicacion->__get('url')
      || $form_state->getValue('descripcion') != $this->aplicacion->__get('descripcion')
      || $categoria->__get('nombre') != $this->aplicacion->__get('categoria')->__get('nombre')) {
      $controlador = Drupal::service('usevalia.controlador');
      $controlador->updateAplicacion($this->aplicacion->__get('id'),
        $form_state->getValue('nombre'), $form_state->getValue('descripcion'), $form_state->getValue('url'),
        $categoria);
      $messenger->addMessage(t('Se han guardado los cambios.'));
    } else {
      $messenger->addMessage(t('No se ha realizado ningun cambio.'));
    }
    $form_state->setRedirect('usevalia.modificar_aplicacion_uno');
  }

}
