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

use Drupal\usevalia\Classes\Auditor;

/**
 * Description of DAOAuditor
 *
 * @author luis
 */
class DAOAuditor
{

    private $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }

    public function getAll()
    {
        $query = 'SELECT uid, name, preferred_langcode, mail FROM {users_field_data} WHERE uid <> 0';
        $resultados = $this->ds->query($query)->fetchAll();
        $auditores = [];
        foreach ($resultados as $fila) {
            $auditores[$fila->uid] = new Auditor($fila->uid, $fila->name, $fila->mail, $fila->preferred_langcode);
        }
        return $auditores;
    }

    public function getAllExcept($id)
    {
        $query = 'SELECT uid, name, preferred_langcode, mail FROM {users_field_data} WHERE uid <> 0 AND uid <> :uid';
        $options = [
            ':uid' => $id
        ];
        $resultados = $this->ds->query($query, $options)->fetchAll();
        $auditores = [];
        foreach ($resultados as $fila) {
            $auditores[$fila->uid] = new Auditor($fila->uid, $fila->name, $fila->mail, $fila->preferred_langcode);
        }
        return $auditores;
    }

    public function getById($id)
    {
        $query = 'SELECT uid, name, preferred_langcode, mail FROM {users_field_data} WHERE uid = :id';
        $resultado = $this->ds->query($query, [
            ':id' => $id
        ])->fetchAll();
        return new Auditor($resultado[0]->uid, $resultado[0]->name, $resultado[0]->mail, $resultado[0]->preferred_langcode);
    }

    public function getSome(array $ids)
    {
        $auditores = [];
        foreach ($ids as $id) {
            $auditores[$id] = $this->getById($id);
        }
        return $auditores;
    }
}
