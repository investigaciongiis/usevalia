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
use Drupal\usevalia\Classes\GrupoAuditores;
use Drupal\usevalia\Classes\GrupoDirectrices;

/**
 * Description of Catalogo
 *
 * @author luis
 */
class Catalogo
{

  private $id;
  // int
  private $nombre;
  // string
  private $esquemaPuntuacion;
  // EsquemaPuntuacion
  private $autor;
  // Auditor
  private $grupoAuditores;
  // GrupoAuditores
  private $permisoLectura;
  // PUBLICO, GRUPO, PRIVADO
  private $permisoEscritura;
  // PUBLICO, GRUPO, PRIVADO
  private $gruposDirectrices;
  // array<GrupoDirectrices>

  public function __construct($id, $nombre, EsquemaPuntuacion $esquemaPuntuacion, Auditor $autor, GrupoAuditores $grupoAuditores, $permisoLectura, $permisoEscritura)
  {
    $this->id = $id;
    $this->nombre = $nombre;
    $this->esquemaPuntuacion = $esquemaPuntuacion;
    $this->autor = $autor;
    $this->grupoAuditores = $grupoAuditores;
    $this->permisoLectura = $permisoLectura;
    $this->permisoEscritura = $permisoEscritura;
    $this->gruposDirectrices = [];
  }

  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
  }

  public function addGrupo(GrupoDirectrices $grupo)
  {
    $this->gruposDirectrices[$grupo->__get('id')] = $grupo;
  }

  public function addGrupos(array $grupos)
  {
    foreach ($grupos as $grupo) {
      $this->addGrupo($grupo);
    }
  }

  public function getNumeroDirectrices()
  {
    $total = 0;
    foreach ($this->gruposDirectrices as $grupo) {
      $total += $grupo->getNumeroDirectrices();
    }
    return $total;
  }

  public function getAllDirectrices()
  {
    $directrices = [];
    foreach ($this->gruposDirectrices as $grupo) {
      $directrices += $grupo->__get('directrices');
    }
    return $directrices;
  }

  public function getDirectricesPrioritarias()
  {
    $directrices = [];
    foreach ($this->gruposDirectrices as $grupo) {
      $directrices += $grupo->getDirectricesPrioritarias();
    }
    return $directrices;
  }
}
