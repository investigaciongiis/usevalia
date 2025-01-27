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

use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\Directriz;

/**
 * Description of Puntuacion
 *
 * @author luis
 */
class Puntuacion
{
    // entero
    private $id;
    // Auditor
    private $usuario;
    // Directriz
    private $directriz;
    // string
    private $puntuacion;
    // string
    private $observacion;
    // string
    private $mejora;
    // Tarea
    private $tarea;
    
    public function __construct($id, Auditor $usuario, Directriz $directriz, $puntuacion, $observacion = '', $mejora = '')
    {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->directriz = $directriz;
        $this->puntuacion = $puntuacion;
        $this->observacion = $observacion;
        $this->mejora = $mejora;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
    
    public function setTarea(Tarea $tarea){
        $this->tarea = $tarea;
    }
}
