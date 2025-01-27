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
use Drupal\usevalia\Classes\Aplicacion;
use Drupal\usevalia\Classes\CategoriaWeb;

/**
 * Description of DAOAplicacion
 *
 * @author luis
 */
class DAOAplicacion {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, $url, $descripcion, CategoriaWeb $categoria) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    if (!is_string($url)) {
      throw new Exception('La URL debe ser un string');
    }
    if (!is_string($descripcion)) {
      throw new Exception('La descripcion debe ser un string');
    }
    $app_id = $this->ds->insert('usevalia__aplicacion')
      ->fields(([
        'nombre' => $nombre,
        'url' => $url,
        'descripcion' => $descripcion,
        'categoria' => $categoria->__get('id')
      ]))
      ->execute();
    return new Aplicacion($app_id, $nombre, $url, $descripcion, $categoria);
  }

  public function getAll() {
    $resultado = $this->ds->select('usevalia__aplicacion', 'a')
      ->fields('a', [
        'id',
        'nombre',
        'url',
        'descripcion',
        'categoria'
      ])
      ->execute()
      ->fetchAll();
    $aplicaciones = [];
    foreach ($resultado as $fila) {
      $query_categoria = 'SELECT id, nombre FROM {usevalia__categoria_web} WHERE id = :id';
      $resultado_categoria = $this->ds->query($query_categoria, [
          ':id' =>$fila->categoria
      ])->fetchAll();
      $cat = new CategoriaWeb($resultado_categoria[0]->id, $resultado_categoria[0]->nombre);
      $aplicaciones[$fila->id] = new Aplicacion($fila->id, $fila->nombre, $fila->url, $fila->descripcion, $cat);
    }
    return $aplicaciones;
  }

  public function get($id) {
    $query = 'SELECT id, nombre, url, descripcion, categoria FROM {usevalia__aplicacion} WHERE id = :id';
    $resultado = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $resultado = $resultado[0];
    $query_categoria = 'SELECT * FROM {usevalia__categoria_web} WHERE id = :id';
    $resultado_categoria = $this->ds->query($query_categoria, [
        ':id' =>$resultado->categoria
    ])->fetchAll();
    $cat = new CategoriaWeb($resultado_categoria[0]->id, $resultado_categoria[0]->nombre);
    return new Aplicacion($resultado->id, $resultado->nombre, $resultado->url, $resultado->descripcion, $cat);
  }

  public function update($id, $nombre, $url, $descripcion, CategoriaWeb $categoria) {
    $this->ds->update('usevalia__aplicacion')->fields([
      'nombre' => $nombre,
      'url' => $url,
      'descripcion' => $descripcion,
      'categoria' => $categoria->__get('id')
    ])->condition('id', $id, '=')->execute();
  }

  public function delete(Aplicacion $app) {
    return $this->ds->delete('usevalia__aplicacion')->condition('id', $app->__get('id'))->execute();
  }

}
