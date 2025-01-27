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
 * Description of ListadoAplicacionesController
 *
 * @author luis
 */
class ListadoAplicacionesController extends ControllerBase
{

  public function content()
  {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $header = [
      'nombre' => t('Nombre'),
      'categoria' => t('Categoría'),
      'url' => t('URL'),
      'descripcion' => t('Descripción'),
    ];
    $output = [];
    $aplicaciones = $controlador->getAllAplicaciones();
    foreach ($aplicaciones as $aplicacion) {
      $output[$aplicacion->__get('id')] = [
        'nombre' => $aplicacion->__get('nombre'),
        'categoria' => t($aplicacion->__get('categoria')->__get('nombre')),
        'url' => $aplicacion->__get('url'),
        'descripcion' => $aplicacion->__get('descripcion'),
      ];
    }
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#empty' => t('No tienes aplicaciones...')
    ];
    return $build;
  }

}
