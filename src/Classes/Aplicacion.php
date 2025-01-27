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
namespace Drupal\usevalia\Classes;

/**
 * Description of Aplicacion
 *
 * @author luis
 */
class Aplicacion
{
    // int
    private $id;
    // string
    private $nombre;
    // string
    private $url;
    // string
    private $descripcion;
    // CategoriaWeb
    private $categoria;

    public function __construct($id, $nombre, $url, $descripcion, CategoriaWeb $categoria)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->url = $url;
        $this->descripcion = $descripcion;
        $this->categoria = $categoria;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
}
