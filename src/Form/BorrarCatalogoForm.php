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
 * Description of BorrarCatalogoForm
 *
 * @author luis
 */
class BorrarCatalogoForm extends FormBase {

  private $catalogos;

  public function getFormId() {
    return 'borrar-catalogo-form';
  }

  private function getCatalogo($controlador) {
    $renderCache = Drupal::service('cache.render');
    $renderCache->invalidateAll();

    $tempstore = Drupal::service('user.private_tempstore')->get('usevalia_temp');
    $id = $tempstore->get('table');
    if (empty($id)) {
      return $id;
    }
    $tempstore->set('table', null);
    return $controlador->getFullCatalogoById($id);
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre del catálogo'),
      'puntuacion' => t('Esquema de puntuación'),
      'admin' => t('Creador'),
      'grupo' => t('Grupo asignado'),
      'lectura' => t('Permiso de lectura'),
      'escritura' => t('Permiso de escritura')
    ];
    $output = [];
    $this->catalogos = $controlador->getCatalogosByUsuarioPropietario(Drupal::currentUser()->id());
    foreach ($this->catalogos as $catalogo) {
      $output[$catalogo->__get('id')] = [
        'nombre' => $catalogo->__get('nombre'),
        'puntuacion' => $catalogo->__get('esquemaPuntuacion')->__get('nombre'),
        'admin' => $catalogo->__get('autor')->__get('nombre'),
        'grupo' => $catalogo->__get('grupoAuditores')->__get('nombre'),
        'lectura' => $catalogo->__get('permisoLectura'),
        'escritura' => $catalogo->__get('permisoEscritura')
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
        if (!$controlador->borrarCatalogo($this->catalogos[$form_state->getValue('table')])) {
          $messenger->addMessage(t('El catálogo está siendo usado.'), 'error');
        }else {
          $messenger->addMessage(t('Se ha borrado el catálogo.'));
        }
        $form_state->setRedirect('usevalia.borrar_catalogo');
    }else{
      $messenger->addMessage(t('Debes seleccionar un catálogo.'), 'error');
    }
  }
}
