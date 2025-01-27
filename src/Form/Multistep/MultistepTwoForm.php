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
 * Contains \Drupal\usevalia\Form\Multistep\MultistepTwoForm.
 */
namespace Drupal\usevalia\Form\Multistep;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class MultistepTwoForm extends MultistepFormBase
{

    /**
     *
     * {@inheritdoc} .
     */
    public function getFormId()
    {
        return 'multistep_form_two';
    }

    /**
     *
     * {@inheritdoc} .
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildForm($form, $form_state);
        
        $form['age'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Your age'),
            '#default_value' => $this->store->get('age') ? $this->store->get('age') : ''
        );
        
        $form['location'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Your location'),
            '#default_value' => $this->store->get('location') ? $this->store->get('location') : ''
        );
        
        $form['actions']['previous'] = array(
            '#type' => 'link',
            '#title' => $this->t('Previous'),
            '#attributes' => array(
                'class' => array(
                    'button'
                )
            ),
            '#weight' => 0,
            '#url' => Url::fromRoute('usevalia.multistep_one')
        );
        
        return $form;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->store->set('age', $form_state->getValue('age'));
        $this->store->set('location', $form_state->getValue('location'));
        
        // Save the data
        parent::saveData();
        $form_state->setRedirect('usevalia');
    }
}
