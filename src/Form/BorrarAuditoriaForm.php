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
 * Description of BorrarAuditoriaForm
 *
 * @author luis
 */
class BorrarAuditoriaForm extends FormBase {

  private $auditorias;
  private $usuario;

  public function getFormId() {
    return 'borrar-auditoria-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->usuario = Drupal::currentUser()->id();
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre de la auditoría'),
      'aplicacion' => t('Aplicación'),
      'catalogo' => t('Catálogo utilizado'),
      'auditores' => t('Participantes')
    ];
    $output = [];
    $this->auditorias = $controlador->getAllAuditoriasFullFrom($this->usuario);
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
      '#empty' => t('No tienes auditorías a tu cargo...'),
      '#multiple' => FALSE
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
      if(!$controlador->borrarAuditoria($this->auditorias[$form_state->getValue('table')])) {
        $messenger->addMessage(t('No se puede borrar una auditoría evaluada.'), 'error');
      }else {
        $messenger->addMessage(t('Se ha borrado la auditoría.'));
      }
      $form_state->setRedirect('usevalia.borrar_auditoria');
    }else{
      $messenger->addMessage(t('Debes seleccionar una auditoría.'), 'error');
    }
  }
}
