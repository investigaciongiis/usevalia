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

use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\Prioridad;

/**
 * Description of Prioridad
 *
 * @author Celia
 */
class DAOPrioridad
{
    
    private $ds;
    
    public function __construct($ds)
    {
        $this->ds = $ds;
    }
    
    public function createMany(Catalogo $catalogo, array $valores_prioridades){
        $prioridades = [];
        foreach ($valores_prioridades as $prioridad) {
            $id_prioridad = $this->ds->insert('usevalia__prioridad')
            ->fields(([
                'nombre' => $prioridad['nombre'],
                'catalogo' => $catalogo->__get('id'),
                'peso' => $prioridad['peso'],
                'fallos' => $prioridad['fallos']
            ]))
            ->execute();
            $prioridades[$prioridad['nombre']] = new Prioridad($id_prioridad, $prioridad['nombre'], $catalogo, $prioridad['peso'], $prioridad['fallos']);
        }
        return $prioridades;
    }
    
    public function getAllByCatalogo(Catalogo $catalogo)
    {
        $prioridades = [];
        $query_prioridad = 'SELECT id, nombre, catalogo, peso, fallos FROM {usevalia__prioridad} WHERE catalogo = :id';
        $options = [
            'id' => $catalogo->__get('id')
        ];
        $resultado_prioridad = $this->ds->query($query_prioridad, $options)->fetchAll();
        foreach ($resultado_prioridad as $fila_prioridad) {
            $prioridades[$fila_prioridad->id] = new Prioridad($fila_prioridad->id, $fila_prioridad->nombre, $catalogo, $fila_prioridad->peso, $fila_prioridad->fallos);
        }
        return $prioridades;
    }
}