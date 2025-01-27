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

use Drupal\usevalia\Classes\GrupoDirectrices;
use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\usevalia\Classes\Fuente;

/**
 * Description of Directriz
 *
 * @author luis
 */
class Directriz
{

    private $iid;
 // int
    private $eid;
 // string
    private $nombre;
 // string
    private $descripcion;
 // string
    private $peso;
 // Prioridad
    private $padre;
 // Directriz
    private $hijos;
 // List<Directriz>
    private $fuentes;
 // List<Fuente>
    private $grupo;
 // GrupoDirectrices
    private $esquemaPuntuacion;
 // EsquemaPuntuacion  
    public function __construct($iid, $eid, $nombre, $descripcion, Prioridad $peso, GrupoDirectrices $grupo)
    {
        $this->iid = $iid;
        $this->eid = $eid;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->peso = $peso;
        $this->grupo = $grupo;
        $this->grupo->addDirectriz($this);
        $this->fuentes = [];
        $this->hijos = [];
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function setEsquemaPuntuacion(EsquemaPuntuacion $esquema)
    {
        if (! isset($this->esquemaPuntuacion)) {
            $this->esquemaPuntuacion = $esquema;
        }
    }

    public function setPadre(Directriz $padre)
    {
        if (! isset($this->padre)) {
            $this->padre = $padre;
        }
    }

    public function addHijo(Directriz $hijo)
    {
        $this->hijos[$hijo->__get('iid')] = $hijo;
    }

    public function addFuente(Fuente $fuente)
    {
        $this->fuentes[$fuente->__get('iid')] = $fuente;
    }
    
    public function getPrioridad(){
        return $this->peso->__get('nombre');
    }
}
