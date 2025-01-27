<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\usevalia\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of BorrarGrupoAuditoresForm
 *
 * @author luis
 */
class BorrarGrupoForm extends FormBase {

  private $gruposAuditores;

  public function getFormId() {
    return 'borrar-grupo-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $renderCache = Drupal::service('cache.render');
    $renderCache->invalidateAll();

    $controlador = Drupal::service('usevalia.controlador');
    $header_grupos = [
      'nombre' => t('Nombre del grupo'),
      'miembros' => t('Miembros del grupo'),
      'descripcion' => t('Descripción'),
      'etiquetas' => t('Etiquetas')
    ];
    $output_grupos = [];
    $this->gruposAuditores = $controlador->getGruposByUsuarioId($this->currentUser()->id());
    foreach ($this->gruposAuditores as $grupo) {
      $miembros = [];
      foreach ($grupo->__get('auditores') as $miembro) {
        array_push($miembros, $miembro->__get('nombre'));
      }
      $output_grupos[$grupo->__get('id')] = [
        'nombre' => $grupo->__get('nombre'),
        'miembros' => implode(', ', $miembros),
        'descripcion' => $grupo->__get('descripcion'),
        'etiquetas' => implode(', ', $grupo->__get('etiquetas'))
      ];
    }
    $form['#cache']['max-age'] = 0;
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header_grupos,
      '#options' => $output_grupos,
      '#empty' => t('No hay grupos de auditores creados...'),
      '#multiple' => FALSE,
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Borrar'),
      '#attributes' => [
        'onclick' => 'if(!confirm("'.t('¿Estás seguro?').'")){return false;}'
      ],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    if(!empty($form_state->getValue('table'))){
        if (!$controlador->borrarGrupoAuditores($this->gruposAuditores[$form_state->getValue('table')])) {
          $messenger->addMessage(t('El grupo de auditores está siendo usado.'), 'error');
        }else {
          $messenger->addMessage(t('Se ha borrado el grupo de auditores.'));
        }
        $form_state->setRedirect('usevalia.borrar_grupo');
    }else{
      $messenger->addMessage(t('Debes seleccionar un grupo de auditores.'), 'error');
    }
  }
}
