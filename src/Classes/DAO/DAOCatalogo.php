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
use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\usevalia\Classes\GrupoAuditores;

/**
 * Description of Catalogo
 *
 * @author luis
 */
class DAOCatalogo {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, EsquemaPuntuacion $esquema, Auditor $autor, GrupoAuditores $grupoAuditores, $permisoLectura, $permisoEscritura) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    $id_cat = $this->ds->insert('usevalia__catalogo')
      ->fields(([
        'nombre' => $nombre,
        'esquema' => $esquema->__get('id'),
        'autor' => $autor->__get('id'),
        'grupo' => $grupoAuditores->__get('id'),
        'lectura' => $permisoLectura,
        'escritura' => $permisoEscritura
      ]))
      ->execute();
    return new Catalogo($id_cat, $nombre, $esquema, $autor, $grupoAuditores, $permisoLectura, $permisoEscritura);
  }

  public function getAll(array $esquemas, array $auditores, array $grupos) {
    $catalogos = $this->ds->select('usevalia__catalogo', 'c')
      ->fields('c', [
        'id',
        'nombre',
        'esquema',
        'autor',
        'grupo',
        'lectura',
        'escritura'
      ])
      ->execute()
      ->fetchAll();
    $lista = [];
    foreach ($catalogos as $catalogo) {
      $lista[$catalogo->id] = new Catalogo($catalogo->id, $catalogo->nombre, $esquemas[$catalogo->esquema], $auditores[$catalogo->autor], $grupos[$catalogo->grupo], $catalogo->lectura, $catalogo->escritura);
    }
    return $lista;
  }

  public function getById($id, array $esquemas, array $auditores, array $grupos) {
    $query = 'SELECT id, nombre, esquema, autor, grupo, lectura, escritura' . ' FROM {usevalia__catalogo} WHERE id = :id';
    $usuario = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $usuario = $usuario[0];
    return new Catalogo($usuario->id, $usuario->nombre, $esquemas[$usuario->esquema], $auditores[$usuario->autor], $grupos[$usuario->grupo], $usuario->lectura, $usuario->escritura);
  }

  public function getNumEsquemasPuntuacion(EsquemaPuntuacion $esquema) {
    $query = 'SELECT COUNT(esquema) FROM {usevalia__catalogo} WHERE esquema = :id';
    return $this->ds->query($query, ['id' => $esquema->__get('id'),])->fetchField();
  }
  
  public function getNumGrupos(GrupoAuditores $grupo) {
    $query = 'SELECT COUNT(grupo) FROM {usevalia__catalogo} WHERE grupo = :id';
    return $this->ds->query($query, ['id' => $grupo->__get('id'),])->fetchField();
  }

  public function getByUsuarioIdConPermisos($id, array $esquemas, array $auditores, array $grupos) {
    $query = "SELECT id, nombre, esquema, autor, grupo, lectura, escritura" . " FROM {usevalia__catalogo} WHERE autor = :id OR lectura = 'PUBLICO'" . " OR (grupo IN (SELECT grupo FROM usevalia__grupo_auditores_usuarios WHERE usuario = :id) AND lectura = 'GRUPO')";
    $catalogos = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $lista = [];
    foreach ($catalogos as $catalogo) {
      $lista[$catalogo->id] = new Catalogo($catalogo->id, $catalogo->nombre, $esquemas[$catalogo->esquema], $auditores[$catalogo->autor], $grupos[$catalogo->grupo], $catalogo->lectura, $catalogo->escritura);
    }
    return $lista;
  }
  
  public function getByUsuarioIdPropietario($id, array $esquemas, array $auditores, array $grupos) {
    $query = "SELECT id, nombre, esquema, autor, grupo, lectura, escritura FROM {usevalia__catalogo} WHERE autor = :id";
    $catalogos = $this->ds->query($query, [
        ':id' => $id
      ])->fetchAll();
    $lista = [];
    foreach ($catalogos as $catalogo) {
      $lista[$catalogo->id] = new Catalogo($catalogo->id, $catalogo->nombre, $esquemas[$catalogo->esquema], $auditores[$catalogo->autor], $grupos[$catalogo->grupo], $catalogo->lectura, $catalogo->escritura);
    }
    return $lista;
  }
  
  public function delete(Catalogo $catalogo) {
    return $this->ds->delete('usevalia__catalogo')->condition('id', $catalogo->__get('id'))->execute();
  }
  
}
