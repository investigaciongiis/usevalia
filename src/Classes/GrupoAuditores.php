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
 * Description of GrupoAuditores
 *
 * @author luis
 */
class GrupoAuditores
{

    private $id;

    private $nombre;

    private $descripcion;

    private $auditores;

    private $etiquetas;

    public function __construct($id, $nombre, $descripcion, array $auditores)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->auditores = $auditores;
        $this->etiquetas = [];
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function addEtiqueta($etiqueta)
    {
        // Etiqueta es string!
        array_push($this->etiquetas, $etiqueta);
    }

    public function addEtiquetas(array $etiquetas)
    {
        foreach ($etiquetas as $etiqueta) {
            $this->addEtiqueta($etiqueta);
        }
    }
}
