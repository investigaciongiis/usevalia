<?php

namespace Drupal\usevalia\Util;

use Drupal;
use TCPDF;

require_once('tcpdf\tcpdf.php');

class GeneradorPDF {

  private $catalogo;

  private $auditoria;

  private $grupos_directrices;

  private $aplicacion;

  private $lider;

  private $auditores;

  private $tareas;

  // Opción 0 : informe completo; Opción 1 : solo sugerencias de mejora.
  private $opcion;

  private $prioridades;

  public function __construct($auditoria, $opcion) {
    $this->opcion = $opcion;
    $controlador = Drupal::service('usevalia.controlador');
    $this->auditoria = $controlador->getAuditoriaById($auditoria);
    $this->catalogo = $this->auditoria->__get('catalogo');
    $this->grupos_directrices = $this->catalogo->__get('gruposDirectrices');
    $this->aplicacion = $this->auditoria->__get('aplicacion');
    $this->lider = $this->auditoria->__get('administrador');
    $this->auditores = $this->auditoria->getParticipantesSinLider();
    $this->prioridades = $controlador->getPrioridadesByCatalogo($this->catalogo);
  }

  private function cabecera(TCPDF $pdf) {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.1);
    // Header
    $html = '<table cellpadding="8" border="1">
                    <tr style="text-align:center;">
                        <td rowspan="2" style="width:142px; line-height: 50px;">
                            <img src="./modules/src/Logo/logoFO.png" border="0" height="25" width="100" />
                        </td>
                        <td rowspan="2" style="width:354px; line-height: 50px; font-size: 13pt; vertical-align:middle;">
                            <b>' . t('INFORME DE AUDITORÍA') . '</b>
                        </td>
                        <td rowspan="2" style="width:142px; font-size: 10pt; line-height: 50px;">' . t('Fecha') . ': ' . date('d/m/Y') . '</td>
                    </tr>
                </table>';
    // output the HTML content
    $pdf->writeHTML($html, TRUE, FALSE, TRUE, FALSE, '');
  }

  private function infoAuditoria(TCPDF $pdf) {
    // TODO arreglar fecha fin real.
    // Colors, line width.
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.1);
    $pdf->SetFont('helvetica', '', 11, '', TRUE);
    // Header and content.
    $pdf->Cell(70, 7, t('Nombre de la auditoría:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, $this->auditoria->__get('nombre'), 1, 'L');

    $pdf->Cell(70, 7, t('Aplicación a auditar:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, $this->aplicacion->__get('nombre'), 1, 'L');

    $pdf->Cell(70, 7, t('Catálogo utilizado:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, $this->catalogo->__get('nombre'), 1, 'L');

    $pdf->Cell(70, 7, t('Auditor líder:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, $this->lider->__get('nombre'), 1, 'L');

    $pdf->Cell(70, 7, t('Grupo auditor:'), 1, 0, 'L', 1);
    $nombres = '';
    foreach ($this->auditores as $auditor) {
      $nombres = implode(' - ', [
        $nombres,
        $auditor->__get('nombre'),
      ]);
    }
    $nombres = substr($nombres, 3);
    $pdf->MultiCell(0, 7, $nombres, 1, 'L');

    $pdf->Cell(70, 7, t('Fecha de realización:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, $this->auditoria->__get('fechaInicio')
        ->format('d/m/Y') . ' - ' . $this->auditoria->__get('fechaFinEstimada')
        ->format('d/m/Y'), 1, 'L');

    $pdf->Cell(70, 7, t('Tipo de evaluación:'), 1, 0, 'L', 1);
    $pdf->MultiCell(0, 7, t($this->auditoria->getEvaluacionNombre()), 1, 'L');

    $pdf->Cell(70, 20, t('Objetivo de la auditoría:'), 1, 0, 'L', 1, 1, '', 0, FALSE, 'T', 'T');
    $pdf->MultiCell(0, 20, $this->auditoria->__get('descripcion'), 1, 'L', 0, 1);

    $pdf->Cell(70, 7, t('Criterios de salida:'), 'TRL', 0, 'L', 1);
    $pdf->Cell(0, 7, t('Número de fallos permitidos en cada nivel de prioridad: '), 'TRL', 1, 'L');
    foreach ($this->prioridades as $prioridad) {
      $pdf->Cell(70, 7, '', 'RL', 0, 'L', 1);
      $pdf->MultiCell(0, 7, '      ' . t($prioridad->__get('nombre')) . ': ' . $prioridad->__get('fallos'), 'RL', 'L');
    }
    $pdf->Cell(70, 1, '', 'RLB', 0, 'L', 1);
    $pdf->Cell(0, 1, '', 'RLB', 1, 'L');

    // Espacio de separación entre info y resultados, NO QUITAR.
    $pdf->Cell(0, 8, '', 0, 1, 'L', 0);
  }

  private function cabecerasDirectrices(TCPDF $pdf, $directriz) {
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(15, 7, 'Id.', 1, 0, 'C', 1);
    $pdf->Cell(45, 7, t('Nombre'), 1, 0, 'L', 1);
    $pdf->Cell(90, 7, t('Descripción'), 1, 0, 'L', 1);
    $pdf->Cell(0, 7, t('Prioridad'), 1, 1, 'L', 1);
    $pdf->MultiCell(15, 7, $directriz->__get('eid'), 'RL', 'C', 0, 0);
    $nameL = $pdf->MultiCell(45, 7, $directriz->__get('nombre'), 'RL', 'L', 0, 0);
    $desL = $pdf->MultiCell(90, 7, $directriz->__get('descripcion'), 'RL', 'L', 0, 0);
    $pdf->MultiCell(0, 7, t($directriz->getPrioridad()), 'RL', 'L');
    return max($nameL, $desL);
  }

  private function checkOpcionesCabeceras($height, TCPDF $pdf) {
    $pdf->SetFillColor(200, 200, 200);
    $pdf->MultiCell(15, 5 * $height - 5, '', 'RLB', 'C', 0, 0);
    $pdf->MultiCell(45, 5 * $height - 5, '', 'RLB', 'L', 0, 0);
    $pdf->MultiCell(90, 5 * $height - 5, '', 'RLB', 'L', 0, 0);
    $pdf->MultiCell(0, 5 * $height - 5, '', 'RLB', 'L',0, 1);
    if ($this->opcion == 0) {
      $pdf->Cell(55, 7, t('Puntuación'), 1, 0, 'L', 1);
      $pdf->Cell(60, 7, t('Observación'), 1, 0, 'L', 1);
    }
    else {
      $pdf->Cell(55, 7, t('Auditor'), 1, 0, 'L', 1);
    }
    $pdf->Cell(0, 7, t('Sugerencias'), 1, 1, 'L', 1);
  }

  private function checkOpcionesContenido(TCPDF $pdf, $puntuaciones, $id_auditoria, $id_grupo, $id_directiva) {
    foreach ($puntuaciones as $puntuacion) {
      if ($this->opcion == 0) {
        $puntos = $puntuacion->__get('usuario')
            ->__get('nombre') . ' : ' . t($puntuacion->__get('puntuacion'));
        $puntL = $pdf->MultiCell(55, 7, $puntos, 'RL', 'L', 0, 0);
        $obvL = $pdf->MultiCell(60, 7, $puntuacion->__get('observacion'), 'RL', 'L', 0, 0);
      }else {
        $autL = $pdf->MultiCell(55, 7, $puntuacion->__get('usuario')
          ->__get('nombre'), 'RL', 'L', 0, 0);
      }
      $suggL = $pdf->MultiCell(0, 7, $puntuacion->__get('mejora'), 'RL', 'L');

      $height = max($puntL, $autL, $obvL, $suggL);

      if ($this->opcion == 0) {
        $pdf->MultiCell(55, 4 * $height - 4, '', 'RLB', 'L', 0, 0);
        $pdf->MultiCell(60, 4 * $height - 4, '', 'RLB', 'L', 0, 0);
      }else{
        $pdf->MultiCell(55, 4 * $height - 4, '', 'RLB', 'L', 0, 0);
      }
      $pdf->MultiCell(0, 4 * $height - 4, '', 'RLB', 'L', 0);
      $uriImage = 'https://giis.inf.um.es:446/drupal3/sites/default/files/screenshots/capture_'.$id_auditoria.'_'.$id_grupo.'_'.$id_directiva.'.jpg';
      $headers=get_headers($uriImage);
      if (stripos($headers[0],"200 OK")?true:false) {
        //$pdf->Image($uriImage,null,null,null,null);
        $pdf->Cell(0, 7, t('Captura'), 'TRL', 1, 0, 'L', 1);
        //$pdf->Cell(0, 7, $pdf->Image($uriImage,70,null,100,50), 'TRL', 1, 'L');
        $pdf->writeHTMLCell(0, 35, '', '', '<img style="margin: 10px;" src="' .$uriImage . '"/>', 1, 1, 0, true, 'C', true);
        $pdf->Cell(0, 8, '', 0, 1, 'L', 0);
      }
      $pdf->Ln();
    }
    $pdf->Ln();
  }

  private function resultadoDetalleAuditoriaBasicaEstandar(TCPDF $pdf) {
    foreach ($this->grupos_directrices as $grupo) {
      $directrices = $grupo->__get('directrices');
      if ($this->auditoria->__get('evaluacion') === 'basica') {
        $directrices = $grupo->getDirectricesPrioritarias();
      }
      foreach ($directrices as $directriz) {
        $height = $this->cabecerasDirectrices($pdf, $directriz);
        $puntuaciones = $this->auditoria->getPuntuacionesByDirectriz($directriz);
        $this->checkOpcionesCabeceras($height, $pdf);
        $this->checkOpcionesContenido($pdf, $puntuaciones, $this->auditoria->__get('id'), $grupo->__get('id'), $directriz->__get('iid'));
      }
    }
  }

  private function resultadoDetalleAuditoriaTareas(TCPDF $pdf) {
    foreach ($this->tareas as $tarea) {
      $pdf->SetFillColor(180, 180, 180);
      $pdf->SetFont('helvetica', 'B', 11, '', TRUE);
      $pdf->Cell(0, 10, t('Tarea: ') . t($tarea->__get('nombre')), 1, 1, 'C', 1);
      $pdf->SetFont('helvetica', '', 11, '', TRUE);
      foreach ($this->grupos_directrices as $grupo) {
        foreach ($grupo->__get('directrices') as $directriz) {
          $height = $this->cabecerasDirectrices($pdf, $directriz);
          $puntuaciones = $this->auditoria->getPuntuacionesByDirectrizTarea($directriz, $tarea);
          $this->checkOpcionesCabeceras($height, $pdf);
          $this->checkOpcionesContenido($pdf, $puntuaciones, $this->auditoria->__get('id'), $grupo->__get('id'), $directriz->__get('iid'));
        }
      }
    }
  }

  private function resultadoGeneral(TCPDF $pdf) {
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('helvetica', 'B', 11, '', TRUE);
    $pdf->Cell(0, 7, t('Resultado de la auditoría'), 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 11, '', TRUE);

    $resultado = $this->auditoria->calcularResultado($this->prioridades);

    $pdf->MultiCell(0, 7, t('Antendiendo a los criterios de salida indicados anteriormente en la información de la auditoría, ') . t($resultado), 1, 'C');
    $pdf->Ln();

    $pdf->SetFont('helvetica', 'B', 11, '', TRUE);
    $pdf->Cell(0, 7, t('Desglose de los resultados'), 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 11, '', TRUE);
    $pdf->Ln();
  }

  public function generarPDF() {
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, TRUE, 'UTF-8', FALSE);
    $pdf->SetTitle(t('Informe de auditoría'));

    $pdf->setPrintHeader(FALSE);
    $pdf->setPrintFooter(TRUE);

    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->setFontSubsetting(TRUE);

    $pdf->AddPage();

    $this->cabecera($pdf);
    $this->infoAuditoria($pdf);
    $this->resultadoGeneral($pdf);
    if ($this->auditoria->__get('evaluacion') != 'tareas') {
      $this->resultadoDetalleAuditoriaBasicaEstandar($pdf);
    }
    else {
      $controlador = Drupal::service('usevalia.controlador');
      $this->tareas = $controlador->getTareasByAuditoria($this->auditoria);
      $this->resultadoDetalleAuditoriaTareas($pdf);
    }
    // La opción S hace que devuelva el PDF como String.
    return $pdf->Output('', 'S');
  }

}
