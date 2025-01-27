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
namespace Drupal\usevalia\Classes;

/**
 * Description of Prioridad
 *
 * @author Celia
 */

class Prioridad
{
    // entero
    private $id;
    // string
    private $nombre;
    // Catalogo
    private $catalogo;
    // entero
    private $peso;
    // entero
    private $fallos;
    
    public function __construct($id, $nombre, Catalogo $catalogo, $peso, $fallos)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->catalogo = $catalogo;
        $this->peso = $peso;
        $this->fallos = $fallos;
    }
    
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
}
