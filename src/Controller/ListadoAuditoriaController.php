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

/**
 * Description of ListadoAuditoriaController
 *
 * @author celia
 */
class ListadoAuditoriaController extends ControllerBase
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
    $template = '<h1>{{nombre}}</h1>' . '<ul id="info-auditoria"><li><b>Aplicación</b>: {{aplicacion}}</li>' .
      '<li><b>Catálogo</b>: {{catalogo}}</li>' . '<li><b>Auditores</b>: {{auditores}}</li>' .
      '<li><b>Tipo de evaluación</b>: {{evaluacion}}</li>' .
      '<li><b>Objetivo</b>: {{objetivo}}</li>' . '<li><b>Auditores finalizados</b>: {{fin}}</li>' .
      '<li><b>Auditores a medio</b>: {{medio}}</li>' . '<li><b>Auditores sin empezar</b>: {{nada}}</li></ul>';
    $auditoria = $this->getAuditoria($controlador);
    if (empty($auditoria)) {
      return $this->redirect('usevalia.mostrar_auditorias');
    }

    $auditores = $auditoria->__get('catalogo')->__get('grupoAuditores')->__get('auditores');
    $puntuaciones = [
      'todo' => [],
      'parcial' => [],
      'nada' => []
    ];
    $tasks = 1;
    $nDirectrices = $auditoria->__get('catalogo')->getNumeroDirectrices();
    if ($auditoria->__get('evaluacion') === 'tareas') {
      $tasks = count($controlador->getTareasByAuditoria($auditoria));
    }else if($auditoria->__get('evaluacion') === 'basica'){
      $nDirectrices = count($auditoria->__get('catalogo')->getDirectricesPrioritarias());
    }
    foreach ($auditores as $auditor) {
      $participacion = count($auditoria->getPuntuacionesByUsuario($auditor));
      if($auditoria->__get('evaluacion') === 'tareas') {
        $participacion = 0;
        foreach ($controlador->getTareasByAuditoria($auditoria) as $tarea) {
          $participacion += count($auditoria->getPuntuacionesByUsuarioTarea($auditor, $tarea));
        }
      }
      if ($participacion <= 0) {
        $puntuaciones['nada'][] = $auditor->__get('nombre');
      } else if ($participacion >= $nDirectrices * $tasks) {
        $puntuaciones['todo'][] = $auditor->__get('nombre');
      } else {
        $puntuaciones['parcial'][] = $auditor->__get('nombre');
      }
    }
    $build['intro'] = [
      '#type' => 'inline_template',
      '#template' => t($template),
      '#context' => [
        'nombre' => $auditoria->__get('nombre'),
        'aplicacion' => $auditoria->__get('aplicacion')->__get('nombre'),
        'catalogo' => $auditoria->__get('catalogo')->__get('nombre'),
        'auditores' => $auditoria->getComponentes(),
        'evaluacion' => t($auditoria->getEvaluacionNombre()),
        'objetivo' => $auditoria->__get('descripcion'),
        'fin' => implode(', ', $puntuaciones['todo']),
        'medio' => implode(', ', $puntuaciones['parcial']),
        'nada' => implode(', ', $puntuaciones['nada'])
      ]
    ];

    $cabeceraDirectrices = '<h1>Directrices a evaluar</h1>';

    $build['cabeceraDirect'] = [
      '#type' => 'inline_template',
      '#template' => t($cabeceraDirectrices)
    ];

    $header = [
      'nombre' => t('Nombre'),
      'prioridad' => t('Prioridad')
    ];
    $output = [];
    $directrices = $auditoria->__get('catalogo')->getAllDirectrices();
    if($auditoria->__get('evaluacion') === 'basica')
      $directrices = $auditoria->__get('catalogo')->getDirectricesPrioritarias();

    foreach ($directrices as $directriz) {
      $output[$directriz->__get('iid')] = [
        'nombre' => $directriz->__get('nombre'),
        'prioridad' => t($directriz->__get('peso')->__get('nombre'))
      ];
    }
    $build['directrices'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#empty' => t('No hay directrices en este catálogo...')
    ];

    if($auditoria->__get('evaluacion') === 'tareas'){
      $outputTareas = [];
      foreach ($controlador->getTareasByAuditoria($auditoria) as $tarea){
        $outputTareas[] = t($tarea->__get('nombre'));
      }

      $build['tareas'] = [
        '#theme' => 'item_list',
        '#list_type' => 'li',
        '#title' => t('Tareas a realizar'),
        '#items' => $outputTareas
      ];
    }
    return $build;
  }
}
