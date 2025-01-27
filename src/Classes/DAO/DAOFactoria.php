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


use Drupal;

/**
 * Description of DAOFactoria
 *
 * @author luis
 */
class DAOFactoria
{

    private $ds;

    public function __construct()
    {
        $this->ds = Drupal::database();
    }

    public function getAplicacionDAO()
    {
        return new DAOAplicacion($this->ds);
    }

    public function getAuditorDAO()
    {
        return new DAOAuditor($this->ds);
    }

    public function getAuditoriaDAO()
    {
        return new DAOAuditoria($this->ds);
    }

    public function getCatalogoDAO()
    {
        return new DAOCatalogo($this->ds);
    }

    public function getDirectrizDAO()
    {
        return new DAODirectriz($this->ds);
    }

    public function getEsquemaPuntuacionDAO()
    {
        return new DAOEsquemaPuntuacion($this->ds);
    }

    public function getEtiquetaDAO()
    {
        return new DAOEtiqueta($this->ds);
    }

    public function getFuenteDAO()
    {
        return new DAOFuente($this->ds);
    }

    public function getGrupoAuditoresDAO()
    {
        return new DAOGrupoAuditores($this->ds);
    }

    public function getGrupoDirectricesDAO()
    {
        return new DAOGrupoDirectrices($this->ds);
    }

    public function getPuntuacionDAO()
    {
        return new DAOPuntuacion($this->ds);
    }

    public function getPrioridadDAO()
    {
        return new DAOPrioridad($this->ds);
    }

    public function getCategoriaWebDAO()
    {
        return new DAOCategoriaWeb($this->ds);
    }

    public function getTareaDAO()
    {
        return new DAOTarea($this->ds);
    }
}
