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

namespace Drupal\usevalia\Service;

use Drupal\usevalia\Classes\Auditoria;
use Drupal\usevalia\Classes\Aplicacion;
use Drupal\usevalia\Classes\Catalogo;
use Drupal\usevalia\Classes\EsquemaPuntuacion;
use Drupal\usevalia\Classes\GrupoAuditores;
use Drupal\usevalia\Classes\DAO\DAOFactoria;
use Drupal\usevalia\Classes\CategoriaWeb;
use Drupal\usevalia\Classes\Tarea;
use Drupal\usevalia\Classes\Auditor;

/**
 * Description of UsevaliaService
 *
 * @author luis
 */
class UsevaliaService {

  private $dao;
  private $auditoria;

  public function __construct() {
    $this->dao = new DAOFactoria();
  }

  public function crearAplicacion($nombre, $url, $descripcion, $categoria) {
    $daoAplicacion = $this->dao->getAplicacionDAO();
    return $daoAplicacion->create($nombre, $url, $descripcion, $categoria);
  }

  public function crearAuditoria($nombre, $descripcion, $fechaFinEstimada, Aplicacion $aplicacion, $admin, Catalogo $catalogo, array $participantes, $evaluacion) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $usuario_admin = $this->getAuditorById($admin);
    return $daoAuditoria->create($nombre, $descripcion, $fechaFinEstimada, $aplicacion, $usuario_admin, $catalogo, $participantes, $evaluacion);
  }

  public function crearCatalogo($nombre, EsquemaPuntuacion $esquemaPuntuacion, $autor_id, GrupoAuditores $grupoAuditores, $permisoLectura, $permisoEscritura) {
    $autor = $this->getAuditorById($autor_id);
    $daoCatalogo = $this->dao->getCatalogoDAO();
    return $daoCatalogo->create($nombre, $esquemaPuntuacion, $autor, $grupoAuditores, $permisoLectura, $permisoEscritura);
  }

  public function crearEsquemaPuntuacion($nombre, $descripcion, array $valores, array $tipos) {
    $daoEsquema = $this->dao->getEsquemaPuntuacionDAO();
    return $daoEsquema->create($nombre, $descripcion, $valores, $tipos);
  }

  public function crearGrupoAuditores($nombre, $descripcion, array $auditores, array $etiquetas) {
    $daoEtiqueta = $this->dao->getEtiquetaDAO();
    $daoGrupoAuditores = $this->dao->getGrupoAuditoresDAO();
    $etiquetasMarcadas = $daoEtiqueta->getAllByValorAndCreateIfNotExists($etiquetas);
    return $daoGrupoAuditores->create($nombre, $descripcion, $auditores, $etiquetasMarcadas);
  }

  public function crearPrioridades(Catalogo $catalogo, array $prioridades){
      $daoPrioridad = $this->dao->getPrioridadDAO();
      return $daoPrioridad->createMany($catalogo, $prioridades);
  }

  public function updateAplicacion($id, $nombre, $descripcion, $url, $categoria) {
    $daoAplicacion = $this->dao->getAplicacionDAO();
    $daoAplicacion->update($id, $nombre, $url, $descripcion, $categoria);
  }

  public function seUsaAplicacion(Aplicacion $aplicacion) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    if ($daoAuditoria->getNumAuditorias($aplicacion) > 0) {
      return true;
    }
    return false;
  }

  public function borrarAplicacion(Aplicacion $aplicacion) {
    $daoAplicacion = $this->dao->getAplicacionDAO();
    if (!$this->seUsaAplicacion($aplicacion)) {
      $daoAplicacion->delete($aplicacion);
      return true;
    }
    return false;
  }

  public function seUsaEsquemaPuntuacion(EsquemaPuntuacion $esquema) {
    $daoCatalogo = $this->dao->getCatalogoDAO();
    $daoGrupoDirectrices = $this->dao->getGrupoDirectricesDAO();
    $daoDirectriz = $this->dao->getDirectrizDAO();
    if (($daoCatalogo->getNumEsquemasPuntuacion($esquema) > 0) || ($daoGrupoDirectrices->getNumEsquemasPuntuacion($esquema) > 0) || ($daoDirectriz->getNumEsquemasPuntuacion($esquema) > 0)) {
      return true;
    }
    return false;
  }

  public function updateEsquemaPuntuacion(EsquemaPuntuacion $esquema, $nombre, $descripcion, array $valores) {
    $daoEsquemaPuntuacion = $this->dao->getEsquemaPuntuacionDAO();
    if (!$this->seUsaEsquemaPuntuacion($esquema)) {
      $daoEsquemaPuntuacion->update($esquema, $nombre, $descripcion, $valores);
      return true;
    }
    return false;
  }

  public function borrarEsquemaPuntuacion(EsquemaPuntuacion $esquema) {
    $daoEsquemaPuntuacion = $this->dao->getEsquemaPuntuacionDAO();
    if (!$this->seUsaEsquemaPuntuacion($esquema)) {
      $daoEsquemaPuntuacion->delete($esquema);
      return true;
    }
    return false;
  }

  public function borrarGrupoAuditores(GrupoAuditores $grupo) {
    $daoCatalogo = $this->dao->getCatalogoDAO();
    $daoGrupoAuditores = $this->dao->getGrupoAuditoresDAO();
    if ($daoCatalogo->getNumGrupos($grupo) == 0) {
      $daoGrupoAuditores->delete($grupo);
      return true;
    }
    return false;
  }

  public function borrarCatalogo(Catalogo $catalogo) {
    $daoCatalogo = $this->dao->getCatalogoDAO();
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    if ($daoAuditoria->getNumAuditoriasCatalogo($catalogo) == 0) {
      $daoCatalogo->delete($catalogo);
      return true;
    }
    return false;
  }

  public function borrarAuditoria(Auditoria $auditoria) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    if ($daoPuntuacion->getNumPuntuaciones($auditoria) == 0) {
      $daoAuditoria->delete($auditoria);
      return true;
    }
    return false;
  }

  public function getAllAuditores() {
    $daoAuditor = $this->dao->getAuditorDAO();
    return $daoAuditor->getAll();
  }

  public function getAllAuditoresExcepto($id) {
    $daoAuditor = $this->dao->getAuditorDAO();
    return $daoAuditor->getAllExcept($id);
  }

  public function getAllCatalogos() {
    $escala = $this->getAllEscalasPuntuaciones();
    $auditores = $this->getAllAuditores();
    $grupos = $this->getAllGruposAuditores();
    $daoCatalogo = $this->dao->getCatalogoDAO();
    return $daoCatalogo->getAll($escala, $auditores, $grupos);
  }

  private function completarCatalogo($catalogo) {
    $daoGrupoDirectrices = $this->dao->getGrupoDirectricesDAO();
    $daoDirectriz = $this->dao->getDirectrizDAO();
    $gruposDirectrices = $daoGrupoDirectrices->getAllByCatalogo($catalogo);
    foreach ($gruposDirectrices as $grupo) {
      /*$dir = */$daoDirectriz->getAllByGrupo($grupo);
      //$grupo->addDirectrices($dir);
    }
    //$catalogo->addGrupos($gruposDirectrices);
  }

  public function getAllCatalogosFull() {
    $catalogos = $this->getAllCatalogos();
    foreach ($catalogos as $catalogo) {
      $this->completarCatalogo($catalogo);
    }
    return $catalogos;
  }

  public function getAllEscalasPuntuaciones() {
    $daoEscala = $this->dao->getEsquemaPuntuacionDAO();
    return $daoEscala->getAll();
  }

  public function getEscalaPuntuacion($id) {
    $daoEscala = $this->dao->getEsquemaPuntuacionDAO();
    return $daoEscala->getById($id);
  }

  public function getAllAuditoriasFrom($id) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $aplicaciones = $this->getAllAplicaciones();
    $auditores = $this->getAllAuditores();
    $catalogos = $this->getAllCatalogos();
    return $daoAuditoria->getFromUsuario($id, $aplicaciones, $auditores, $catalogos);
  }

  public function getAllAuditoriasFullFrom($id) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $auditores = $this->getAllAuditores();
    $auditorias = $this->getAllAuditoriasFrom($id);
    foreach ($auditorias as $auditoria) {
      $this->completarCatalogo($auditoria->__get('catalogo'));
      $auditoria->addManyPuntuaciones($daoPuntuacion->getAllByAuditoria($auditoria, $auditores));
    }
    return $auditorias;
  }

  public function getAuditoriasAbiertasFrom($id) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $aplicaciones = $this->getAllAplicaciones();
    $auditores = $this->getAllAuditores();
    $catalogos = $this->getAllCatalogos();
    return $daoAuditoria->getAbiertasFromUsuario($id, $aplicaciones, $auditores, $catalogos);
  }

  public function getAuditoriasFullAbiertasFrom($id) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $auditores = $this->getAllAuditores();
    $auditorias = $this->getAuditoriasAbiertasFrom($id);
    foreach ($auditorias as $auditoria) {
      $this->completarCatalogo($auditoria->__get('catalogo'));
      $auditoria->addManyPuntuaciones($daoPuntuacion->getAllByAuditoria($auditoria, $auditores));
    }
    return $auditorias;
  }

  public function getAuditoriasCerradasFrom($id) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $aplicaciones = $this->getAllAplicaciones();
    $auditores = $this->getAllAuditores();
    $catalogos = $this->getAllCatalogos();
    return $daoAuditoria->getCerradasFromUsuario($id, $aplicaciones, $auditores, $catalogos);
  }

  public function getAuditoriasFullCerradasFrom($id) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $auditores = $this->getAllAuditores();
    $auditorias = $this->getAuditoriasCerradasFrom($id);
    foreach ($auditorias as $auditoria) {
      $this->completarCatalogo($auditoria->__get('catalogo'));
      $auditoria->addManyPuntuaciones($daoPuntuacion->getAllByAuditoria($auditoria, $auditores));
    }
    return $auditorias;
  }

  public function getAuditoriasFromAdmin($id) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $aplicaciones = $this->getAllAplicaciones();
    $auditores = $this->getAllAuditores();
    $catalogos = $this->getAllCatalogos();
    return $daoAuditoria->getByAdmin($id, $aplicaciones, $auditores, $catalogos);
  }

  public function getAllAplicaciones() {
    $daoAplicaciones = $this->dao->getAplicacionDAO();
    return $daoAplicaciones->getAll();
  }

  public function getAplicacion($id) {
    $daoAplicaciones = $this->dao->getAplicacionDAO();
    return $daoAplicaciones->get($id);
  }

  public function addGrupoDirectrices(Catalogo $catalogo, $nombre, array $directrices) {
    $daoGrupoDirectrices = $this->dao->getGrupoDirectricesDAO();
    $daoDirectriz = $this->dao->getDirectrizDAO();
    $grupo = $daoGrupoDirectrices->create($nombre, $catalogo);
    $daoDirectriz->createMany($grupo, $directrices);
    return $grupo;
  }

  public function crearPuntuacionCatalogo($idUsuario, Auditoria $auditoria, array $puntuaciones) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $usuario = $this->getAuditorById($idUsuario);
    return $daoPuntuacion->createMany($usuario, $auditoria, $puntuaciones);
  }

  public function cambiarPuntuacionCatalogo($idUsuario, Auditoria $auditoria, array $puntuaciones) {
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $usuario = $this->getAuditorById($idUsuario);
    $daoPuntuacion->updateMany($usuario, $auditoria, $puntuaciones);
  }

  public function createPuntuacionTarea($idUsuario, Auditoria $auditoria, array $puntuaciones, Tarea $tarea) {
      $daoPuntuacion = $this->dao->getPuntuacionDAO();
      $usuario = $this->getAuditorById($idUsuario);
      return $daoPuntuacion->createPuntuacionesTarea($usuario, $auditoria, $puntuaciones, $tarea);
  }

  public function cambiarPuntuacionTarea($idUsuario, Auditoria $auditoria, array $puntuaciones, Tarea $tarea) {
      $daoPuntuacion = $this->dao->getPuntuacionDAO();
      $usuario = $this->getAuditorById($idUsuario);
      $daoPuntuacion->updateManyTarea($usuario, $auditoria, $puntuaciones, $tarea);
  }

  public function getAuditorById($id) {
    $daoAuditor = $this->dao->getAuditorDAO();
    return $daoAuditor->getById($id);
  }

  public function getFullCatalogoById($id) {
    $escala = $this->getAllEscalasPuntuaciones();
    $auditores = $this->getAllAuditores();
    $gruposAuditores = $this->getAllGruposAuditores();
    $daoCatalogo = $this->dao->getCatalogoDAO();
    $catalogo = $daoCatalogo->getById($id, $escala, $auditores, $gruposAuditores);
    $this->completarCatalogo($catalogo);
    return $catalogo;
  }

  public function getAuditoriaById($id) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $daoPuntuacion = $this->dao->getPuntuacionDAO();
    $auditores = $this->getAllAuditores();
    $auditoria = $daoAuditoria->getById($id, $this->getAllAplicaciones(), $auditores, $this->getAllCatalogos());
    $this->completarCatalogo($auditoria->__get('catalogo'));
    $auditoria->addManyPuntuaciones($daoPuntuacion->getAllByAuditoria($auditoria, $auditores));
    return $auditoria;
  }

  public function getAuditoriaByIdTarea($id, Tarea $tarea) {
      $daoAuditoria = $this->dao->getAuditoriaDAO();
      $daoPuntuacion = $this->dao->getPuntuacionDAO();
      $auditores = $this->getAllAuditores();
      $auditoria = $daoAuditoria->getById($id, $this->getAllAplicaciones(), $auditores, $this->getAllCatalogos());
      $this->completarCatalogo($auditoria->__get('catalogo'));
      $auditoria->addManyPuntuaciones($daoPuntuacion->getAllByAuditoriaTarea($auditoria, $auditores, $tarea));
      return $auditoria;
  }

  public function getAllGruposAuditores() {
    $daoGrupoAuditores = $this->dao->getGrupoAuditoresDAO();
    $daoAuditores = $this->dao->getAuditorDAO();
    return $daoGrupoAuditores->getAll($daoAuditores->getAll(), $this->getAllEtiquetas());
  }

  public function getGruposByUsuarioId($id) {
    $daoGrupoAuditores = $this->dao->getGrupoAuditoresDAO();
    $daoAuditores = $this->dao->getAuditorDAO();
    return $daoGrupoAuditores->getByUsuarioId($id, $daoAuditores->getAll(), $this->getAllEtiquetas());
  }

  public function getAllEtiquetas() {
    $daoEtiqueta = $this->dao->getEtiquetaDAO();
    return $daoEtiqueta->getAll();
  }

  public function getCatalogosByUsuarioConPermisos($id) {
    $escala = $this->getAllEscalasPuntuaciones();
    $auditores = $this->getAllAuditores();
    $gruposAuditores = $this->getAllGruposAuditores();
    $daoCatalogo = $this->dao->getCatalogoDAO();
    return $daoCatalogo->getByUsuarioIdConPermisos($id, $escala, $auditores, $gruposAuditores);
  }

  public function getCatalogosByUsuarioPropietario($id) {
    $escala = $this->getAllEscalasPuntuaciones();
    $auditores = $this->getAllAuditores();
    $gruposAuditores = $this->getAllGruposAuditores();
    $daoCatalogo = $this->dao->getCatalogoDAO();
    return $daoCatalogo->getByUsuarioIdPropietario($id, $escala, $auditores, $gruposAuditores);
  }

  public function cerrarAuditoria(Auditoria $auditoria) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $hoy = $daoAuditoria->cerrarAuditoria($auditoria->__get('id'));
    $auditoria->finalizar($hoy);
  }

  public function reabrirAuditoria(Auditoria $auditoria) {
    $daoAuditoria = $this->dao->getAuditoriaDAO();
    $daoAuditoria->reabrirAuditoria($auditoria->__get('id'));
    $auditoria->reabrir();
  }

  public function getAllFuentes() {
    $daoFuente = $this->dao->getFuenteDAO();
    return $daoFuente->getAll();
  }

  public function getAllCategorias() {
      $daoCategoria = $this->dao->getCategoriaWebDAO();
      return $daoCategoria->getAll();
  }

  public function getCategoriaById($id) {
      $daoCategoria = $this->dao->getCategoriaWebDAO();
      return $daoCategoria->getBy($id);
  }

  public function getAllTareas() {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getAll();
  }

  public function getTareaById($id) {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getBy($id);
  }

  public function getTareasByCategoria(CategoriaWeb $categoria) {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getAllByCategoria($categoria);
  }

  public function getPrimeraTarea(CategoriaWeb $categoria) {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getPrimeraByCategoria($categoria);
  }

  public function getNextTarea(Tarea $tarea) {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getNextTarea($tarea);
  }

  public function getPreviousTarea(Tarea $tarea) {
      $daoTarea = $this->dao->getTareaDAO();
      return $daoTarea->getPreviousTarea($tarea);
  }

  public function getTareasByAuditoria(Auditoria $auditoria) {
      return $this->getTareasByCategoria($auditoria->getCategoriaApp());
  }

  public function getPrioridadesByCatalogo(Catalogo $catalogo){
      $daoPrioridad = $this->dao->getPrioridadDAO();
      return $daoPrioridad->getAllByCatalogo($catalogo);
  }
}
