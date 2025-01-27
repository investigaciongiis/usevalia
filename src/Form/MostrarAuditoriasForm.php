<?php

/*
 * Copyright (C) 2020 celia
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
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Description of MostrarAuditoriasForm
 *
 * @author celia
 */
class MostrarAuditoriasForm extends FormBase {

  private $usuario;

  public function getFormId() {
    return 'mostrar_auditorias_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->usuario = Drupal::currentUser()->id();

    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre de la auditoría'),
      'aplicacion' => t('Aplicación'),
      'catalogo' => t('Catálogo utilizado'),
      'auditores' => t('Participantes'),
	    'estado' => t('Estado de evaluación')
    ];
    $output = [];
    $auditorias = $controlador->getAllAuditoriasFullFrom($this->usuario);
    foreach ($auditorias as $auditoria) {
      $participantes = [];
      foreach ($auditoria->__get('participantes') as $participante) {
        array_push($participantes, $participante->__get('nombre'));
      }
      $output[$auditoria->__get('id')] = [
        'nombre' => $auditoria->__get('nombre'),
        'aplicacion' => $auditoria->__get('aplicacion')->__get('nombre'),
        'catalogo' => $auditoria->__get('catalogo')->__get('nombre'),
        'auditores' => implode(', ', $participantes),
		    'estado' => $auditoria->getEstadoEvaluacion(),
		    '#attributes' => array('style' => array($auditoria->getEstadoColorEvaluacion())),
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['showcase'] = [
      '#type' => 'label'
    ];
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No tienes auditorías...'),
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
    $form_state->setRedirect('usevalia.listado_auditoria');
  }
}
