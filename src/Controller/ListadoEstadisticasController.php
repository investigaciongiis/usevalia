<?php

/*
 * Copyright (C) 2020 celia
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
use Drupal\usevalia\Classes\Auditoria;
use Drupal\usevalia\Util\GeneradorGraficos;

/**
 * Description of ListadoEstadisticasController
 *
 * @author celia
 */
class ListadoEstadisticasController extends ControllerBase
{

  private $usuario;

  private function getAuditoria($controlador)
  {
    $tempstore = Drupal::service('user.private_tempstore')->get('usevalia_temp');
    $variable = 'controlador' . $this->usuario;
    $id = $tempstore->get('controlador' . $this->usuario);
    if (empty($id)) {
      return $id;
    }
    $tempstore->set($variable, null);
    return $controlador->getAuditoriaById($id);
  }

  public function content()
  {
    $this->usuario = Drupal::currentUser()->id();
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $auditoria = $this->getAuditoria($controlador);
    if (empty($auditoria)) {
      return $this->redirect('usevalia.generar_graficos');
    }

    $build['graphs']['graph2'] = [
      '#theme' => 'image',
      '#attributes' => [
        'class' => 'stat-img'
      ],
      '#uri' => GeneradorGraficos::porcetanjesFalloPass($auditoria)->getFileUri(),
    ];

    $build['graphs']['graph1'] = [
      '#theme' => 'image',
      '#attributes' => [
        'class' => 'stat-img'
      ],
      '#uri' => GeneradorGraficos::resultadosPorPrioridad($auditoria)->getFileUri(),
    ];

    $build['graphs1']['graph'] = [
      '#theme' => 'image',
      '#attributes' => [
        'class' => 'stat-img'
      ],
      '#uri' => GeneradorGraficos::resultadosPorDirectriz($auditoria)->getFileUri(),
    ];

    $build['graphs2']['graph'] = [
      '#theme' => 'image',
      '#attributes' => [
        'class' => 'stat-img'
      ],
      '#uri' => GeneradorGraficos::porcentajesGrupos($auditoria)->getFileUri(),
    ];

    $build['graphs3']['graph'] = [
      '#theme' => 'image',
      '#attributes' => [
        'class' => 'stat-img'
      ],
      '#uri' => GeneradorGraficos::resultadosPorGrupo($auditoria)->getFileUri(),
    ];

    if($auditoria->__get('evaluacion') === 'tareas') {
      $build['graphs4']['graph'] = [
        '#theme' => 'image',
        '#attributes' => [
          'class' => 'stat-img'
        ],
        '#uri' => GeneradorGraficos::resultadosPorTarea($auditoria)->getFileUri(),
      ];
      $build['graphs5']['graph'] = [
        '#theme' => 'image',
        '#attributes' => [
          'class' => 'stat-img'
        ],
        '#uri' => GeneradorGraficos::porcetanjesTareas($auditoria)->getFileUri(),
      ];
    }
    return $build;
  }
}
