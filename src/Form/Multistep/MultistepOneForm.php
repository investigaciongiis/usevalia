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

/**
 * @file
 * Contains \Drupal\usevalia\Form\Multistep\MultistepOneForm.
 */
namespace Drupal\usevalia\Form\Multistep;

use Drupal\Core\Form\FormStateInterface;

class MultistepOneForm extends MultistepFormBase
{

    /**
     *
     * {@inheritdoc} .
     */
    public function getFormId()
    {
        return 'multistep_form_one';
    }

    /**
     *
     * {@inheritdoc} .
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildForm($form, $form_state);
        
        $form['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Your name'),
            '#default_value' => $this->store->get('name') ? $this->store->get('name') : ''
        );
        
        $form['email'] = array(
            '#type' => 'email',
            '#title' => $this->t('Your email address'),
            '#default_value' => $this->store->get('email') ? $this->store->get('email') : ''
        );
        
        $form['actions']['submit']['#value'] = $this->t('Next');
        return $form;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->store->set('email', $form_state->getValue('email'));
        $this->store->set('name', $form_state->getValue('name'));
        $form_state->setRedirect('usevalia.multistep_two');
    }
}
