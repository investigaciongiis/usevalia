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

namespace Drupal\usevalia\Classes\DAO;

use Exception;
use Drupal\usevalia\Classes\EsquemaPuntuacion;

/**
 * Description of EsquemaPuntuacion
 *
 * @author luis
 */
class DAOEsquemaPuntuacion {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, $descripcion, array $valores, array $tipos) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    if (!is_string($descripcion)) {
      throw new Exception('La descripción debe ser un string');
    }
    $id_escala = $this->ds->insert('usevalia__esquema_puntuacion')
      ->fields(([
        'nombre' => $nombre,
        'descripcion' => $descripcion
      ]))
      ->execute();
    $query = $this->ds->insert('usevalia__valor_puntuacion')->fields([
      'nombre',
      'escala',
      'tipo'
    ]);
    foreach ($valores as $valor) {
      $query->values([
        'nombre' => $valor,
        'escala' => $id_escala,
        'tipo' => $tipos[$valor]
      ]);
    }
    $query->execute();
    return new EsquemaPuntuacion($id_escala, $nombre, $descripcion, $valores, $tipos);
  }

  public function getAll() {
    $query_valores = 'SELECT nombre, escala, tipo FROM {usevalia__valor_puntuacion} WHERE escala = :escala';
    $resultado_escalas = $this->ds->select('usevalia__esquema_puntuacion', 'e')
      ->fields('e', [
        'id',
        'nombre',
        'descripcion'
      ])
      ->execute()
      ->fetchAll();
    $esquemas = [];
    foreach ($resultado_escalas as $fila_escala) {
      $resultado_valores = $this->ds->query($query_valores, [
          ':escala' => $fila_escala->id
        ])->fetchAll();
      $lista_valores = [];
      $lista_tipos = [];
      foreach ($resultado_valores as $fila_valores) {
        $lista_valores[$fila_valores->nombre] = $fila_valores->nombre;
        $lista_tipos[$fila_valores->nombre] = $fila_valores->tipo;
      }
      $esquemas[$fila_escala->id] = new EsquemaPuntuacion($fila_escala->id, $fila_escala->nombre, $fila_escala->descripcion, $lista_valores, $lista_tipos);
    }
    return $esquemas;
  }

  public function getById($id) {
    $query_escala = 'SELECT id, nombre, descripcion FROM {usevalia__esquema_puntuacion} WHERE id = :id';
    $query_valores = 'SELECT nombre, tipo FROM {usevalia__valor_puntuacion} WHERE escala = :escala';
    $resultado_escala = $this->ds->query($query_escala, [
        ':id' => $id
      ])->fetchAll();
    $resultado_valores = $this->ds->query($query_valores, [
        ':escala' => $resultado_escala[0]->id
      ])->fetchAll();
      $lista_valores = [];
      $lista_tipos = [];
      foreach ($resultado_valores as $fila_valores) {
          $lista_valores[$fila_valores->nombre] = $fila_valores->nombre;
          $lista_tipos[$fila_valores->nombre] = $fila_valores->tipo;
      }
      return new EsquemaPuntuacion($resultado_escala[0]->id, $resultado_escala[0]->nombre, $resultado_escala[0]->descripcion, $lista_valores, $lista_tipos);
  }

  public function update(EsquemaPuntuacion $esquema, $nombre, $descripcion, array $valores_nuevos) {
    $query = 'DELETE FROM {usevalia__esquema_puntuacion} WHERE escala = :id AND escala = :valor';
    $this->ds->update('usevalia__esquema_puntuacion')->fields([
      'nombre' => $nombre,
      'descripcion' => $descripcion,
    ])->condition('id', $esquema->__get('id'), '=')->execute();
    $valores_antiguos = $esquema->__get('valores');
    // Calcular valores a eliminar y crear
    $valores_eliminar = array_diff($valores_antiguos, $valores_nuevos);
    $valores_crear = array_diff($valores_nuevos, $valores_antiguos);

    if (!empty($valores_eliminar)) {
      foreach ($valores_eliminar as $valor) {
        $this->ds->query($query, [
          ':id' => $esquema->__get('id'),
          ':valor' => $valor,
          ])->execute();
      }
    }
    if (!empty($valores_crear)) {
      $query = $this->ds->insert('usevalia__valor_puntuacion')->fields([
        'nombre',
        'escala'
      ]);
      foreach ($valores_crear as $valor) {
        $query->values([
          'nombre' => $valor,
          'escala' => $esquema->__get('id')
        ]);
      }
      $query->execute();
    }
  }

  public function delete(EsquemaPuntuacion $esquema) {
    return $this->ds->delete('usevalia__esquema_puntuacion')->condition('id', $esquema->__get('id'))->execute();
  }

}
