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
 * Description of CerrarAuditoriaForm
 *
 * @author luis
 */
class CerrarAuditoriaForm extends FormBase {

  private $auditorias;
  private $usuario;

  public function getFormId() {
    return 'cerrar-auditoria-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->usuario = Drupal::currentUser()->id();
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre de la auditoría'),
      'aplicacion' => t('Aplicación'),
      'catalogo' => t('Catálogo'),
      'auditores' => t('Participantes')
    ];
    $output = [];
    $this->auditorias = $controlador->getAuditoriasFullAbiertasFrom($this->usuario);
    foreach ($this->auditorias as $auditoria) {
      $participantes = [];
      foreach ($auditoria->__get('participantes') as $participante) {
        $participantes[] = $participante->__get('nombre');
      }
      $output[$auditoria->__get('id')] = [
        'nombre' => $auditoria->__get('nombre'),
        'aplicacion' => $auditoria->__get('aplicacion')->__get('nombre'),
        'catalogo' => $auditoria->__get('catalogo')->__get('nombre'),
        'auditores' => implode(', ', $participantes)
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No tienes auditorías abiertas...'),
      '#multiple' => FALSE
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Cerrar')
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    if(!empty($form_state->getValue('table'))){
        $controlador->cerrarAuditoria($this->auditorias[$form_state->getValue('table')]);
        $messenger->addMessage(t('La auditoría "'). $this->auditorias[$form_state->getValue('table')]->__get('nombre') . t('" se ha cerrado correctamente.'));
        $form_state->setRedirect('usevalia.cerrar_auditoria');
    }else{
      $messenger->addMessage(t('Debes seleccionar una auditoría.'), 'error');
    }
  }

}
