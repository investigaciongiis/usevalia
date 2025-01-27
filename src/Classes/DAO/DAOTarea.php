<?php
/*
 * Copyright (C) 2020 Celia
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
namespace Drupal\usevalia\Classes\DAO;

use Drupal\usevalia\Classes\CategoriaWeb;
use Drupal\usevalia\Classes\Tarea;

/**
 * Description of DAOTarea
 *
 * @author Celia
 */
class DAOTarea {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function getAll() {
    $resultado = $this->ds->select('usevalia__tarea', 't')
      ->fields('t', [
        'id',
        'nombre',
        'categoria'
      ])
      ->execute()
      ->fetchAll();
    $categorias = [];
    foreach ($resultado as $fila) {
        $categorias[$fila->id] = new CategoriaWeb($fila->id, $fila->nombre );
    }
    return $categorias;
  }

  public function getBy($id) {
    $query = 'SELECT id, nombre, categoria FROM {usevalia__tarea} WHERE id = :id';
    $resultado = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $resultado = $resultado[0];
    $controlador = \Drupal::service('usevalia.controlador');
    $categoria = $controlador->getCategoriaById($resultado->categoria);
    return new Tarea($resultado->id, $resultado->nombre, $categoria);
  }

  public function getPrimeraByCategoria(CategoriaWeb $categoria) {
      $query = 'SELECT id, nombre, categoria FROM {usevalia__tarea} WHERE categoria = :id';
      $resultado = $this->ds->query($query, [
          ':id' => $categoria->__get('id')
      ])->fetchAll();
      $resultado = $resultado[0];
      return new Tarea($resultado->id, $resultado->nombre, $categoria);
  }

  public function getNextTarea(Tarea $tarea) {
      $query = 'SELECT id, nombre, categoria FROM {usevalia__tarea} WHERE id = :id';
      $resultado = $this->ds->query($query, [
          ':id' => $tarea->__get('id')+1
      ])->fetchAll();
      $resultado = $resultado[0];
      if($resultado->categoria == $tarea->__get('categoria')->__get('id'))
          return new Tarea($resultado->id, $resultado->nombre, $tarea->__get('categoria'));
      else
          return null;
  }

  public function getPreviousTarea(Tarea $tarea) {
      $query = 'SELECT id, nombre, categoria FROM {usevalia__tarea} WHERE id = :id';
      $resultado = $this->ds->query($query, [
          ':id' => $tarea->__get('id')-1
      ])->fetchAll();
      $resultado = $resultado[0];
      if($resultado->categoria == $tarea->__get('categoria')->__get('id'))
          return new Tarea($resultado->id, $resultado->nombre, $tarea->__get('categoria'));
      else
          return null;
  }

  public function getAllByCategoria(CategoriaWeb $categoria) {
      $query = 'SELECT id, nombre, categoria FROM {usevalia__tarea} WHERE categoria = :categoria';
      $resultado = $this->ds->query($query, [
          ':categoria' => $categoria->__get('id')
      ])->fetchAll();
      $tareas = [];
      foreach ($resultado as $tarea){
          $tareas[$tarea->id] = new Tarea($tarea->id, $tarea->nombre, $categoria);
      }
      return $tareas;
  }

}
