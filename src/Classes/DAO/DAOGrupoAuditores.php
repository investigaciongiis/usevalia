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
use Drupal\usevalia\Classes\GrupoAuditores;

/**
 * Description of GrupoAuditores
 *
 * @author luis
 */
class DAOGrupoAuditores {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, $descripcion, array $auditores, array $etiquetas) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    if (!is_string($descripcion)) {
      throw new Exception('La descripción debe ser un string');
    }
    $id_grupo = $this->ds->insert('usevalia__grupo_auditores')
      ->fields(([
        'nombre' => $nombre,
        'descripcion' => $descripcion
      ]))
      ->execute();
    $insert_usuarios = $this->ds->insert('usevalia__grupo_auditores_usuarios')->fields([
      'usuario',
      'grupo'
    ]);
    $insert_etiquetas = $this->ds->insert('usevalia__grupo_auditores_etiqueta')->fields([
      'etiqueta',
      'grupo'
    ]);
    foreach ($auditores as $auditor) {
      $insert_usuarios->values([
        'usuario' => $auditor->__get('id'),
        'grupo' => $id_grupo
      ]);
    }
    $insert_usuarios->execute();
    foreach ($etiquetas as $etiqueta) {
      $insert_etiquetas->values([
        'etiqueta' => $etiqueta['id'],
        'grupo' => $id_grupo
      ]);
    }
    $insert_etiquetas->execute();
    $grupo = new GrupoAuditores($id_grupo, $nombre, $descripcion, $auditores);
    $grupo->addEtiquetas($etiquetas);
    return $grupo;
  }

  public function getAll(array $auditores_todos, array $etiquetas) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__grupo_auditores_usuarios} WHERE grupo = :id';
    $query_tags = 'SELECT etiqueta FROM {usevalia__grupo_auditores_etiqueta} ' . 'WHERE grupo = :id';
    $resultado_grupo = $this->ds->select('usevalia__grupo_auditores', 'g')
      ->fields('g', [
        'id',
        'nombre',
        'descripcion'
      ])
      ->execute()
      ->fetchAll();
    $grupos = [];
    foreach ($resultado_grupo as $fila_grupo) {
      $options = [
        'id' => $fila_grupo->id
      ];
      $auditores_grupo = [];
      $resultado_auditores = $this->ds->query($query_usuarios, $options)->fetchAll();
      foreach ($resultado_auditores as $fila_auditores) {
        $auditores_grupo[$fila_auditores->usuario] = $auditores_todos[$fila_auditores->usuario];
      }
      $grupos[$fila_grupo->id] = new GrupoAuditores($fila_grupo->id, $fila_grupo->nombre, $fila_grupo->descripcion, $auditores_grupo);
      $etiquetas_grupo = [];
      $resultado_etiquetas = $this->ds->query($query_tags, $options)->fetchAll();
      foreach ($resultado_etiquetas as $fila_etiquetas) {
        array_push($etiquetas_grupo, $etiquetas[$fila_etiquetas->etiqueta]['valor']);
      }
      $grupos[$fila_grupo->id]->addEtiquetas($etiquetas_grupo);
    }
    return $grupos;
  }

  public function getByUsuarioId($id, array $auditores_todos, array $etiquetas) {
    $query_grupos = 'SELECT id, nombre, descripcion FROM {usevalia__grupo_auditores}' . ' WHERE id IN (SELECT grupo FROM {usevalia__grupo_auditores_usuarios}' . ' WHERE usuario = :id );';
    $query_usuarios = 'SELECT usuario FROM {usevalia__grupo_auditores_usuarios} WHERE grupo = :id';
    $query_tags = 'SELECT etiqueta FROM {usevalia__grupo_auditores_etiqueta} ' . 'WHERE grupo = :id';
    $resultado_grupo = $this->ds->query($query_grupos, [
        'id' => $id
      ])->fetchAll();
    $grupos = [];
    foreach ($resultado_grupo as $fila_grupo) {
      $auditores_grupo = [];
      $resultado_auditores = $this->ds->query($query_usuarios, [
          'id' => $fila_grupo->id
        ])->fetchAll();
      foreach ($resultado_auditores as $fila_auditores) {
        $auditores_grupo[$fila_auditores->usuario] = $auditores_todos[$fila_auditores->usuario];
      }
      $grupos[$fila_grupo->id] = new GrupoAuditores($fila_grupo->id, $fila_grupo->nombre, $fila_grupo->descripcion, $auditores_grupo);
      $etiquetas_grupo = [];
      $resultado_etiquetas = $this->ds->query($query_tags, [
          'id' => $fila_grupo->id
        ])->fetchAll();
      foreach ($resultado_etiquetas as $fila_etiquetas) {
        array_push($etiquetas_grupo, $etiquetas[$fila_etiquetas->etiqueta]['valor']);
      }
      $grupos[$fila_grupo->id]->addEtiquetas($etiquetas_grupo);
    }
    return $grupos;
  }

  public function delete(GrupoAuditores $grupo) {
    return $this->ds->delete('usevalia__grupo_auditores')->condition('id', $grupo->__get('id'))->execute();
  }

}
