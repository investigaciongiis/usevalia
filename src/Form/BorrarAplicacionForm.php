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
 * Description of BorrarAplicacionesForm
 *
 * @author luis
 */
class BorrarAplicacionForm extends FormBase {

  private $aplicaciones;

  public function getFormId() {
    return 'borrar_aplicacion_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
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
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Borrar'),
      '#attributes' => [
        'onclick' => 'if(!confirm("'.t('¿Estás seguro?').'")){return false;}'
      ],
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    if(!empty($form_state->getValue('table'))){
        if (!$controlador->borrarAplicacion($this->aplicaciones[$form_state->getValue('table')])) {
          $messenger->addMessage(t('La aplicación está siendo usada.'), 'error');
        }else {
          $messenger->addMessage(t('Se ha borrado la aplicación.'));
        }
        $form_state->setRedirect('usevalia.borrar_aplicacion');
    }else{
      $messenger->addMessage(t('Debes seleccionar una aplicación.'), 'error');
    }
  }

}
