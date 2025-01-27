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
namespace Drupal\usevalia\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\usevalia\Classes\EsquemaPuntuacion;

/**
 * Description of PuntuacionForm
 *
 * @author luis
 */
class CrearPuntuacionForm extends FormBase
{

    public function getFormId()
    {
        return 'crear_puntuacion_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $header = [
            'valor' => t('Valor'),
            'tipo' => t('Tipo')
        ];

        $form['#cache']['max-age'] = 0;
        $form['#tree'] = TRUE;
        $form['nombre'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre del esquema'),
            '#required' => TRUE
        ];
        $form['descripcion'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Descripción')
        ];
        $form['opcionalidad'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Permitir directrices no evaluables')
        ];
        $form['text_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => t('Puntuaciones'),
            '#required' => TRUE,
            '#prefix' => '<div id="puntuaciones-fieldset">',
            '#suffix' => '</div>'
        ];
        $form['text_fieldset']['table'] = [
            '#type' => 'table',
            '#header' => $header
        ];
        $num_fields = $form_state->get('num_fields');
        if (empty($num_fields)) {
            $num_fields = 2;
            $form_state->set('num_fields', $num_fields);
        }
        for ($i = 0; $i < $num_fields; $i++) {
            $form['text_fieldset']['table'][$i]['valor'] = [
                '#type' => 'textfield'
            ];
            $form['text_fieldset']['table'][$i]['tipo'] = [
                '#type' => 'select',
                '#options' => array(
                    'aprobado' => t('Aprobado'),
                    'fallo' => t('Fallo')
                )
            ];
        }
        $form['text_fieldset']['add_item'] = [
            '#type' => 'submit',
            '#value' => t('Añadir puntuación'),
            '#limit_validation_errors' => [],
            '#submit' => [
                '::addTextfield'
            ]
        ];
        $form['text_fieldset']['add_item1'] = [
            '#type' => 'submit',
            '#value' => t('Eliminar puntuación'),
            '#limit_validation_errors' => [],
            '#submit' => [
                '::subtractTextfield'
            ]
        ];
        $form['submit_button'] = [
            '#type' => 'submit',
            '#value' => t('Crear')
        ];
        $form_state->setCached(FALSE);
        return $form;
    }

    public function addTextfield(array &$form, FormStateInterface $form_state)
    {
        $num_fields = $form_state->get('num_fields') + 1;
        $form_state->set('num_fields', $num_fields);
        $form_state->setRebuild(TRUE);
    }

    public function subtractTextfield(array &$form, FormStateInterface $form_state)
    {
        $messenger = Drupal::messenger();
        if ($form_state->get('num_fields') == 2) {
            $messenger->addMessage(t('Debes introducir al menos 2 puntuaciones.'), 'error');
        } else {
            $num_fields = $form_state->get('num_fields') - 1;
            $form_state->set('num_fields', $num_fields);
        }
        $form_state->setRebuild(TRUE);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $num_fields = $form_state->get('num_fields');
        $valores = $form_state->getValues()['text_fieldset']['table'];
        $messenger = Drupal::messenger();
        $campos_real = [];
        $tipos_real = [];
        $campos_lwr = [];
        for ($i = 0; $i < $num_fields; $i ++) {
            $valor = $valores[$i]['valor'];
            $tipo = $valores[$i]['tipo'];
            if (! empty($valor) && ! in_array(strtolower(trim($valor)), $campos_lwr)) {
                $campos_real[] = trim($valor);
                $tipos_real[$valor] = trim($tipo);
                $campos_lwr[] = strtolower(trim($valor));
            }
        }
        if (count($campos_real) < 2) {
            // TODO ver por qué con 0 y 1 no deja.
           $messenger->addMessage(t('Debes añadir al menos 2 puntuaciones, hay '). count($campos_real) .'.', 'error');
        } else {
            if ($form_state->getValue('opcionalidad') === 1) {
                $campos_real[] = 'No aplicable';
                $tipos_real['No aplicable'] = 'N/A';
            }
            $controlador = Drupal::service('usevalia.controlador');
            $puntuacion = $controlador->crearEsquemaPuntuacion($form_state->getValue('nombre'), $form_state->getValue('descripcion'), $campos_real, $tipos_real);
            $messenger->addMessage(t('La escala "'). $puntuacion->__get('nombre') . t('" se ha creado correctamente.'));
        }
    }
}
