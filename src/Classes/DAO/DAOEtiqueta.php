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

/**
 * Description of DAOEtiqueta
 *
 * @author luis
 */
class DAOEtiqueta
{

    private $ds;

    public function __construct($ds)
    {
        $this->ds = $ds;
    }

    public function create($etiqueta)
    {
        $id_tag = $this->ds->insert('usevalia__etiqueta')
            ->fields([
            'valor' => $etiqueta
        ])
            ->execute();
        $tag = [
            'id' => $id_tag,
            'valor' => $etiqueta
        ];
        return $tag;
    }

    public function createMany(array $etiquetas)
    {
        $etiquetas_final = [];
        foreach ($etiquetas as $etiqueta) {
            $tag = $this->create($etiqueta);
            $etiquetas_final[$tag['id']] = $tag;
        }
        return $etiquetas_final;
    }

    public function getByValor($valor)
    {
        $query = 'SELECT id FROM {usevalia__etiqueta} WHERE valor = :valor';
        $resultado = $this->ds->query($query, [
            ':valor' => $valor
        ])->fetchAll();
        if (empty($resultado)) {
            return;
        } else {
            return [
                'id' => $resultado[0]->id,
                'valor' => $valor
            ];
        }
    }

    public function getByValores(array $valores)
    {
        $tags = [];
        foreach ($valores as $valor) {
            $tag = $this->getByValor($valor);
            if (isset($tag)) {
                $tags[$tag['id']] = $tag;
            }
        }
        return $tags;
    }

    public function getAllByValorAndCreateIfNotExists(array $tags)
    {
        $etiquetasBBDD = $this->getByValores($tags);
        $existentes = [];
        foreach ($etiquetasBBDD as $etiqueta) {
            array_push($existentes, $etiqueta['valor']);
        }
        $pendientes = array_diff($tags, $existentes);
        return array_merge($etiquetasBBDD, $this->createMany($pendientes));
    }

    public function getAll()
    {
        $resultado = $this->ds->select('usevalia__etiqueta', 'e')
            ->fields('e', [
            'id',
            'valor'
        ])
            ->execute()
            ->fetchAll();
        $etiquetas = [];
        foreach ($resultado as $fila) {
            $etiquetas[$fila->id] = [
                'id' => $fila->id,
                'valor' => $fila->valor
            ];
        }
        return $etiquetas;
    }
}
