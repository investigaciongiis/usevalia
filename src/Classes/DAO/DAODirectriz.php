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

use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\usevalia\Classes\GrupoDirectrices;
use Drupal\usevalia\Classes\Directriz;
use Drupal\usevalia\Classes\Prioridad;

/**
 * Description of Directriz
 *
 * @author luis
 */
class DAODirectriz
{

    private $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }

    public function createMany(GrupoDirectrices $grupo, array $valores_directrices)
    {
        $directrices = [];
        foreach ($valores_directrices as $fila_directrices) {
            $id_dir = $this->ds->insert('usevalia__directriz')
                ->fields([
                'eid' => $fila_directrices['id'],
                'nombre' => $fila_directrices['nombre'],
                'descripcion' => $fila_directrices['descripcion'],
                'peso' => $fila_directrices['peso']->__get('id'),
                'esquema' => $fila_directrices['esquema']->__get('id'),
                'grupo' => $grupo->__get('id')
            ])
                ->execute();
            $directrices[$id_dir] = new Directriz($id_dir, $fila_directrices['id'], $fila_directrices['nombre'], $fila_directrices['descripcion'], $fila_directrices['peso'], $grupo, $fila_directrices['esquema']);
        }
        return $directrices;
    }

    public function getAllByGrupo(GrupoDirectrices $grupo)
    {
        $query = 'SELECT iid, eid, nombre, descripcion, peso FROM {usevalia__directriz} WHERE grupo = :id';
        $resultado = $this->ds->query($query, [
            ':id' => $grupo->__get('id')
        ])
            ->fetchAll();
        $directrices = [];
        foreach ($resultado as $fila) {
            $query_prioridad = 'SELECT id, nombre, peso, fallos FROM {usevalia__prioridad} WHERE id = :id';
            $resultado_prioridad = $this->ds->query($query_prioridad, [
                ':id' => $fila->peso
            ])
            ->fetchAll();
            $prioridad = new Prioridad($resultado_prioridad[0]->id, $resultado_prioridad[0]->nombre, $grupo->__get('catalogo'), $resultado_prioridad[0]->peso, $resultado_prioridad[0]->fallos);
            $directrices[$fila->iid] = new Directriz($fila->iid, $fila->eid, $fila->nombre, $fila->descripcion, $prioridad, $grupo);
        }
        return $directrices;
    }

    public function getNumEsquemasPuntuacion(EsquemaPuntuacion $esquema)
    {
        $query = 'SELECT COUNT(esquema) FROM {usevalia__directriz} WHERE esquema = :id';
        return $this->ds->query($query, [
            'id' => $esquema->__get('id')
        ])
            ->fetchField();
    }

    /*
     * public function getByIid($iid) {
     * $query = 'SELECT iid, eid, nombre, descripcion FROM {usevalia__directriz} WHERE iid = :iid';
     * $resultado = $this->ds->query($query, [':iid' => $iid,])->fetchAll();
     * $directrices = [];
     * foreach ($resultado as $fila) {
     * $directrices[$fila->iid] = new Directriz($fila->iid, $fila->eid, $fila->nombre,
     * $fila->descripcion, 0, $grupo);
     * }
     * return $directrices;
     * }
     *
     */
}
