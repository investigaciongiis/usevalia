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

use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\Auditoria;
use Drupal\usevalia\Classes\Puntuacion;
use Drupal\usevalia\Classes\Tarea;

/**
 * Description of Puntuacion
 *
 * @author luis
 */
class DAOPuntuacion
{

    private $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }

    public function createMany(Auditor $usuario, Auditoria $auditoria, array $valores_puntuaciones)
    {
        $puntuaciones = [];
        foreach ($valores_puntuaciones as $puntuacion) {
            $id_punt = $this->ds->insert('usevalia__puntuacion')
                ->fields([
                'usuario' => $usuario->__get('id'),
                'auditoria' => $auditoria->__get('id'),
                'directriz' => $puntuacion['directriz']->__get('iid'),
                'puntuacion' => $puntuacion['valor'],
                'observacion' => $puntuacion['observacion'],
                'mejora' => $puntuacion['mejora'],
            ])
                ->execute();
            $puntuaciones[$id_punt] = new Puntuacion($id_punt, $usuario, $puntuacion['directriz'], $puntuacion['valor']);
        }
        $auditoria->addManyPuntuaciones($puntuaciones);
        return $puntuaciones;
    }

    public function updateMany(Auditor $usuario, Auditoria $auditoria, array $valores_puntuaciones)
    {
        foreach ($valores_puntuaciones as $valor) {
            $this->ds->update('usevalia__puntuacion')
                ->fields([
                'puntuacion' => $valor['valor'],
                'observacion' => $valor['observacion'],
                'mejora' => $valor['mejora'],
            ])
                ->condition('id', $valor['puntuacion']->__get('id'), '=')
                ->execute();
        }
    }

    public function getAllByAuditoria(Auditoria $auditoria, array $auditores)
    {
        $puntuaciones = [];
        $directrices = $auditoria->__get('catalogo')->getAllDirectrices();
        $query_puntuaciones = 'SELECT id, usuario, directriz, puntuacion, observacion, mejora, tarea FROM {usevalia__puntuacion} WHERE auditoria = :id';
        $options = [
            'id' => $auditoria->__get('id')
        ];
        $resultado_puntuaciones = $this->ds->query($query_puntuaciones, $options)->fetchAll();
        foreach ($resultado_puntuaciones as $fila_puntuaciones) {
            if($auditoria->__get('evaluacion') == 'tareas'){
                $controlador = \Drupal::service('usevalia.controlador');
                $tarea = $controlador->getTareaById($fila_puntuaciones->tarea);
                $puntuaciones[$fila_puntuaciones->id] = new Puntuacion($fila_puntuaciones->id, $auditores[$fila_puntuaciones->usuario], $directrices[$fila_puntuaciones->directriz], $fila_puntuaciones->puntuacion, $fila_puntuaciones->observacion, $fila_puntuaciones->mejora);
                $puntuaciones[$fila_puntuaciones->id]->setTarea($tarea);
            }else{
                $puntuaciones[$fila_puntuaciones->id] = new Puntuacion($fila_puntuaciones->id, $auditores[$fila_puntuaciones->usuario], $directrices[$fila_puntuaciones->directriz], $fila_puntuaciones->puntuacion, $fila_puntuaciones->observacion, $fila_puntuaciones->mejora);
            }
        }
        return $puntuaciones;
    }

    public function getAllByAuditoriaTarea(Auditoria $auditoria, array $auditores, Tarea $tarea)
    {
        $puntuaciones = [];
        $directrices = $auditoria->__get('catalogo')->getAllDirectrices();
        $query_puntuaciones = 'SELECT id, usuario, directriz, puntuacion, observacion, mejora, tarea FROM {usevalia__puntuacion} WHERE auditoria = :id AND tarea = :tarea';
        $options = [
            'id' => $auditoria->__get('id'),
            'tarea' => $tarea->__get('id')
        ];
        $resultado_puntuaciones = $this->ds->query($query_puntuaciones, $options)->fetchAll();
        foreach ($resultado_puntuaciones as $fila_puntuaciones) {
            $puntuaciones[$fila_puntuaciones->id] = new Puntuacion($fila_puntuaciones->id, $auditores[$fila_puntuaciones->usuario], $directrices[$fila_puntuaciones->directriz], $fila_puntuaciones->puntuacion, $fila_puntuaciones->observacion, $fila_puntuaciones->mejora);
            $puntuaciones[$fila_puntuaciones->id]->setTarea($tarea);
        }
        return $puntuaciones;
    }

    public function getNumPuntuaciones(Auditoria $auditoria)
    {
      $query = 'SELECT COUNT(*) FROM {usevalia__puntuacion} WHERE auditoria = :id';
      return $this->ds->query($query, [
        'id' => $auditoria->__get('id')
      ])->fetchField();
    }

    public function createPuntuacionesTarea(Auditor $usuario, Auditoria $auditoria, array $puntuaciones_array, Tarea $tarea){
        $puntuaciones = [];
        foreach ($puntuaciones_array as $puntuacion) {
            $id_punt = $this->ds->insert('usevalia__puntuacion')
            ->fields([
                'usuario' => $usuario->__get('id'),
                'auditoria' => $auditoria->__get('id'),
                'directriz' => $puntuacion['directriz']->__get('iid'),
                'puntuacion' => $puntuacion['valor'],
                'observacion' => $puntuacion['observacion'],
                'mejora' => $puntuacion['mejora'],
                'tarea' => $tarea->__get('id'),
            ])
            ->execute();
            $puntuaciones[$id_punt] = new Puntuacion($id_punt, $usuario, $puntuacion['directriz'], $puntuacion['valor']);
            $puntuaciones[$id_punt]->setTarea($tarea);
        }
        $auditoria->addManyPuntuaciones($puntuaciones);
        return $puntuaciones;
    }

    public function updateManyTarea(Auditor $usuario, Auditoria $auditoria, array $puntuaciones, Tarea $tarea)
    {
        foreach ($puntuaciones as $valor) {
            $this->ds->update('usevalia__puntuacion')
            ->fields([
                'puntuacion' => $valor['valor'],
                'observacion' => $valor['observacion'],
                'mejora' => $valor['mejora'],
            ])
            ->condition('id', $valor['puntuacion']->__get('id'), '=')
            ->execute();
        }
    }
}
