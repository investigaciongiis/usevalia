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

use Drupal\usevalia\Classes\Fuente;

/**
 * Description of Fuente
 *
 * @author luis
 */
class DAOFuente
{

    private $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }
    
    public function getAll()
    {
        $resultado = $this->ds->select('usevalia__fuente', 'f')
            ->fields('f', [
            'iid',
            'eid',
            'nombre',
            'descripcion',
            'url'
        ])
            ->execute()
            ->fetchAll();
        $fuentes = [];
        foreach ($resultado as $fila) {
            $fuentes[$fila->iid] = new Fuente($fila->iid, $fila->eid, $fila->nombre,
              $fila->descripcion, $fila->url);
        }
        return $fuentes;
    }
}
