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
use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\GrupoDirectrices;

/**
 * Description of GrupoDirectrices
 *
 * @author luis
 */
class DAOGrupoDirectrices {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, Catalogo $catalogo) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    $id_grupo = $this->ds->insert('usevalia__grupo_directrices')
      ->fields(([
        'nombre' => $nombre,
        'catalogo' => $catalogo->__get('id')
      ]))
      ->execute();
    return new GrupoDirectrices($id_grupo, $nombre, $catalogo);
  }

  public function getAllByCatalogo(Catalogo $catalogo) {
    $query = 'SELECT id, nombre FROM {usevalia__grupo_directrices} WHERE catalogo = :id';
    $resultado_grupos = $this->ds->query($query, [
        ':id' => $catalogo->__get('id')
      ])
      ->fetchAll();
    $grupos = [];
    foreach ($resultado_grupos as $grupo) {
      $grupos[$grupo->id] = new GrupoDirectrices($grupo->id, $grupo->nombre, $catalogo);
    }
    return $grupos;
  }

  public function getNumEsquemasPuntuacion(EsquemaPuntuacion $esquema) {
    $query = 'SELECT COUNT(esquema) FROM {usevalia__grupo_directrices} WHERE esquema = :id';
    return $this->ds->query($query, ['id' => $esquema->__get('id'),])->fetchField();
  }

}
