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

use DateTime;
use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\usevalia\Classes\Auditor;
use Drupal\usevalia\Classes\Auditoria;
use Drupal\usevalia\Classes\Aplicacion;
use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\EsquemaPuntuacion;

/**
 * Description of AuditoriaForm
 *
 * @author luis
 */
class CrearAuditoriaForm extends FormBase {

  private $auditores;
  private $gruposAuditores;
  private $catalogos;
  private $aplicaciones;

  public function getFormId() {
    return 'crear_auditoria_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $controlador = Drupal::service('usevalia.controlador');
    $header_usuarios = [
      'nombre' => t('Nombre de usuario'),
      'email' => t('Correo electronico'),
      'idioma' => t('Idioma')
    ];
    $header_catalogos = [
      'nombre' => t('Nombre del catálogo'),
      'puntuacion' => t('Esquema de puntuación'),
      'admin' => t('Creador'),
      'grupo' => t('Grupo asignado'),
      'lectura' => t('Permiso de lectura'),
      'escritura' => t('Permiso de escritura')
    ];
    $header_grupos = [
      'nombre' => t('Nombre del grupo'),
      'descripcion' => t('Descripción'),
      'etiquetas' => t('Etiquetas')
    ];
    $output_usuarios = [];
    $output_catalogos = [];
    $output_grupos = [];
    $this->auditores = $controlador->getAllAuditoresExcepto(Drupal::currentUser()->id());
    foreach ($this->auditores as $auditor) {
      $output_usuarios[$auditor->__get('id')] = [
        'nombre' => $auditor->__get('nombre'),
        'email' => $auditor->__get('email'),
        'idioma' => $auditor->__get('idioma')
      ];
    }
    $this->catalogos = $controlador->getCatalogosByUsuarioConPermisos(Drupal::currentUser()->id());
    foreach ($this->catalogos as $catalogo) {
      $output_catalogos[$catalogo->__get('id')] = [
        'nombre' => $catalogo->__get('nombre'),
        'puntuacion' => $catalogo->__get('esquemaPuntuacion')->__get('nombre'),
        'admin' => $catalogo->__get('autor')->__get('nombre'),
        'grupo' => $catalogo->__get('grupoAuditores')->__get('nombre'),
        'lectura' => t($catalogo->__get('permisoLectura')),
        'escritura' => t($catalogo->__get('permisoEscritura'))
      ];
    }
    $this->gruposAuditores = $controlador->getAllGruposAuditores();
    foreach ($this->gruposAuditores as $grupo) {
      $output_grupos[$grupo->__get('id')] = [
        'nombre' => $grupo->__get('nombre'),
        'descripcion' => $grupo->__get('descripcion'),
        'etiquetas' => implode(', ', $grupo->__get('etiquetas'))
      ];
    }
    // Formulario
    $form['#cache']['max-age'] = 0;
    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de la auditoría'),
      '#required' => TRUE
    ];
    $form['descripcion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Objetivo'),
      '#required' => TRUE
    ];
    $form['fecha_estimada'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de finalización estimada'),
      '#required' => TRUE
    ];

    $this->aplicaciones = $controlador->getAllAplicaciones();
    $output_aplicaciones = [];
    foreach ($this->aplicaciones as $aplicacion) {
      $output_aplicaciones[$aplicacion->__get('id')] = implode(' - ', [
        $aplicacion->__get('nombre'),
        $aplicacion->__get('url')
      ]);
    }

    $form['id_app'] = [
      '#type' => 'select',
      '#title' => $this->t('Selecciona la aplicación'),
      '#options' => $output_aplicaciones,
      '#required' => TRUE
    ];

    $form['evaluacion'] = [
        '#type' => 'select',
        '#title' => $this->t('Selecciona el tipo de evaluación'),
        '#options' => array(
            'basica' => t('Básica'),
            'estandar' => t('Estándar'),
            'tareas' => t('Tareas')
        ),
        '#required' => TRUE
    ];

    $form['showcase'] = [
      '#type' => 'label',
      '#title' => t('Usuarios'),
      '#required' => TRUE
    ];
    // Participantes - usuario actual
    $form['usuarios'] = [
      '#type' => 'tableselect',
      '#header' => $header_usuarios,
      '#options' => $output_usuarios,
      '#empty' => t('No hay usuarios disponibles...')
    ];
    $form['showcase2'] = [
      '#type' => 'label',
      '#title' => t('Grupos de usuarios'),
      '#required' => TRUE
    ];
    $form['grupos'] = [
      '#type' => 'tableselect',
      '#header' => $header_grupos,
      '#options' => $output_grupos,
      '#empty' => t('No hay grupos disponibles...'),
      '#multiple' => FALSE
    ];
    $form['showcase3'] = [
      '#type' => 'label',
      '#title' => t('Catálogo a utilizar'),
      '#required' => TRUE
    ];
    $form['catalogo'] = [
      '#type' => 'tableselect',
      '#header' => $header_catalogos,
      '#options' => $output_catalogos,
      '#empty' => t('No hay catálogos disponibles...'),
      '#multiple' => FALSE
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Crear')
    ];
    return $form;
  }

  private function getUsuariosSeleccionados(array $form, FormStateInterface $form_state) {
    $valores = [];
    $seleccionados = [];
    foreach ($form_state->getValue('usuarios') as $valor) {
      if (!empty($valor)) {
        $valores[] = $valor;
      }
    }
    if (!empty($form_state->getValue('grupos'))) {
      foreach ($this->gruposAuditores[$form_state->getValue('grupos')]->__get('auditores') as $auditor) {
        $valores[] = $auditor->__get('id');
      }
    }
    $valores = array_diff(array_unique($valores), [
      Drupal::currentUser()->id()
    ]);
    foreach ($valores as $valor) {
        $seleccionados[$valor] = $this->auditores[$valor];
    }
    return $seleccionados;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    $app = $this->aplicaciones[$form_state->getValue('id_app')];
    $usuariosSeleccionados = $this->getUsuariosSeleccionados($form, $form_state);
    $today = date("Y-m-d");
    if($today > $form_state->getValue('fecha_estimada')){
      $messenger->addMessage(t('No puedes crear un auditoría con fecha de cierre anterior a la actual.'), 'error');
    }else if(empty($form_state->getValue('catalogo'))){
      $messenger->addMessage(t('El campo "Catálogo a utilizar" es obligatorio.'), 'error');
    }else{
        $auditoria = $controlador->crearAuditoria($form_state->getValue('nombre'), $form_state->getValue('descripcion'),
                $form_state->getValue('fecha_estimada'), $app, Drupal::currentUser()->id(), $this->catalogos[$form_state->getValue('catalogo')],
                $usuariosSeleccionados, $form_state->getValue('evaluacion'));
        $messenger->addMessage(t('La auditoría "') . $auditoria->__get('nombre') . t('" se ha creado correctamente.'));
    }
  }

  public function promptCallback(array $form, FormStateInterface $form_state) {
    return $form['app_fieldset'];
  }

}
