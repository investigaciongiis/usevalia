<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\usevalia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Description of ListadoFuentesController
 *
 * @author luis
 */
class ListadoFuentesController extends ControllerBase {

  public function content() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = \Drupal::service('usevalia.controlador');
    $header_grupos = [
      'id' => t('Id.'),
      'nombre' => t('Nombre'),
      'descripcion' => t('Descripción'),
      'url' => t('URL')
    ];
    $output_grupos = [];
    $fuentes = $controlador->getAllFuentes();
    foreach ($fuentes as $fuente) {
      $output_grupos[$fuente->__get('iid')] = [
        'id' => $fuente->__get('eid'),
        'nombre' => $fuente->__get('nombre'),
        'descripcion' => $fuente->__get('descripcion'),
        'url' => $fuente->__get('url'),
      ];
    }
    $build['table'] = [
      '#type' => 'table',
      '#caption' => t('Grupos de auditores'),
      '#header' => $header_grupos,
      '#rows' => $output_grupos,
      '#empty' => t('No hay fuentes creadas...')
    ];
    return $build;
  }

}
