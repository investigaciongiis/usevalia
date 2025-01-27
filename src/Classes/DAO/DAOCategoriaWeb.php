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

/**
 * Description of DAOCategoriaWeb
 *
 * @author Celia 
 */
class DAOCategoriaWeb {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function getAll() {
    $resultado = $this->ds->select('usevalia__categoria_web', 'c')
      ->fields('c', [
        'id',
        'nombre'
      ])
      ->execute()
      ->fetchAll();
    $categorias = [];
    foreach ($resultado as $fila) {
        $categorias[$fila->id] = new CategoriaWeb($fila->id, $fila->nombre);
    }
    return $categorias;
  }

  public function getBy($id) {
    $query = 'SELECT id, nombre FROM {usevalia__categoria_web} WHERE id = :id';
    $resultado = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $categoria = new CategoriaWeb($resultado[0]->id, $resultado[0]->nombre);
    return $categoria;
  }

}
