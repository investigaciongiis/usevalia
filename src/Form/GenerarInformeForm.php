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
use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\stream_wrapper_example\StreamWrapper\SessionWrapper;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\usevalia\Util\GeneradorPDF;
use function file_save_data;

/**
 * Description of GenerarInformeForm
 *
 * @author luis
 */
class GenerarInformeForm extends FormBase
{

  protected $state;

  protected $requestStack;

  protected $fileSystem;

  protected $streamWrapperManager;

  protected $sessionSchemeEnabled;

  protected $moduleHandler;

  private $auditorias;

  public function __construct(StateInterface $state, FileSystemInterface $file_system, StreamWrapperManagerInterface $stream_wrapper_manager, ModuleHandlerInterface $module_handler, RequestStack $request_stack)
  {
    $this->state = $state;
    $this->fileSystem = $file_system;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->sessionSchemeEnabled = $this->moduleHandler->moduleExists('stream_wrapper_example');
  }

  public static function create(ContainerInterface $container)
  {
    $state = $container->get('state');
    $file_system = $container->get('file_system');
    $module_handler = $container->get('module_handler');
    $request_stack = $container->get('request_stack');
    $stream_wrapper_manager = $container->get('stream_wrapper_manager');
    return new static($state, $file_system, $stream_wrapper_manager, $module_handler, $request_stack);
  }

  protected function getExternalUrl($file_object)
  {
    if ($file_object instanceof FileInterface) {
      $uri = $file_object->getFileUri();
    } else {
      $uri = file_create_url($file_object);
    }

    try {
      $wrapper = $this->streamWrapperManager->getViaUri($uri);
      if ($wrapper) {
        $external_url = $wrapper->getExternalUrl();
        if ($external_url) {
          $url = Url::fromUri($external_url);
          return $url;
        }
      } else {
        $url = Url::fromUri($uri);
        return $url;
      }
    } catch (Exception $e) {
      return FALSE;
    }
    return FALSE;
  }

  protected function setDefaultFile($uri)
  {
    $this->state->set('file_example_default_file', (string)$uri);
  }

  public function getFormId()
  {
    return 'generar-informe-form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $controlador = Drupal::service('usevalia.controlador');

    $form['opciones'] = array(
      '#type' => 'radios',
      '#title' => t('Tipo de informe'),
      '#default_value' => 0,
      '#options' => array(
        0 => $this
          ->t('Completo'),
        1 => $this
          ->t('Sin puntuaciones y sin notas'),
      ),
    );

    $header = [
      'nombre' => t('Nombre de la auditoría'),
      'aplicacion' => t('Nombre de la aplicación'),
      'catalogo' => t('Catálogo utilizado'),
      'auditores' => t('Participantes')
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
        'auditores' => implode(', ', $participantes)
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
      '#value' => t('Generar')
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $messenger = Drupal::messenger();
    if (!empty($form_state->getValue('table'))) {
      $auditoria = $this->auditorias[$form_state->getValue('table')];
      $pdf = new GeneradorPDF($auditoria->__get('id'), $form_state->getValue('opciones'));
      $uri = 'public://informe.pdf';
      $file_object = file_save_data($pdf->generarPDF(), $uri, FILE_EXISTS_RENAME);
      if (!empty($file_object)) {
        $url = $this->getExternalUrl($file_object);
        $this->setDefaultFile($file_object->getFileUri());
        $file_data = $file_object->toArray();
        if ($url) {
          $messenger->addMessage($this->t('Se ha creado el informe correctamente: (<a href=":url" target="_blank">Enlace de descarga</a>)', [
            '%destination' => $uri,
            '@uri' => $file_object->getFileUri(),
            ':url' => $url->toString()
          ]));
        } else {
          $messenger->addMessage(t('Recarga y vuelve a intentarlo.'), 'error');
        }
      }
    } else {
      $messenger->addMessage(t('Debes seleccionar un auditoría.'), 'error');
    }
  }
}
