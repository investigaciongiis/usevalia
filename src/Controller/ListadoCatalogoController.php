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

namespace Drupal\usevalia\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

/**
 * Description of ResultController
 *
 * @author luis
 */
class ListadoCatalogoController extends ControllerBase {

  private $usuario;

  private function getCatalogo($controlador) {
    $tempstore = Drupal::service('user.private_tempstore')->get('usevalia_temp');
    $variable = 'controlador' . $this->usuario;
    $id = $tempstore->get($variable);
    if (empty($id)) {
      return $id;
    }
    $tempstore->set($variable, null);
    return $controlador->getFullCatalogoById($id);
  }

  public function content() {
    $this->usuario = Drupal::currentUser()->id();
    // Inicialización de los componentes principales
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'id' => t('Id.'),
      'nombre' => t('Nombre'),
      'prioridad' => t('Prioridad')
    ];
    $template = '<ul id="info-catalogo"><li><b>Nombre del catálogo</b>: {{nombre}}</li>' . '<li><b>Esquema de puntuación</b>: {{esquema}}</li>' . '<li><b>Dueño</b>: {{admin}}</li>' . '<li><b>Grupo</b>: {{grupo}}</li>' . '<li><b>Permiso de lectura</b>: {{lectura}}</li>' . '<li><b>Permiso de escritura</b>: {{escritura}}</li>' . '<li><b>Numero de directrices</b>: {{directrices}}</li></ul>' . '<h1>Directrices:</h1>';
    $catalogo = $this->getCatalogo($controlador);
    if (empty($catalogo)) {
      return $this->redirect('usevalia.mostrar_catalogos');
    }
    $build['intro'] = [
      '#type' => 'inline_template',
      '#template' => t($template),
      '#context' => [
        'nombre' => $catalogo->__get('nombre'),
        'esquema' => $catalogo->__get('esquemaPuntuacion')->__get('nombre'),
        'admin' => $catalogo->__get('autor')->__get('nombre'),
        'grupo' => $catalogo->__get('grupoAuditores')->__get('nombre'),
        'lectura' => t($catalogo->__get('permisoLectura')),
        'escritura' => t($catalogo->__get('permisoEscritura')),
        'directrices' => $catalogo->getNumeroDirectrices()
      ]
    ];
    $text = t('Este grupo no tiene directrices...');
    foreach ($catalogo->__get('gruposDirectrices') as $grupo) {
      $output = [];
      foreach ($grupo->__get('directrices') as $directriz) {
        $output[$directriz->__get('iid')] = [
          'id' => $directriz->__get('eid'),
          'nombre' => $directriz->__get('nombre'),
          'prioridad' => t($directriz->__get('peso')->__get('nombre'))
        ];
      }
      $build['table' . $grupo->__get('id')] = [
        '#type' => 'table',
        '#caption' => $grupo->__get('nombre'),
        '#header' => $header,
        '#rows' => $output,
        '#empty' => $text
      ];
    }
    return $build;
  }
}
