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
use Drupal\Core\Url;
use Drupal\usevalia\Classes\Auditoria;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Html;

/**
 * Description of PuntuacionDosForm
 *
 * @author luis
 */
class PuntuacionDosForm extends PuntuacionFormBase
{

  private $auditoria;

  /**
   *
   * {@inheritdoc} .
   */
  public function getFormId()
  {
    return 'puntuacion_form_dos';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form = parent::buildForm($form, $form_state);
    $form['#cache']['max-age'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    $this->auditoria = $controlador->getAuditoriaById($this->store->get('auditoria'));
    $catalogo = $this->auditoria->__get('catalogo');
    $header = [
      'nombre' => t('Nombre'),
      'descripcion' => t('Descripción'),
      'puntuacion' => t('Puntuación'),
      'observacion' => t('Observaciones'),
      'mejora' => t('Sugerencias de mejora'),
      'captura' => t('Captura'),
      'adjuntar' => t('Adjuntar captura')
    ];
    $puntuaciones = $this->auditoria->getPuntuacionesByUsuario($controlador->getAuditorById(Drupal::currentUser()->id()));


    $form['manual'] = [
      '#type' => 'inline_template',
      '#template' => '<div id="catalog-instructions">{{nombre}}<br/><br/>{{aplicacion}}<br/><br/>'.t('URL:').' <a href="'.$this->auditoria->__get('aplicacion')->url . '" target="_blank">'. $this->auditoria->__get('aplicacion')->url . '</a>'.'<br/><br/>{{catalogo}}<p><br/><br/></div>',
      '#context' => [
        'nombre' => t('Auditoria:').' '.$this->auditoria->__get('nombre'),
        'aplicacion' => t('Aplicacion:').' '.$this->auditoria->__get('aplicacion')->nombre,
        'catalogo' => t('Catálogo:').' '.$this->auditoria->__get('catalogo')->nombre
      ]
    ];
    $form['#tree'] = TRUE;
    $form['tables'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'tables'
      ]
    ];


    foreach ($catalogo->__get('gruposDirectrices') as $grupo) {
      $form['tables'][$grupo->__get('id')] = [
        '#type' => 'table',
        '#caption' => $grupo->__get('nombre'),
        '#header' => $header,
        '#empty' => t('Este grupo no tiene directrices...')
      ];
      $directrices = $grupo->__get('directrices');
      if ($this->auditoria->__get('evaluacion') == 'basica') {
        $directrices = $grupo->getDirectricesPrioritarias();
      }
      foreach ($directrices as $directriz) {
        $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['nombre'] = [
          '#type' => 'inline_template',
          '#template' => '{{msg}}',
          '#context' => [
            'msg' => $directriz->__get('nombre')
          ]
        ];
        $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['descripcion'] = [
          '#type' => 'inline_template',
          '#template' => '{{msg}}',
          '#context' => [
            'msg' => $directriz->__get('descripcion')
          ]
        ];
        $msg = '-';
        $observacion = '';
        $mejora = '';
        if (isset($puntuaciones[$directriz->__get('iid')])) {
          $msg = $puntuaciones[$directriz->__get('iid')]->__get('puntuacion');
          $observacion = $puntuaciones[$directriz->__get('iid')]->__get('observacion');
          $mejora = $puntuaciones[$directriz->__get('iid')]->__get('mejora');
		      $captura = '';
        }
        $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['puntuacion'] = [
          '#type' => 'select',
          '#title' => t('Puntuacion'),
          '#title_display' => 'invisible',
          '#options' => $catalogo->__get('esquemaPuntuacion')->__get('valores'),
          '#empty_value' => '',
          '#default_value' => $msg,
          '#attributes' => array('style' => 'min-width: 100px;')
        ];
        $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['observacion'] = [
          '#type' => 'textarea',
          '#default_value' => $observacion
        ];
        $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['mejora'] = [
          '#type' => 'textarea',
          '#default_value' => $mejora
        ];
		    $uriImage = 'https://giis.inf.um.es:446/drupal3/sites/default/files/screenshots/capture_'.$this->auditoria->__get('id') . '_' .$grupo->__get('id').'_'.$directriz->__get('iid').'.jpg';
        //$uri = DRUPAL_ROOT . '/sites/default/files/capture_'.$grupo->__get('id').'_'.$directriz->__get('iid').'.jpg';
        $headers=get_headers($uriImage);
        if (stripos($headers[0],"200 OK")?true:false) {
          $date = date_create();
        //if (file_exists($uri)) {
          $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['captura'] = [
            '#type' => 'inline_template',
            '#template' => '<img class="imageScreenshoot" style="cursor: zoom-in;" width="100%" src="'.$uriImage.'?t='.date_timestamp_get($date).'"/>'
          ];
        } else {
          $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['captura'] = [
            '#type' => 'inline_template',
            '#template' => ''
          ];
        }

		    $form['tables'][$grupo->__get('id')][$directriz->__get('iid')]['adjuntar'] = [
          '#description' => $this->t('<br> Seleccionar jpg a adjuntar'),
          '#type' => 'managed_file',
          '#multiple' => FALSE,
          '#name' => 'formfile',
		      //'#upload_location' => 'public://',
          '#upload_location' => 'temporary://',
		      '#upload_validators' => [
			      'file_validate_extensions' => [
              'jpg'
			      ]
		      ],
		      //'#default_value' => $captura
        ];

      }
    }
    $form['actions']['previous'] = [
      '#type' => 'submit',
      '#value' => $this->t('Anterior'),
      '#weight' => 0,
      '#submit' => [
        '::goBack'
      ]
    ];

    return $form;
  }


  public function goBack(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRedirect('usevalia.puntuacion_uno');
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Save the data
    $controlador = Drupal::service('usevalia.controlador');
    $puntuacionesAnteriores = $this->auditoria->getPuntuacionesByUsuario($controlador->getAuditorById(Drupal::currentUser()->id()));
    $valores = $form_state->getValues()['tables'];
    $puntuacionesNuevas = []; // [Directriz, puntuacion]
    $puntuacionesCambiar = []; // [Directriz, puntuacion]
    $catalogo = $this->auditoria->__get('catalogo');
    foreach ($catalogo->__get('gruposDirectrices') as $grupo) {
      $directrices = $grupo->__get('directrices');

      if ($this->auditoria->__get('evaluacion') == 'basica') {
        $directrices = $grupo->getDirectricesPrioritarias();
      }
      foreach ($directrices as $directriz) {
        $resultado = $valores[$grupo->__get('id')][$directriz->__get('iid')]['puntuacion'];
        $observacion = $valores[$grupo->__get('id')][$directriz->__get('iid')]['observacion'];
        $mejora = $valores[$grupo->__get('id')][$directriz->__get('iid')]['mejora'];
        $adjuntar = $valores[$grupo->__get('id')][$directriz->__get('iid')]['adjuntar'];

        //if($grupo->__get('id') == 62 && $directriz->__get('iid') == 136){
        if(!is_null($adjuntar)){
          $file = File::load($adjuntar[0]);
          if(!is_null($file)){
            $data = file_get_contents($file->getFileUri());

            //drupal_set_message('-'.$file->getFileUri().'-', 'error');

            $file_name = 'capture_'. $this->auditoria->__get('id') . '_'. $grupo->__get('id') . '_' . $directriz->__get('iid') . '.jpg';
            $file_params = array('filename' => $file_name, 'uri' => 'public://screenshots/' . $file_name, 'filemime' => 'image/jpg', 'status' => FILE_STATUS_PERMANENT);
            $fileOutput = File::create($file_params);
            file_put_contents($fileOutput->getFileUri(), $data);
            $file->save();
          }

          //drupal_set_message('-'.$fileOutput->getFileUri().'-', 'error');
        }

        if (!empty($resultado)) {
          if (isset($puntuacionesAnteriores[$directriz->__get('iid')])) {
            $puntuacionesCambiar[] = [
              'puntuacion' => $puntuacionesAnteriores[$directriz->__get('iid')],
              'valor' => $resultado,
              'observacion' => $observacion,
              'mejora' => $mejora
            ];
          } else {
            $puntuacionesNuevas[] = [
              'directriz' => $directriz,
              'valor' => $resultado,
              'observacion' => $observacion,
              'mejora' => $mejora
            ];
          }
        }
      }
    }
    $controlador->crearPuntuacionCatalogo(Drupal::currentUser()->id(), $this->auditoria, $puntuacionesNuevas);
    $controlador->cambiarPuntuacionCatalogo(Drupal::currentUser()->id(), $this->auditoria, $puntuacionesCambiar);
    parent::saveData();

    $isCompleted = $this->auditoria->isCompleted();
    /*$messenger = Drupal::messenger();
    $messenger->addMessage($this->t('Ha sido completada: ' + $isCompleted ? 'Sí' : 'No'));
    $messenger->addMessage($this->auditoria->__get('administrador')->__get('email'));
    $messenger->addMessage($this->auditoria->__get('nombre'));*/

    if($isCompleted){
      //enviamos email informando ...
      $adminAuditoria = $this->auditoria->__get('administrador')->__get('email');
      $nombreAuditoria = $this->auditoria->__get('nombre');
      simple_mail_send('usevalia@um.es', $adminAuditoria, $this->t('Aviso de Usevalia'), 
        $this->t('La auditoría "@auditoria" ha sido completada por todos sus participantes.', array('@auditoria' => $nombreAuditoria)));
    }
    
    $form_state->setRedirect('usevalia.puntuacion_uno');
  }
}
