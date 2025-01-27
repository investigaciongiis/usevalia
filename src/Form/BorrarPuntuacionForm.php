<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\usevalia\Form;

use Drupal;
use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of BorrarPuntuacionForm
 *
 * @author luis
 */
class BorrarPuntuacionForm extends FormBase {

  private $puntuaciones;

  public function getFormId() {
    return 'borrar-puntuacion-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
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
    $form['#cache']['max-age'] = 0;
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $output,
      '#empty' => t('No hay esquemas de puntuación creados...'),
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
        if (!$controlador->borrarEsquemaPuntuacion($this->puntuaciones[$form_state->getValue('table')])) {
          $messenger->addMessage(t('El esquema está siendo usado.'), 'error');
        }else {
          $messenger->addMessage(t('Se ha borrado el esquema de puntuación.'));
        }
        $form_state->setRedirect('usevalia.borrar_puntuacion');
    }else{
      $messenger->addMessage(t('Debes seleccionar un esquema de puntuación.'), 'error');
    }
  }

}
