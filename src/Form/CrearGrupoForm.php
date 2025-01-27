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
use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\GrupoAuditores;

/**
 * Description of GrupoCreacionForm
 *
 * @author luis
 */
class CrearGrupoForm extends FormBase {

  private $auditores;

  public function getFormId() {
    return 'crear_grupo_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $this->auditores = $controlador->getAllAuditores();
    $header = [
      'nombre' => t('Nombre de usuario'),
      'idioma' => t('Idioma'),
      'correo' => t('Correo electronico')
    ];
    $output = [];
    foreach ($this->auditores as $usuario) {
      $output[$usuario->__get('id')] = [
        'nombre' => $usuario->__get('nombre'),
        'idioma' => $usuario->__get('idioma'),
        'correo' => $usuario->__get('email')
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del grupo'),
      '#required' => TRUE
    ];
    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción')
    ];
    $form['tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiquetas (separadas por coma)')
    ];
    $form['showcase'] = [
      '#type' => 'label',
      '#title' => t('Usuarios'),
      '#required' => TRUE
    ];
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#multiple' => TRUE,
      '#empty' => t('No hay usuarios disponibles...')
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Crear')
    ];
    return $form;
  }

  private function getValoresSeleccionados(array $form, FormStateInterface $form_state) {
    $seleccionados = [];
    foreach ($form_state->getValue('table') as $clave => $valor) {
      if (!empty($valor)) {
        array_push($seleccionados, $this->auditores[$clave]);
      }
    }
    return $seleccionados;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $seleccionados = $this->getValoresSeleccionados($form, $form_state);
    $messenger = Drupal::messenger();
    if(empty($seleccionados)){
      $messenger->addMessage(t('El campo "Usuarios" es obligatorio.'), 'error');
    }else{
        $etiquetas = array_map('mb_strtolower', array_map('trim', explode(',', $form_state->getValue('tags'))));
        $grupo = $controlador->crearGrupoAuditores($form_state->getValue('nombre'), $form_state->getValue('descripcion'), $seleccionados, $etiquetas);
        $messenger->addMessage(t('El grupo "') . $grupo->__get('nombre') . t('" se ha creado correctamente.'));
    }
  }

}
