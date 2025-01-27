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

use DateTime;
use Exception;
use Drupal\usevalia\Classes\Aplicacion;
use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\Auditoria;
use Drupal\usevalia\Classes\Catalogo;

/**
 * Description of Auditoria
 *
 * @author luis
 */
class DAOAuditoria {

  private $ds;

  public function __construct($ds) {
    $this->ds = $ds;
  }

  public function create($nombre, $descripcion, $fechaFinEstimada, Aplicacion $aplicacion, Auditor $administrador, Catalogo $catalogo, array $participantes, $evaluacion) {
    if (!is_string($nombre)) {
      throw new Exception('El nombre debe ser un string');
    }
    if (!is_string($descripcion)) {
      throw new Exception('La descripcion debe ser un string');
    }
    $participantes[$administrador->__get('id')] = $administrador;
    // drupal_set_message(print_r($participantes, true));
    $hoy = new DateTime('now');
    $id_audit = $this->ds->insert('usevalia__auditoria')
      ->fields(([
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'fecha_inicio' => $hoy->format('Y-m-d'),
        'fecha_fin_estimada' => $fechaFinEstimada,
        'aplicacion' => $aplicacion->__get('id'),
        'administrador' => $administrador->__get('id'),
        'catalogo' => $catalogo->__get('id'),
        'evaluacion' => $evaluacion
      ]))
      ->execute();
    $peticion = $this->ds->insert('usevalia__auditoria_usuarios')->fields([
      'usuario',
      'auditoria'
    ]);
    foreach ($participantes as $usuario) {
      $peticion->values([
        'usuario' => $usuario->__get('id'),
        'auditoria' => $id_audit
      ]);
    }
    $peticion->execute();
    return new Auditoria($id_audit, $nombre, $descripcion, $hoy, DateTime::createFromFormat('Y-m-d', $fechaFinEstimada), $aplicacion, $administrador, $catalogo, $participantes, $evaluacion);
  }

  public function getFromUsuario($id, array $aplicaciones, array $auditores, array $catalogos) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__auditoria_usuarios} WHERE auditoria = :id';
    $query_auditoria = 'SELECT id, nombre, descripcion, fecha_inicio, fecha_fin_estimada, ' . 'aplicacion, administrador, catalogo, evaluacion FROM usevalia__auditoria ' . 'WHERE id IN ( ' . 'SELECT DISTINCT au.auditoria FROM usevalia__auditoria_usuarios AS au ' . 'LEFT JOIN usevalia__auditoria AS a ' . 'ON au.auditoria = a.id AND au.usuario = :id );';
    $options = [
      'id' => $id
    ];
    $resultado_auditoria = $this->ds->query($query_auditoria, $options)->fetchAll();
    $auditorias = [];
    foreach ($resultado_auditoria as $fila_auditoria) {
      $resultado_participantes = $this->ds->query($query_usuarios, [
          ':id' => $fila_auditoria->id
        ])->fetchAll();
      $participantes = [];
      foreach ($resultado_participantes as $fila_participante) {
        $participantes[$fila_participante->usuario] = $auditores[$fila_participante->usuario];
      }
      $auditorias[$fila_auditoria->id] = new Auditoria($fila_auditoria->id, $fila_auditoria->nombre, $fila_auditoria->descripcion,
            DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_inicio), DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_fin_estimada),
            $aplicaciones[$fila_auditoria->aplicacion], $auditores[$fila_auditoria->administrador], $catalogos[$fila_auditoria->catalogo], $participantes, $fila_auditoria->evaluacion);
    }
    return $auditorias;
  }

  public function getAbiertasFromUsuario($id, array $aplicaciones, array $auditores, array $catalogos) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__auditoria_usuarios} WHERE auditoria = :id';
    $query_auditoria = 'SELECT id, nombre, descripcion, fecha_inicio, fecha_fin_estimada, ' . 'aplicacion, administrador, catalogo, evaluacion FROM usevalia__auditoria ' . 'WHERE id IN ( ' . 'SELECT DISTINCT au.auditoria FROM usevalia__auditoria_usuarios AS au ' . 'LEFT JOIN usevalia__auditoria AS a ' . 'ON au.auditoria = a.id AND au.usuario = :id ) ' . 'AND fecha_fin_real IS NULL;';
    $options = [
      'id' => $id
    ];
    $resultado_auditoria = $this->ds->query($query_auditoria, $options)->fetchAll();
    $auditorias = [];
    foreach ($resultado_auditoria as $fila_auditoria) {
      $resultado_participantes = $this->ds->query($query_usuarios, [
          ':id' => $fila_auditoria->id
        ])->fetchAll();
      $participantes = [];
      foreach ($resultado_participantes as $fila_participante) {
        $participantes[$fila_participante->usuario] = $auditores[$fila_participante->usuario];
      }
      $auditorias[$fila_auditoria->id] = new Auditoria($fila_auditoria->id, $fila_auditoria->nombre, $fila_auditoria->descripcion,
            DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_inicio), DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_fin_estimada),
            $aplicaciones[$fila_auditoria->aplicacion], $auditores[$fila_auditoria->administrador], $catalogos[$fila_auditoria->catalogo], $participantes, $fila_auditoria->evaluacion);
    }
    return $auditorias;
  }

  public function getCerradasFromUsuario($id, array $aplicaciones, array $auditores, array $catalogos) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__auditoria_usuarios} WHERE auditoria = :id';
    $query_auditoria = 'SELECT id, nombre, descripcion, fecha_inicio, fecha_fin_estimada, ' . 'fecha_fin_real, aplicacion, administrador, catalogo, evaluacion FROM usevalia__auditoria ' . 'WHERE id IN ( ' . 'SELECT DISTINCT au.auditoria FROM usevalia__auditoria_usuarios AS au ' . 'LEFT JOIN usevalia__auditoria AS a ' . 'ON au.auditoria = a.id AND au.usuario = :id ) ' . 'AND fecha_fin_real IS NOT NULL;';
    $options = [
      'id' => $id
    ];
    $resultado_auditoria = $this->ds->query($query_auditoria, $options)->fetchAll();
    $auditorias = [];
    foreach ($resultado_auditoria as $fila_auditoria) {
      $resultado_participantes = $this->ds->query($query_usuarios, [
          ':id' => $fila_auditoria->id
        ])->fetchAll();
      $participantes = [];
      foreach ($resultado_participantes as $fila_participante) {
        $participantes[$fila_participante->usuario] = $auditores[$fila_participante->usuario];
      }
      $auditorias[$fila_auditoria->id] = new Auditoria($fila_auditoria->id, $fila_auditoria->nombre, $fila_auditoria->descripcion,
            DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_inicio), DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_fin_estimada),
            $aplicaciones[$fila_auditoria->aplicacion], $auditores[$fila_auditoria->administrador], $catalogos[$fila_auditoria->catalogo], $participantes, $fila_auditoria->evaluacion);
      $auditorias[$fila_auditoria->id]->finalizar(DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_fin_real));
    }
    return $auditorias;
  }

  public function getById($id, array $aplicaciones, array $auditores, array $catalogos) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__auditoria_usuarios} WHERE auditoria = :id';
    $query_auditoria = 'SELECT id, nombre, descripcion, fecha_inicio, fecha_fin_estimada, ' . 'aplicacion, administrador, catalogo, evaluacion FROM {usevalia__auditoria} WHERE id = :id';
    $options = [
      'id' => $id
    ];
    $resultado_auditoria = $this->ds->query($query_auditoria, $options)->fetchAll();
    $resultado_participantes = $this->ds->query($query_usuarios, [
        ':id' => $resultado_auditoria[0]->id
      ])->fetchAll();
    $participantes = [];
    foreach ($resultado_participantes as $fila_participante) {
      $participantes[$fila_participante->usuario] = $auditores[$fila_participante->usuario];
    }
    return new Auditoria($resultado_auditoria[0]->id, $resultado_auditoria[0]->nombre, $resultado_auditoria[0]->descripcion,
            DateTime::createFromFormat('Y-m-d', $resultado_auditoria[0]->fecha_inicio), DateTime::createFromFormat('Y-m-d', $resultado_auditoria[0]->fecha_fin_estimada),
            $aplicaciones[$resultado_auditoria[0]->aplicacion], $auditores[$resultado_auditoria[0]->administrador], $catalogos[$resultado_auditoria[0]->catalogo],
            $participantes, $resultado_auditoria[0]->evaluacion);
  }

  public function getByAdmin($id, array $aplicaciones, array $auditores, array $catalogos) {
    $query_usuarios = 'SELECT usuario FROM {usevalia__auditoria_usuarios} WHERE auditoria = :id';
    $query_auditoria = 'SELECT id, nombre, descripcion, fecha_inicio, fecha_fin_estimada, '
      . 'aplicacion, administrador, catalogo, evaluacion FROM {usevalia__auditoria} WHERE administrador = :id';
    $options = [
      'id' => $id
    ];
    $resultado_auditoria = $this->ds->query($query_auditoria, $options)->fetchAll();
    $auditorias = [];
    foreach ($resultado_auditoria as $fila_auditoria) {
      $resultado_participantes = $this->ds->query($query_usuarios, [
          ':id' => $fila_auditoria->id
        ])->fetchAll();
      $participantes = [];
      foreach ($resultado_participantes as $fila_participante) {
        $participantes[$fila_participante->usuario] = $auditores[$fila_participante->usuario];
      }
      $auditorias[$fila_auditoria->id] = new Auditoria($fila_auditoria->id, $fila_auditoria->nombre, $fila_auditoria->descripcion,
             DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_inicio), DateTime::createFromFormat('Y-m-d', $fila_auditoria->fecha_fin_estimada),
             $aplicaciones[$fila_auditoria->aplicacion], $auditores[$fila_auditoria->administrador], $catalogos[$fila_auditoria->catalogo], $participantes, $fila_auditoria->evaluacion);
    }
    return $auditorias;
  }

  public function getNumAuditorias(Aplicacion $app) {
    $query = 'SELECT COUNT(id) FROM {usevalia__auditoria} WHERE aplicacion = :id';
    return $this->ds->query($query, ['id' => $app->__get('id'),])->fetchField();
  }

  public function getNumAuditoriasCatalogo(Catalogo $catalogo) {
    $query = 'SELECT COUNT(catalogo) FROM {usevalia__auditoria} WHERE catalogo = :id';
    return $this->ds->query($query, ['id' => $catalogo->__get('id'),])->fetchField();
  }

  public function isCerrada(Auditoria $auditoria) {
    $query = 'SELECT fecha_fin_real FROM {usevalia__auditoria} WHERE catalogo = :id';
    $fecha = $this->ds->query($query, ['id' => $auditoria->__get('id'),])->fetchField();
    return isset($fecha);
  }

  public function delete(Auditoria $auditoria) {
    return $this->ds->delete('usevalia__auditoria')->condition('id', $auditoria->__get('id'))->execute();
  }

  public function cerrarAuditoria($id) {
    $hoy = new DateTime('now');
    $valor = $this->ds->update('usevalia__auditoria')
      ->fields([
        'fecha_fin_real' => $hoy->format('Y-m-d')
      ])
      ->condition('id', $id, '=')
      ->execute();
    return $hoy;
  }

  public function reabrirAuditoria($id) {
    $this->ds->update('usevalia__auditoria')
      ->fields([
        'fecha_fin_real' => NULL
      ])
      ->condition('id', $id, '=')
      ->execute();
  }

}
