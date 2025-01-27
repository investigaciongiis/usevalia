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

namespace Drupal\usevalia\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of TableForm
 *
 * @author luis
 */
class MostrarCatalogosForm extends FormBase {

  private $usuario;

  public function getFormId() {
    return 'mostrar_catalogos_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->usuario = Drupal::currentUser()->id();

    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre del catálogo'),
      'puntuacion' => t('Esquema de puntuación'),
      'admin' => t('Creador'),
      'grupo' => t('Grupo asignado')
    ];
    $output = [];
    $catalogos = $controlador->getCatalogosByUsuarioConPermisos(Drupal::currentUser()->id());
    foreach ($catalogos as $catalogo) {
      $output[$catalogo->__get('id')] = [
        'nombre' => $catalogo->__get('nombre'),
        'puntuacion' => $catalogo->__get('esquemaPuntuacion')->__get('nombre'),
        'admin' => $catalogo->__get('autor')->__get('nombre'),
        'grupo' => $catalogo->__get('grupoAuditores')->__get('nombre')
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No hay catálogos creados...'),
      '#multiple' => FALSE
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Ver')
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tempstore = Drupal::service('user.private_tempstore')->get('usevalia_temp');
    $tempstore->set('controlador' . $this->usuario, $form_state->getValue('table'));
    $form_state->setRedirect('usevalia.listado_catalogo');
  }
}
