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

namespace Drupal\usevalia\Classes;

use DateTime;
use Drupal;

/**
 * Description of Auditoria
 *
 * @author luis
 */
class Auditoria
{

  private $id;
  // int
  private $nombre;
  // string
  private $descripcion;
  // string
  private $fechaInicio;
  // DateTime
  private $fechaFinEstimada;
  // DateTime
  private $fechaFinReal;
  // DateTime
  private $aplicacion;
  // Aplicacion
  private $puntuacion;
  // array<Puntuacion> - Tabla externa
  private $participantes;
  // array<Auditor> (array de usuarios) - Tabla externa
  private $administrador;
  // Auditor (Usuario)
  private $catalogo;
  // Catalogo
  private $evaluacion;
  // string

  public function __construct($id, $nombre, $descripcion, DateTime $fechaInicio, DateTime $fechaFinEstimada, Aplicacion $aplicacion, Auditor $administrador, Catalogo $catalogo, array $participantes, $evaluacion)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->descripcion = $descripcion;
    $this->fechaInicio = $fechaInicio;
    $this->fechaFinEstimada = $fechaFinEstimada;
    $this->aplicacion = $aplicacion;
    $this->administrador = $administrador;
    $this->catalogo = $catalogo;
    $this->participantes = $participantes;
    $this->puntuacion = [];
    $this->evaluacion = $evaluacion;
  }

  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
  }

  public function addPuntuacion(Puntuacion $puntuacion)
  {
    $this->puntuacion[$puntuacion->__get('id')] = $puntuacion;
  }

  public function addManyPuntuaciones(array $puntuaciones)
  {
    foreach ($puntuaciones as $puntuacion) {
      $this->addPuntuacion($puntuacion);
    }
  }

  public function finalizar(DateTime $fecha)
  {
    if (!isset($this->fechaFinReal)) {
      $this->fechaFinReal = $fecha;
    }
  }

  public function reabrir()
  {
    if (isset($this->fechaFinReal)) {
      unset($this->fechaFinReal);
    }
  }

  public function getPuntuacionesByUsuario(Auditor $usuario)
  {
    $puntuaciones = [];
    foreach ($this->puntuacion as $puntuacion) {
      if ($puntuacion->__get('usuario')->__get('id') === $usuario->__get('id')) {
        if ($this->evaluacion == 'basica' && $puntuacion->__get('directriz')->getPrioridad()!=='Bajo') {
          $puntuaciones[$puntuacion->__get('directriz')->__get('iid')] = $puntuacion;
        } else if ($this->evaluacion != 'basica') {
          $puntuaciones[$puntuacion->__get('directriz')->__get('iid')] = $puntuacion;
        }
      }
    }
    return $puntuaciones;
  }

  public function getPuntuacionesByUsuarioTarea(Auditor $usuario, Tarea $tarea)
  {
    $puntuaciones = [];
    foreach ($this->puntuacion as $puntuacion) {
      if ($puntuacion->__get('usuario')->__get('id') === $usuario->__get('id')) {
        if ($puntuacion->__get('tarea')->__get('id') === $tarea->__get('id')) {
          $puntuaciones[$puntuacion->__get('directriz')->__get('iid')] = $puntuacion;
        }
      }
    }
    return $puntuaciones;
  }

  public function getPuntuacionesByDirectriz(Directriz $directriz)
  {
    $puntuaciones = [];
    foreach ($this->puntuacion as $puntuacion) {
      if ($puntuacion->__get('directriz')->__get('iid') === $directriz->__get('iid')) {
        $puntuaciones[$puntuacion->__get('id')] = $puntuacion;
      }
    }
    return $puntuaciones;
  }

  public function getPuntuacionesByDirectrizTarea(Directriz $directriz, Tarea $tarea)
  {
    $puntuaciones = [];
    foreach ($this->puntuacion as $puntuacion) {
      if ($puntuacion->__get('directriz')->__get('iid') === $directriz->__get('iid')) {
        if ($puntuacion->__get('tarea')->__get('id') === $tarea->__get('id')) {
          $puntuaciones[$puntuacion->__get('id')] = $puntuacion;
        }
      }
    }
    return $puntuaciones;
  }

  public function getValoracionesByDirectriz(Directriz $directriz)
  {
    $valoraciones = [];
    foreach ($this->puntuacion as $puntuacion) {
      if ($puntuacion->__get('directriz')->__get('iid') === $directriz->__get('iid')) {
        array_push($valoraciones, $puntuacion->__get('usuario')->__get('nombre') . ': ' . $puntuacion->__get('observacion'));
      }
    }
    return $valoraciones;
  }

  public function getPuntuacionesDesglosadas()
  {
    $puntuaciones = [];
    foreach ($this->participantes as $auditor) {
      $puntuaciones[$auditor->__get('id')] = count($this->getPuntuacionesByUsuario($auditor));
    }
    return $puntuaciones;
  }

  public function getCategoriaApp()
  {
    return $this->aplicacion->__get('categoria');
  }

  public function getCategoriaTareas()
  {
    return $this->aplicacion->__get('categoria')->__get('tareas');
  }

  public function getComponentes()
  {
    $participantes = [];
    foreach ($this->participantes as $participante) {
      array_push($participantes, $participante->__get('nombre'));
    }
    return implode(', ', $participantes);
  }

  public function getEvaluacionNombre()
  {
    if ($this->evaluacion == 'tareas')
      return 'Tareas';
    else if ($this->evaluacion == 'basica')
      return 'Básica';
    return 'Estándar';
  }

  public function getEstadoEvaluacion()
  {
    $auditores = $this->__get('catalogo')->__get('grupoAuditores')->__get('auditores');
    $puntuaciones = [
      'todo' => [],
      'parcial' => [],
      'nada' => []
    ];
    $tasks = 1;
    $controlador = Drupal::service('usevalia.controlador');
    $nDirectrices = $this->__get('catalogo')->getNumeroDirectrices();
    if ($this->__get('evaluacion') === 'tareas') {
      $tasks = count($controlador->getTareasByAuditoria($this));
    }else if($this->__get('evaluacion') === 'basica'){
      $nDirectrices = count($this->__get('catalogo')->getDirectricesPrioritarias());
    }
    foreach ($auditores as $auditor) {
      $participacion = count($this->getPuntuacionesByUsuario($auditor));
      if($this->__get('evaluacion') === 'tareas') {
        $participacion = 0;
        foreach ($controlador->getTareasByAuditoria($this) as $tarea) {
          $participacion += count($this->getPuntuacionesByUsuarioTarea($auditor, $tarea));
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

    $estado = t('completada');
    $count = count($auditores) - count($puntuaciones['nada']) - count($puntuaciones['parcial']);
    if($count != count($auditores)){
      $estado = t('pendiente');
    }

    return $count . '/' . count($auditores) . ' - ' . $estado;
  }
  /*public function getEstadoEvaluacion()
  {
	$puntuaciones = [
		'todo' => 0,
		'parcial' => 0,
		'nada' => 0
	];
	foreach ($this->getPuntuacionesDesglosadas() as $participacion) {
		if ($participacion <= 0) {
			$puntuaciones['nada'] += 1;
		} else if ($participacion >= $this->__get('catalogo')->getNumeroDirectrices()) {
			$puntuaciones['todo'] += 1;
		} else {
			$puntuaciones['parcial'] += 1;
		}
	}
	$estado = t('completada');
	$count = count($this->participantes) - $puntuaciones['nada'] - $puntuaciones['parcial'];
	if($count != count($this->participantes)){
		$estado = t('pendiente');
	}
	//$participantes = '';
	//foreach ($this->participantes as $participante) {
	//	$participantes .= $participante->__get('nombre').' ';
	//}
	//return $count . '/' . $puntuaciones['nada'] . '/' . $puntuaciones['parcial'] . '/' .  count($this->participantes) . ' - ' . $estado;
    return $count . '/' . count($this->participantes) . ' - ' . $estado;
	//return $participantes;
  }*/

public function getEstadoColorEvaluacion()
  {
    $auditores = $this->__get('catalogo')->__get('grupoAuditores')->__get('auditores');
    $puntuaciones = [
      'todo' => [],
      'parcial' => [],
      'nada' => []
    ];
    $tasks = 1;
    $controlador = Drupal::service('usevalia.controlador');
    $nDirectrices = $this->__get('catalogo')->getNumeroDirectrices();
    if ($this->__get('evaluacion') === 'tareas') {
      $tasks = count($controlador->getTareasByAuditoria($this));
    }else if($this->__get('evaluacion') === 'basica'){
      $nDirectrices = count($this->__get('catalogo')->getDirectricesPrioritarias());
    }
    foreach ($auditores as $auditor) {
      $participacion = count($this->getPuntuacionesByUsuario($auditor));
      if($this->__get('evaluacion') === 'tareas') {
        $participacion = 0;
        foreach ($controlador->getTareasByAuditoria($this) as $tarea) {
          $participacion += count($this->getPuntuacionesByUsuarioTarea($auditor, $tarea));
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

    $estado = 'background: #00FA9A; color: black;';
    $count = count($auditores) - count($puntuaciones['nada']) - count($puntuaciones['parcial']);
    if($count != count($auditores)){
      //$estado = 'background: #E75858; color: white;';
      $estado = '';
    }

    return $estado;
  }
  /*public function getEstadoColorEvaluacion()
  {
	$puntuaciones = [
		'todo' => 0,
		'parcial' => 0,
		'nada' => 0
	];
	foreach ($this->getPuntuacionesDesglosadas() as $participacion) {
		if ($participacion <= 0) {
			$puntuaciones['nada'] += 1;
		} else if ($participacion >= $this->__get('catalogo')->getNumeroDirectrices()) {
			$puntuaciones['todo'] += 1;
		} else {
			$puntuaciones['parcial'] += 1;
		}
	}
	$estado = 'background: #00FA9A; color: black;';
	$count = count($this->participantes) - $puntuaciones['nada'] - $puntuaciones['parcial'];
	if($count != count($this->participantes)){
		//$estado = 'background: #E75858; color: white;';
    $estado = '';
	}

    return $estado;
  }*/

    public function isCompleted()
  {
    $auditores = $this->__get('catalogo')->__get('grupoAuditores')->__get('auditores');
    $puntuaciones = [
      'todo' => [],
      'parcial' => [],
      'nada' => []
    ];
    $tasks = 1;
    $controlador = Drupal::service('usevalia.controlador');
    $nDirectrices = $this->__get('catalogo')->getNumeroDirectrices();
    if ($this->__get('evaluacion') === 'tareas') {
      $tasks = count($controlador->getTareasByAuditoria($this));
    }else if($this->__get('evaluacion') === 'basica'){
      $nDirectrices = count($this->__get('catalogo')->getDirectricesPrioritarias());
    }
    foreach ($auditores as $auditor) {
      $participacion = count($this->getPuntuacionesByUsuario($auditor));
      if($this->__get('evaluacion') === 'tareas') {
        $participacion = 0;
        foreach ($controlador->getTareasByAuditoria($this) as $tarea) {
          $participacion += count($this->getPuntuacionesByUsuarioTarea($auditor, $tarea));
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

    $count = count($auditores) - count($puntuaciones['nada']) - count($puntuaciones['parcial']);
    if($count != count($auditores)){
      return false;
    }

    return true;
  }

  
  public function getParticipantesSinLider()
  {
    $auditores = [];
    foreach ($this->participantes as $auditor) {
      if ($auditor->__get('id') != $this->administrador->__get('id'))
        $auditores[$auditor->__get('id')] = $auditor;
    }
    return $auditores;
  }

  public function calcularResultado($prioridades)
  {
    $conteo = [];
    $fallos = [];
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($prioridades as $prioridad) {
      $conteo[$prioridad->__get('nombre')] = 0;
      $fallos[$prioridad->__get('nombre')] = $prioridad->__get('fallos');
    }
    foreach ($this->participantes as $user) {
      $puntuaciones = $this->getPuntuacionesByUsuario($user);
      if($this->evaluacion == 'tareas'){
        $controlador = Drupal::service('usevalia.controlador');
        foreach ($controlador->getTareasByAuditoria($this) as $tarea) {
          $puntuaciones = $this->getPuntuacionesByUsuarioTarea($user, $tarea);
          $check = $this->check($conteo, $fallos, $puntuaciones, $tiposPuntuaciones);
          if($check === true)
            return 'no ha sido superada.';
          else
            $conteo = $check;
        }
      }else{
        $check = $this->check($conteo, $fallos, $puntuaciones, $tiposPuntuaciones);
        if($check === true)
          return 'no ha sido superada.';
        else
          $conteo = $check;
      }
    }
    // Caso en el que ninguna supere su límite.
    return 'ha sido superada con éxito.';
  }

  private function check($conteo, $fallos, $puntuaciones, $tiposPuntuaciones) {
    // Sumamos todos los fallos de cada una de las prioridades.
    foreach ($puntuaciones as $puntuacion) {
      if ($tiposPuntuaciones[$puntuacion->__get('puntuacion')] == 'fallo')
        $conteo[$puntuacion->__get('directriz')->getPrioridad()]++;

      // Cuando una prioridad supere su límite de fallos se termina.
      if ($conteo[$puntuacion->__get('directriz')->getPrioridad()] > $fallos[$puntuacion->__get('directriz')->getPrioridad()])
        return true;
    }
    return $conteo;
  }

  public function getPuntuacionesByTipo()
  {
    $puntuaciones['fallo'] = 0;
    $puntuaciones['aprobado'] = 0;
    $puntuaciones['N/A'] = 0;
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($this->puntuacion as $puntuacion) {
      $puntuaciones[$tiposPuntuaciones[$puntuacion->__get('puntuacion')]]++;
    }
    return $puntuaciones;
  }

  public function desgloseByDirectriz(Directriz $directriz)
  {
    $puntuaciones['fallo'] = 0;
    $puntuaciones['aprobado'] = 0;
    $puntuaciones['N/A'] = 0;
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($this->getPuntuacionesByDirectriz($directriz) as $puntuacion) {
      $puntuaciones[$tiposPuntuaciones[$puntuacion->__get('puntuacion')]]++;
    }
    return $puntuaciones;
  }

  public function getDirectricesSortedPrioridad()
  {
    $sorted['Bajo']['aprobado'] = 0;
    $sorted['Bajo']['fallo'] = 0;
    $sorted['Bajo']['N/A'] = 0;
    $sorted['Medio']['aprobado'] = 0;
    $sorted['Medio']['fallo'] = 0;
    $sorted['Medio']['N/A'] = 0;
    $sorted['Alto']['aprobado'] = 0;
    $sorted['Alto']['fallo'] = 0;
    $sorted['Alto']['N/A'] = 0;
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($this->puntuacion as $puntuacion) {
      $sorted[$puntuacion->__get('directriz')->getPrioridad()][$tiposPuntuaciones[$puntuacion->__get('puntuacion')]]++;
    }
    return $sorted;
  }

  public function getDirectricesSortedGrupo(GrupoDirectrices $grupoDirectrices)
  {
    $sorted['fallo'] = 0;
    $sorted['aprobado'] = 0;
    $sorted['N/A'] = 0;
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($this->puntuacion as $puntuacion) {
      if($grupoDirectrices->hasDirectriz($puntuacion->__get('directriz')))
        $sorted[$tiposPuntuaciones[$puntuacion->__get('puntuacion')]]++;
    }
    return $sorted;
  }

  public function getDirectricesSortedTarea(Tarea $tarea)
  {
    $sorted['fallo'] = 0;
    $sorted['aprobado'] = 0;
    $sorted['N/A'] = 0;
    $tiposPuntuaciones = $this->catalogo->__get('esquemaPuntuacion')->__get('tipos');
    foreach ($this->catalogo->getAllDirectrices() as $directriz) {
      $puntuaciones = $this->getPuntuacionesByDirectrizTarea($directriz, $tarea);
      foreach ($puntuaciones as $puntuacion) {
        $sorted[$tiposPuntuaciones[$puntuacion->__get('puntuacion')]]++;
      }
    }
    return $sorted;
  }
}
