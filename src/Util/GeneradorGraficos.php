<?php

namespace Drupal\usevalia\Util;

use AccBarPlot;
use BarPlot;
use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\usevalia\Classes\Auditoria;
use Graph;
use GroupBarPlot;
use LinePlot;
use PieGraph;
use PiePlot;
use PiePlot3D;
use SoftyTheme;

require_once('jpgraph/src/jpgraph.php');
require_once('jpgraph/src/jpgraph_line.php');
require_once('jpgraph/src/jpgraph_pie.php');
require_once('jpgraph/src/jpgraph_pie3d.php');
require_once('jpgraph/src/jpgraph_bar.php');

class GeneradorGraficos
{
  private function __construct()
  {
  }

  public static function porcetanjesFalloPass(Auditoria $auditoria)
  {
    $puntuaciones = $auditoria->getPuntuacionesByTipo();
    $data = array($puntuaciones['fallo'], $puntuaciones['aprobado'], $puntuaciones['N/A']);

    $graph = new PieGraph(400, 300);
    $graph->SetTheme(new SoftyTheme());
    $graph->title->Set(t('Porcentajes totales de directrices'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->title->SetMargin(30);

    $p1 = new PiePlot3D($data);
    $p1->SetAngle(40);
    $p1->SetSize(0.5);
    $p1->SetCenter(0.5, 0.5);
    $p1->SetLegends(array(t('Fallidas'), t('Superadas'), t('No aplicables')));

    $graph->Add($p1);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphProcentajes.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function resultadosPorDirectriz(Auditoria $auditoria)
  {
    $directrices = $auditoria->__get('catalogo')->getAllDirectrices();
    if($auditoria->__get('evaluacion') === 'basica'){
      $directrices = $auditoria->__get('catalogo')->getDirectricesPrioritarias();
    }
    $legend = array();
    $fallos = array();
    $aprobados = array();
    $na = array();
    foreach ($directrices as $directriz) {
      $puntuaciones = $auditoria->desgloseByDirectriz($directriz);
      $legend[] = $directriz->__get('eid');
      $fallos[] = $puntuaciones['fallo'];
      $aprobados[] = $puntuaciones['aprobado'];
      $na[] = $puntuaciones['N/A'];
    }

    $graph = new Graph(900, 350, 'auto');
    $graph->SetScale("textlin");
    $graph->SetShadow();
    $graph->SetTheme(new SoftyTheme());
    $graph->img->SetMargin(55, 30, 70, 60);
    $graph->title->SetMargin(40);

    $graph->xaxis->SetTickLabels($legend);
    $graph->xaxis->title->Set(t('ID directriz'));
    $graph->xaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->xaxis->title->SetMargin(10);
    $graph->yaxis->title->Set(t('Nº puntuaciones'));
    $graph->yaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->yaxis->title->SetMargin(10);

    $graph->title->Set(t('Resultados por directriz'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $graph->legend->SetFillColor('azure2@0.4');
    $graph->legend->SetFrameWeight(1);

    $bplot1 = new BarPlot($fallos);
    $bplot1->SetLegend(t("Fallo"));
    $bplot2 = new BarPlot($aprobados);
    $bplot2->SetLegend(t("Superada"));
    $bplot3 = new BarPlot($na);
    $bplot3->SetLegend(t("No aplicable"));

    $gbarplot = new GroupBarPlot(array($bplot1, $bplot2, $bplot3));
    $gbarplot->SetWidth(0.2);
    $graph->Add($gbarplot);

    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphDirectriz.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function resultadosPorPrioridad(Auditoria $auditoria)
  {
    $ordenadas = $auditoria->getDirectricesSortedPrioridad();
    $legend = array(t('Alto'), t('Medio'));
    $fallos = array($ordenadas['Alto']['fallo'], $ordenadas['Medio']['fallo']);
    $aprobados = array($ordenadas['Alto']['aprobado'], $ordenadas['Medio']['aprobado']);
    $na = array($ordenadas['Alto']['N/A'], $ordenadas['Medio']['N/A']);
    if($auditoria->__get('evaluacion') !== 'basica'){
      $fallos[] = $ordenadas['Bajo']['fallo'];
      $aprobados[] = $ordenadas['Bajo']['aprobado'];
      $na[] = $ordenadas['Bajo']['N/A'];
      $legend[] = t('Bajo');
    }

    $graph = new Graph(400, 300, 'auto');
    $graph->SetScale("textlin");
    $graph->SetShadow();
    $graph->SetTheme(new SoftyTheme());
    $graph->img->SetMargin(55, 30, 60, 80);
    $graph->title->SetMargin(30);

    $graph->xaxis->SetTickLabels($legend);
    $graph->xaxis->title->Set(t('Prioridad'));
    $graph->xaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->xaxis->title->SetMargin(5);
    $graph->yaxis->title->Set(t('Nº puntuaciones'));
    $graph->yaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->yaxis->title->SetMargin(10);

    $graph->title->Set(t('Resultados por prioridad'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $graph->legend->SetFillColor('azure2@0.4');
    $graph->legend->SetFrameWeight(1);

    $bplot1 = new BarPlot($fallos);
    $bplot1->SetLegend(t("Fallidas"));
    $bplot2 = new BarPlot($aprobados);
    $bplot2->SetLegend(t("Superadas"));
    $bplot3 = new BarPlot($na);
    $bplot3->SetLegend(t("No aplicables"));

    $gbarplot = new GroupBarPlot(array($bplot1, $bplot2, $bplot3));
    $gbarplot->SetWidth(0.4);
    $graph->Add($gbarplot);

    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphPrioridad.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function porcentajesGrupos(Auditoria $auditoria)
  {
    $data = array();
    $labels = array();

    foreach ($auditoria->__get('catalogo')->__get('gruposDirectrices') as $grupo){
      $labels[] = "{$grupo->__get('nombre')}\n(%.1f%%)";
      if($auditoria->__get('evaluacion') === 'basica'){
        $data[] = count($grupo->getDirectricesPrioritarias());
      }else{
        $data[] = $grupo->getNumeroDirectrices();
      }
    }

    $graph = new PieGraph(400, 400);
    $graph->SetTheme(new SoftyTheme());
    $graph->title->Set(t('Porcentaje de directrices por grupo'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->title->SetMargin(30);

    $p1 = new PiePlot($data);
    $p1->SetCenter(0.5, 0.53);
    $p1->SetSize(0.3);

    $p1->SetLabels($labels);
    $p1->SetLabelPos(1);
    $p1->SetLabelType(PIE_VALUE_PER);
    $p1->value->Show();
    $p1->value->SetFont(FF_ARIAL, FS_NORMAL, 9);
    $p1->value->SetColor('darkgray');

    $graph->Add($p1);

    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphPorcentajesGrupos.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function resultadosPorGrupo(Auditoria $auditoria)
  {
    $legend = array();
    $fallos = array();
    $aprobados = array();
    $na = array();
    foreach ($auditoria->__get('catalogo')->__get('gruposDirectrices') as $grupo){
      $ordenadas = $auditoria->getDirectricesSortedGrupo($grupo);
      $fallos[] = $ordenadas['fallo'];
      $aprobados[] = $ordenadas['aprobado'];
      $na[] = $ordenadas['N/A'];
      $legend[] = $grupo->__get('nombre');
    }

    $graph = new Graph(900, 380, 'auto');
    $graph->SetScale("textlin");
    $graph->SetShadow();
    $graph->SetTheme(new SoftyTheme());
    $graph->img->SetMargin(55, 30, 35, 60);
    $graph->title->SetMargin(5);

    $graph->xaxis->SetTickLabels($legend);
    $graph->xaxis->title->Set(t('Grupo'));
    $graph->xaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->xaxis->title->SetMargin(5);
    $graph->yaxis->title->Set(t('Nº puntuaciones'));
    $graph->yaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->yaxis->title->SetMargin(10);

    $graph->title->Set(t('Resultados por grupo'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $graph->legend->SetFillColor('azure2@0.4');
    $graph->legend->SetFrameWeight(1);

    $bplot1 = new BarPlot($fallos);
    $bplot1->SetLegend(t("Fallidas"));
    $bplot2 = new BarPlot($aprobados);
    $bplot2->SetLegend(t("Superadas"));
    $bplot3 = new BarPlot($na);
    $bplot3->SetLegend(t("No aplicables"));

    $gbarplot = new GroupBarPlot(array($bplot1, $bplot2, $bplot3));
    $gbarplot->SetWidth(0.2);
    $graph->Add($gbarplot);

    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphResGrupo.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function resultadosPorTarea(Auditoria $auditoria)
  {
    $legend = array();
    $fallos = array();
    $aprobados = array();
    $na = array();
    $controlador = Drupal::service('usevalia.controlador');
    foreach ($controlador->getTareasByAuditoria($auditoria) as $tarea){
      $ordenadas = $auditoria->getDirectricesSortedTarea($tarea);
      $fallos[] = $ordenadas['fallo'];
      $aprobados[] = $ordenadas['aprobado'];
      $na[] = $ordenadas['N/A'];
      $legend[] = t($tarea->__get('nombre'));
    }

    $graph = new Graph(900, 550, 'auto');
    $graph->SetScale("textlin");
    $graph->SetShadow();
    $graph->SetTheme(new SoftyTheme());
    $graph->img->SetMargin(55, 30, 45, 200);
    $graph->title->SetMargin(30);

    $graph->xaxis->SetTickLabels($legend);
    $graph->xaxis->title->Set(t('Tarea'));
    $graph->xaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->xaxis->title->SetMargin(5);
    $graph->xaxis->SetLabelAngle(50);
    $graph->yaxis->title->Set(t('Nº puntuaciones'));
    $graph->yaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
    $graph->yaxis->title->SetMargin(10);

    $graph->title->Set(t('Resultados por tarea'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $graph->legend->SetFillColor('azure2@0.4');
    $graph->legend->SetFrameWeight(1);

    $bplot1 = new BarPlot($fallos);
    $bplot1->SetLegend(t("Fallidas"));
    $bplot2 = new BarPlot($aprobados);
    $bplot2->SetLegend(t("Superadas"));
    $bplot3 = new BarPlot($na);
    $bplot3->SetLegend(t("No aplicables"));

    $gbarplot = new GroupBarPlot(array($bplot1, $bplot2, $bplot3));
    $gbarplot->SetWidth(0.4);
    $graph->Add($gbarplot);

    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphResTarea.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }

  public static function porcetanjesTareas(Auditoria $auditoria)
  {
    $res['fallo'] = 0;
    $res['aprobado'] = 0;
    $controlador = Drupal::service('usevalia.controlador');
    foreach ($controlador->getTareasByAuditoria($auditoria) as $tarea){
      $res[$tarea->isSuperada($auditoria)]++;
    }
    $data = array($res['fallo'], $res['aprobado']);

    $graph = new PieGraph(400, 300);
    $graph->SetTheme(new SoftyTheme());
    $graph->title->Set(t('Porcentajes por tareas'));
    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
    $graph->title->SetMargin(30);

    $p1 = new PiePlot3D($data);
    $p1->SetAngle(40);
    $p1->SetSize(0.5);
    $p1->SetCenter(0.5, 0.5);
    $p1->SetLegends(array(t('Fallidas'), t('Superadas')));

    $graph->Add($p1);
    $graph->legend->SetPos(0.5, 0.99, 'center', 'bottom');
    $graph->legend->SetColumns(3);
    $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
    $uri = 'public://graphProcentajesTarea.png';
    $graph->img->Stream($uri);
    $contenido = file_get_contents($uri);
    return file_save_data($contenido, $uri, FILE_EXISTS_REPLACE);
  }
}
