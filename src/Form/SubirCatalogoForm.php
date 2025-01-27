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
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\Directriz;
use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\usevalia\Classes\GrupoDirectrices;
use function file_save_data;

/**
 * Description of UsevaliaForm
 *
 * @author luis
 */
class SubirCatalogoForm extends FormBase
{

  private $puntuaciones;

  private $gruposAuditores;

  public function getFormId()
  {
    return 'subir_catalogo_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $controlador = Drupal::service('usevalia.controlador');
    $this->puntuaciones = $controlador->getAllEscalasPuntuaciones();

    $output = [];
    foreach ($this->puntuaciones as $escala) {
      $output[$escala->__get('id')] = $escala->__get('nombre');
    }
    $form['#cache']['max-age'] = 0;
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título del catálogo'),
      '#required' => TRUE
    ];
    $header_grupos = [
      'nombre' => t('Nombre del grupo'),
      'descripcion' => t('Descripción'),
      'etiquetas' => t('Etiquetas')
    ];
    $output_grupos = [];
    $this->gruposAuditores = $controlador->getGruposByUsuarioId(Drupal::currentUser()->id());
    foreach ($this->gruposAuditores as $grupo) {
      $output_grupos[$grupo->__get('id')] = [
        'nombre' => $grupo->__get('nombre'),
        'descripcion' => $grupo->__get('descripcion'),
        'etiquetas' => implode(', ', $grupo->__get('etiquetas'))
      ];
    }
    $form['showcase'] = [
      '#type' => 'label',
      '#title' => t('Grupo de auditores'),
      '#required' => TRUE
    ];
    $form['grupos'] = [
      '#type' => 'tableselect',
      '#header' => $header_grupos,
      '#options' => $output_grupos,
      '#empty' => t('No hay grupos disponibles...'),
      '#multiple' => FALSE
    ];
    $form['select'] = [
      '#type' => 'select',
      '#title' => $this->t('Selecciona un esquema de puntuación'),
      '#options' => $output,
      '#required' => TRUE
    ];
    $form['permiso-lectura'] = [
      '#type' => 'select',
      '#title' => $this->t('Permiso de lectura'),
      '#options' => [
        'PUBLICO' => t('PUBLICO'),
        'GRUPO' => t('GRUPO'),
        'PRIVADO' => t('PRIVADO')
      ]
    ];
    $form['permiso-escritura'] = [
      '#type' => 'select',
      '#title' => $this->t('Permiso de escritura'),
      '#options' => [
        'PUBLICO' => t('PUBLICO'),
        'GRUPO' => t('GRUPO'),
        'PRIVADO' => t('PRIVADO')
      ]
    ];

    $form['manual'] = [
      '#type' => 'inline_template',
      '#template' => '<div id="catalog-instructions">{{title}}<br/><p>{{msg}}</p></div>',
      '#context' => [
        'title' => t('Instrucciones del formato del catálogo'),
        'msg' => t('Debe ser un archivo CSV, donde el delimitador de campo es una coma (,) y el de texto es
          una doble comilla ("). Usevalia lee el archivo de la siguiente manera: En la primera fila, debes poner las
          prioridades de menor a mayor (Bajo, Medio, Alto). En las siguientes filas, para indicar que es un grupo de
          directivas, solo debes poner el texto del  título en la primera columna mientras que si deseas poner una
          directriz, debes rellenar los datos a partir de la segunda columna. Los datos que toma el programa son los
          siguientes: identificador, nombre, descripción, prioridad. A continuación tienes un pequeño ejemplo.')
      ]
    ];
    $template_ejemplo = '<table id="catalog-example" style="border: 1px solid black;">
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">'.t('Bajo').'</td>
            <td style="border: 1px solid black;">'.t('Medio').'</td>
            <td style="border: 1px solid black;">'.t('Alto').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">'.t('3').'</td>
            <td style="border: 1px solid black;">'.t('5').'</td>
            <td style="border: 1px solid black;">'.t('1').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">'.t('Grupo').' 1</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. 1</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Alto').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. 2</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Medio').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. n</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Bajo').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;">'.t('Grupo').' 2</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. 1</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Bajo').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. 2</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Alto').'</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
            <td style="border: 1px solid black;">...</td>
        </tr>
        <tr style="border: 1px solid black;">
            <td style="border: 1px solid black;"></td>
            <td style="border: 1px solid black;">Id. n</td>
            <td style="border: 1px solid black;">'.t('Nombre').'</td>
            <td style="border: 1px solid black;">'.t('Descripción').'</td>
            <td style="border: 1px solid black;">'.t('Bajo').'</td>
        </tr>
        </table>';
    $form['ejemplo'] = [
      '#type' => 'inline_template',
      '#template' => $template_ejemplo,
      '#context' => []
    ];

    $uri = 'public://PlantillaCatalogo.csv';
    $filename = './modules/src/Plantillas/PlantillaCatalogo.csv';
    $contenido = file_get_contents($filename);
    // Aunque la variable de abajo no se use, NO se debe borrar, es imprescindible para que se pueda obtener el enlace a la plantilla.
    $archivo = file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
    $url = file_create_url($uri);
    $uri2 = 'public://CatalogTemplate.csv';
    $filename2 = './modules/src/Plantillas/CatalogTemplate.csv';
    $contenido2 = file_get_contents($filename2);
    // Aunque la variable de abajo no se use, NO se debe borrar, es imprescindible para que se pueda obtener el enlace a la plantilla.
    $archivo2 = file_save_data($contenido2, $uri2, FILE_EXISTS_REPLACE);
    $url2 = file_create_url($uri2);
    $form['download'] = [
      '#type' => 'inline_template',
      '#template' => '<div id="catalog-template">{{msg}}</div>',
      '#context' => [
        'msg' => $this->t('Descarga una plantilla para crear tu catálogo en este enlace: <a href=":url">español</a>, <a href=":url2">english</a> </br> La descarga comenzará automaticamente.', [
          '%destination' => $uri,
          '@uri' => $uri,
          ':url' => $url,
          '%destination2' => $uri2,
          '@uri2' => $uri2,
          ':url2' => $url2,
        ])
      ]
    ];

    $form['upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Archivo del catálogo'),
      '#multiple' => FALSE,
      '#upload_location' => 'temporary://',
      '#upload_validators' => [
        'file_validate_extensions' => [
          'csv'
        ]
      ],
      '#required' => TRUE
    ];
    $form['submit_button'] = [
      '#type' => 'submit',
      '#value' => t('Subir catálogo')
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $controlador = Drupal::service('usevalia.controlador');
    $messenger = Drupal::messenger();
    $this->puntuaciones = $controlador->getAllEscalasPuntuaciones();
    $this->gruposAuditores = $controlador->getAllGruposAuditores();
    if (empty($form_state->getValue('grupos'))) {
      $messenger->addMessage(t('El campo "Grupos de usuarios" es obligatorio.'), 'error');
    } else {
      // drupal_set_message(print_r($contents));
      // Recojo el fichero
      $fid = $form_state->getValue('upload');
      $file = File::load($fid[0]);
      $data = file_get_contents($file->getFileUri());
      // Separo las lineas
      $contents = explode(PHP_EOL, str_replace([
        '\r\n',
        '\n\r',
        '\r'
      ], PHP_EOL, $data));
      $catalogo = $controlador->crearCatalogo($form_state->getValue('title'), $this->puntuaciones[$form_state->getValue('select')], Drupal::currentUser()->id(), $this->gruposAuditores[$form_state->getValue('grupos')], $form_state->getValue('permiso-lectura'), $form_state->getValue('permiso-escritura'));
      $pesos = [];
      $prioridades_name = [];
      $prioridades = [];
      $prioridades_creadas = [];
      $trans = array(
        'Low' => 'Bajo',
        'Medium' => 'Medio',
        'High' => 'Alto'
      );
      // Recorro cada valor del contenido
      foreach ($contents as $i => $linea) {
        if (!empty($linea)) {
          $parametros = str_getcsv(trim($linea), ',', '"');
          if ($i == 0) {
            if(sizeof($parametros) < 3){
              $messenger->addError(t('La fila de prioridades no puede estar vacía.'));
              $controlador->borrarCatalogo($catalogo);
              return;
            }
            foreach ($parametros as $prio){
              if(!empty($prio) && (trim($prio) !== 'Bajo') && (trim($prio) !== 'Medio') && (trim($prio) !== 'Alto')) {
                $prioridades_name[] = $trans[trim($prio)];
              }else if(!empty($prio)){
                $prioridades_name[] = trim($prio);
              }
            }
            foreach ($prioridades_name as $j => $valor_peso) {
              $pesos[$valor_peso] = $j + 1;
            }
          } else if ($i == 1) {
            foreach ($prioridades_name as $j => $valor_peso) {
              if(!empty($valor_peso)){
                if(!is_nan($parametros[$j])){
                  $prioridades[] = [
                    'nombre' => $prioridades_name[$j],
                    'peso' => $pesos[$valor_peso],
                    'fallos' => (int)($parametros[$j])
                  ];
                }else{
                  $messenger->addError(t('Debes rellenar los fallos por prioridad correctamente.'));
                  $controlador->borrarCatalogo($catalogo);
                  return;
                }
              }
            }
            $prioridades_creadas = $controlador->crearPrioridades($catalogo, $prioridades);
          } else if (!empty($parametros[0])) {
            if (isset($nombreGrupo) && isset($directrices)) {
              $controlador->addGrupoDirectrices($catalogo, $nombreGrupo, $directrices);
            }
            $nombreGrupo = $parametros[0];
            $directrices = [];
            // drupal_set_message($clave . ' -> Grupo');
          } else {
            $prio = trim($parametros[4]);
            if(array_key_exists($prio, $trans)){
              if($prio !== 'Bajo' && $prio !== 'Medio' && $prio !== 'Alto') {
                $prio = $trans[$prio];
              }
            }
            if(!array_key_exists($prio, $prioridades_creadas)){
              $messenger->addError(t('La prioridad "').$prio.t('" no ha sido indicada en la fila de prioridades.'));
              $controlador->borrarCatalogo($catalogo);
              return;
            }
            $directrices[] = [
              'id' => $parametros[1],
              'nombre' => $parametros[2],
              'descripcion' => $parametros[3],
              'esquema' => $this->puntuaciones[$form_state->getValue('select')],
              'peso' => $prioridades_creadas[$prio]
            ];
          }
        }
      }
      // drupal_set_message($contents[0]);
      // $contenido = str_getcsv($contents[0],",",'"');
      // drupal_set_message($contenido[0]);
      // drupal_set_message($pesos[$contenido[1]]);
      // drupal_set_message($prioridades[0]['fallos']);
      $controlador->addGrupoDirectrices($catalogo, $nombreGrupo, $directrices);
      $messenger->addMessage(t('El catálogo "') . $catalogo->__get('nombre') . t('" se ha creado correctamente.'));
    }
  }

  public function textfieldsCallback($form, FormStateInterface $form_state)
  {
    return $form['textfields_container'];
  }
}
