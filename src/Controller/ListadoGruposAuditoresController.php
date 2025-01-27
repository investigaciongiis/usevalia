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

use Drupal\Core\Controller\ControllerBase;

/**
 * Description of ListadoGruposAuditoresController
 *
 * @author luis
 */
class ListadoGruposAuditoresController extends ControllerBase {

  public function content() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $controlador = \Drupal::service('usevalia.controlador');
    $header_grupos = [
      'nombre' => t('Nombre del grupo'),
      'miembros' => t('Miembros del grupo'),
      'descripcion' => t('Descripción'),
      'etiquetas' => t('Etiquetas')
    ];
    $output_grupos = [];
    $gruposAuditores = $controlador->getGruposByUsuarioId($this->currentUser()->id());
    foreach ($gruposAuditores as $grupo) {
      $miembros = [];
      foreach ($grupo->__get('auditores') as $miembro) {
        array_push($miembros, $miembro->__get('nombre'));
      }
      $output_grupos[$grupo->__get('id')] = [
        'nombre' => $grupo->__get('nombre'),
        'miembros' => implode(', ', $miembros),
        'descripcion' => $grupo->__get('descripcion'),
        'etiquetas' => implode(', ', $grupo->__get('etiquetas'))
      ];
    }
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header_grupos,
      '#rows' => $output_grupos,
      '#empty' => t('No hay grupos de auditores creados...')
    ];
    return $build;
  }

}
