<?php

/*
 * Copyright (C) 2020 celia
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
use Drupal\stream_wrapper_example\StreamWrapper\SessionWrapper;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Description of GenerarEstadisticasForm
 *
 * @author celia
 */
class GenerarEstadisticasForm extends FormBase
{
    private $auditorias;
    private $usuario;

    public function getFormId()
    {
        return 'generar-informe-form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $controlador = Drupal::service('usevalia.controlador');
        $this->usuario = Drupal::currentUser()->id();

      $header = [
        'nombre' => t('Nombre de la auditoría'),
        'aplicacion' => t('Nombre de la aplicación'),
        'catalogo' => t('Catálogo utilizado'),
        'auditores' => t('Participantes'),
            //'estado' => t('Estado de evaluación')
      ];
      $output = [];
      $this->auditorias = $controlador->getAuditoriasFullCerradasFrom($this->currentUser()->id());
      foreach ($this->auditorias as $auditoria) {
        $participantes = [];
        foreach ($auditoria->__get('participantes') as $participante) {
          $participantes[] = $participante->__get('nombre');
        }
        $output[$auditoria->__get('id')] = [
          'nombre' => $auditoria->__get('nombre'),
          'aplicacion' => $auditoria->__get('aplicacion')->__get('nombre'),
          'catalogo' => $auditoria->__get('catalogo')->__get('nombre'),
          'auditores' => implode(', ', $participantes),
				//'estado' => $auditoria->getEstadoEvaluacion(),
				//'#attributes' => array('class' => array($auditoria->getEstadoColorEvaluacion())),
				//'#attributes' => array('style' => array($auditoria->getEstadoColorEvaluacion())),
        ];
      }
        $form['#cache']['max-age'] = 0;
        $form['table'] = [
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $output,
            '#empty' => t('No tienes auditorías cerradas...'),
            '#multiple' => FALSE
        ];
        $form['submit_button'] = [
            '#type' => 'submit',
            '#value' => t('Ver')
        ];
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        if(!empty($form_state->getValue('table'))){
            $tempstore = Drupal::service('user.private_tempstore')->get('usevalia_temp');
            $tempstore->set('controlador' . $this->usuario, $form_state->getValue('table'));
            $form_state->setRedirect('usevalia.estadisticas');
        }else{
            $messenger = Drupal::messenger();
            $messenger->addMessage(t('Debes seleccionar un auditoría.'), 'error');
        }
    }
}
