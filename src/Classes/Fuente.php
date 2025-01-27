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
 * Description of Fuente
 *
 * @author luis
 */
class Fuente
{

    private $iid;
 // int
    private $eid;
 // int
    private $nombre;
 // string
    private $descripcion;
 // string
    private $url;
 // string
    public function __construct($iid, $eid, $nombre, $descripcion, $url)
    {
        $this->iid = $iid;
        $this->eid = $eid;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->url = $url;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
}
