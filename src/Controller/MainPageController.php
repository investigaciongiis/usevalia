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
 * Description of MainPageController
 *
 * @author luis
 */
class MainPageController extends ControllerBase {

  public function content() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $texto = '';
    if (Drupal::currentUser()->isAnonymous()) {
      $texto = 'Bienvenido a Usevalia, esta herramienta está destinada a su uso en auditorías de usabilidad, concretamente
      auditorías con evaluación heurística. Podrás importar tus propios catálogos de directrices para
      usarlos en diferentes auditorías. También dispones de varios tipos de evaluación, básica, estándar y por tareas.
      Al completar una auditoría podrás descarga un informe generado por Usevalia con la resolución de la misma, además tendrás
      a tu disposición diversas estadísticas que no se incluyen en los informes.';
    }
    else {
      $texto = 'Bienvenido a Usevalia.';
    }
    $build['text'] = [
      '#type' => 'inline_template',
      '#template' => '{{msg}}',
      '#context' => [
        'msg' => t($texto)
      ]
    ];
    return $build;
  }

}
