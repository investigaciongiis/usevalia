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
namespace Drupal\usevalia\Form\Puntuacion;

use Drupal;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description of PuntuacionUnoForm
 *
 * @author luis
 */
class PuntuacionUnoForm extends PuntuacionFormBase
{

    private $auditorias;

    /**
     *
     * {@inheritdoc} .
     */
    public function getFormId()
    {
        return 'puntuacion_form_uno';
    }

    /**
     *
     * {@inheritdoc} .
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $controlador = Drupal::service('usevalia.controlador');
        $form = parent::buildForm($form, $form_state);
        $form['#cache']['max-age'] = 0;
        $header = [
            'nombre' => t('Nombre de la auditoría'),
            'aplicacion' => t('Nombre de la aplicación'),
            'catalogo' => t('Catálogo utilizado'),
            'puntuacion' => t('Tipo de puntuación'),
            'evaluacion' => t('Tipo de evaluación'),
            //'estado' => t('Estado de evaluación')
        ];
        $output = [];
        $this->auditorias = $controlador->getAuditoriasAbiertasFrom($this->currentUser()
            ->id());
        foreach ($this->auditorias as $auditoria) {
            $output[$auditoria->__get('id')] = [
                'nombre' => $auditoria->__get('nombre'),
                'aplicacion' => $auditoria->__get('aplicacion')->__get('nombre'),
                'catalogo' => $auditoria->__get('catalogo')->__get('nombre'),
                'puntuacion' => $auditoria->__get('catalogo')
                    ->__get('esquemaPuntuacion')
                    ->__get('nombre'),
                'evaluacion' => t($auditoria->getEvaluacionNombre()),
				//'estado' => $auditoria->getEstadoEvaluacion(),
				//'#attributes' => array('class' => array($auditoria->getEstadoColorEvaluacion())),
				//'#attributes' => array('style' => array($auditoria->getEstadoColorEvaluacion())),
            ];
        }
        $form['table'] = [
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $output,
            '#empty' => t('No tienes auditorías pendientes...'),
            '#multiple' => FALSE
        ];
        $form['actions']['submit']['#value'] = $this->t('Siguiente');
        return $form;
    }

    /**
     *
     * {@inheritdoc} .
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $messenger = Drupal::messenger();
        if (empty($form_state->getValue('table'))) {
          $messenger->addMessage(t('Debes seleccionar un auditoría.'), 'error');
        }else{
            $id = $this->auditorias[$form_state->getValue('table')]->__get('id');
            $this->store->set('auditoria', $id);
            if($this->auditorias[$form_state->getValue('table')]->__get('evaluacion') != "tareas"){
                $form_state->setRedirect('usevalia.puntuacion_dos');
            }else if($this->auditorias[$form_state->getValue('table')]->__get('evaluacion') == "tareas"){
                $controlador = Drupal::service('usevalia.controlador');
                $this->store->set('tarea', $controlador->getPrimeraTarea($this->auditorias[$form_state->getValue('table')]->getCategoriaApp()));
                $this->store->set('anteriorTarea', null);
                $form_state->setRedirect('usevalia.puntuacion_tareas');
            }
        }
    }
}
