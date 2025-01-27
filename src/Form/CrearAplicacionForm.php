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

/**
 * Description of CrearAplicacionForm
 *
 * @author luis
 */
class CrearAplicacionForm extends FormBase
{
    private $categorias;

    public function getFormId()
    {
        return 'crear_aplicacion_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $controlador = Drupal::service('usevalia.controlador');
        $form['#cache']['max-age'] = 0;
        $form['nombre'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Nombre de la aplicación'),
            '#required' => TRUE
        ];
        $form['url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('URL de la aplicación'),
            '#requiered' => TRUE
        ];
        $this->categorias = $controlador->getAllCategorias();
        $output_categorias = [];
        foreach ($this->categorias as $categoria) {
            $output_categorias[$categoria->__get('id')] = t($categoria->__get('nombre'));
        }
        $form['categoria'] = [
            '#type' => 'select',
            '#title' => $this->t('Selecciona la categoría web'),
            '#options' => $output_categorias,
            '#required' => TRUE
        ];
        $form['descripcion'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Descripción')
        ];
        $form['submit_button'] = [
            '#type' => 'submit',
            '#value' => t('Crear aplicación')
        ];
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $controlador = Drupal::service('usevalia.controlador');
        $categoria = $this->categorias[$form_state->getValue('categoria')];
        $aplicacion = $controlador->crearAplicacion($form_state->getValue('nombre'), $form_state->getValue('url'), $form_state->getValue('descripcion'), $categoria);
        $messenger = Drupal::messenger();
        $messenger->addMessage(t('La aplicación "') . $aplicacion->__get('nombre') . t('" se ha creado correctamente.'));
    }
}
