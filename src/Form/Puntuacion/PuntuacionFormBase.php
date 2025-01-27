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
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of PuntuacionFormBase
 *
 * @author luis
 */
abstract class PuntuacionFormBase extends FormBase {

  /**
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  private $sessionManager;

  /**
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * Constructs a \Drupal\demo\Form\Multistep\MultistepFormBase.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;
    $this->store = $this->tempStoreFactory->get('multistep_data');
  }

  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('user.private_tempstore'), $container->get('session_manager'), $container->get('current_user'));
  }

  /**
   *
   * {@inheritdoc} .
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Start a manual session for anonymous users.
    if ($this->currentUser->isAnonymous()) {
      $form_state->setRedirect('usevalia');
    }

    $form = [];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enviar'),
      '#button_type' => 'primary',
      '#weight' => 10
    ];

    return $form;
  }

  /**
   * Saves the data from the multistep form.
   */
  protected function saveData() {
    // Borrado
    $this->deleteStore();
    $messenger = Drupal::messenger();
    $messenger->addMessage($this->t('La puntuación se ha guardado correctamente.'));
  }

  /**
   * Helper method that removes all the keys from the store collection used for
   * the multistep form.
   */
  protected function deleteStore() {
    $keys = [
      'catalogo'
    ];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }

}
