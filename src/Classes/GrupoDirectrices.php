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

use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\EsquemaPuntuacion;

/**
 * Description of GrupoDirectrices
 *
 * @author luis
 */
class GrupoDirectrices
{

  private $id;
  // int
  private $nombre;
  // string
  private $catalogo;
  // Catalogo
  private $directrices;
  // array<Directrices>
  private $esquemaPuntuacion;

  // EsquemaPuntuacion
  public function __construct($id, $nombre, Catalogo $catalogo)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->catalogo = $catalogo;
    $this->catalogo->addGrupo($this);
    $this->directrices = [];
  }

  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
  }

  public function addDirectriz(Directriz $directriz)
  {
    $this->directrices[$directriz->__get('iid')] = $directriz;
  }

  public function addDirectrices(array $directrices)
  {
    foreach ($directrices as $directriz) {
      $this->addDirectriz($directriz);
    }
  }

  public function setEsquemaPuntuacion(EsquemaPuntuacion $esquema)
  {
    if (!isset($this->esquemaPuntuacion)) {
      $this->esquemaPuntuacion = $esquema;
    }
  }

  public function getNumeroDirectrices()
  {
    return count($this->directrices);
  }

  public function getDirectricesPrioritarias()
  {
    $directrices = [];
    foreach ($this->directrices as $directriz) {
      if ($directriz->getPrioridad() == 'Alto' || $directriz->getPrioridad() == 'Medio') {
        $directrices[$directriz->__get('iid')] = $directriz;
      }
    }
    return $directrices;
  }

  public function hasDirectriz(Directriz $directriz){
    foreach ($this->directrices as $dir){
      if($directriz->__get('iid') === $dir->__get('iid'))
        return true;
    }
    return false;
  }
}
