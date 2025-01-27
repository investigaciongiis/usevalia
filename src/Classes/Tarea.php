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

use Drupal;

/**
 * Description of Tarea
 *
 * @author Celia
 */

class Tarea
{
    // entero
    private $id;
    // string
    private $nombre;
    // CategoriaWeb
    private $categoria;

    public function __construct($id, $nombre, CategoriaWeb $categoria)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->categoria = $categoria;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function isSuperada(Auditoria $auditoria){
      $controlador = Drupal::service('usevalia.controlador');
      $prioridades = $controlador->getPrioridadesByCatalogo($auditoria->__get('catalogo'));
      $tiposPuntuaciones = $auditoria->__get('catalogo')->__get('esquemaPuntuacion')->__get('tipos');
      foreach ($prioridades as $prioridad) {
        $conteo[$prioridad->__get('nombre')] = 0;
        $fallos[$prioridad->__get('nombre')] = $prioridad->__get('fallos');
      }
      foreach ($auditoria->__get('catalogo')->getAllDirectrices() as $directriz){
        foreach ($auditoria->getPuntuacionesByDirectrizTarea($directriz, $this) as $puntuacion) {
          if ($tiposPuntuaciones[$puntuacion->__get('puntuacion')] == 'fallo')
            $conteo[$puntuacion->__get('directriz')->getPrioridad()]++;

          // Cuando una prioridad supere su límite de fallos se termina.
          if ($conteo[$puntuacion->__get('directriz')->getPrioridad()] > $fallos[$puntuacion->__get('directriz')->getPrioridad()])
            return 'fallo';
        }
      }
      return 'aprobado';
    }
}
