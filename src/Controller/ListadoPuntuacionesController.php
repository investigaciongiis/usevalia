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
 * Description of ListadoPuntuacionesController
 *
 * @author luis
 */
class ListadoPuntuacionesController extends ControllerBase {

  public function content() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre del esquema'),
      'descripcion' => t('Descripción'),
      'valores' => t('Lista de valores')
    ];
    $puntuaciones = $controlador->getAllEscalasPuntuaciones();
    $output = [];
    foreach ($puntuaciones as $puntuacion) {
      $valores = [];
      foreach ($puntuacion->__get('valores') as $valor) {
        $valores[] = t($valor);
      }
      $output[$puntuacion->__get('id')] = [
        'nombre' => $puntuacion->__get('nombre'),
        'descripcion' => $puntuacion->__get('descripcion'),
        'valores' => implode(', ', $valores)
      ];
    }
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#empty' => t('No hay esquemas de puntuación creados...')
    ];
    return $build;
  }
}
