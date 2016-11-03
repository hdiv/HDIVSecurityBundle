<?php

/*
 * This file is part of the HDIV bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HDIV\SecurityBundle\Form\Extension;

use HDIV\SecurityBundle\HDIVCore\HDIVConfig;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use HDIV\SecurityBundle\HDIVCore\DataComposer\DataComposerMemory;

/**
 * Class HDIVTypeExtension
 * @package HDIV\SecurityBundle\Form\Extension
 */
class HDIVTypeExtension extends AbstractTypeExtension
{
    protected static $typeNames = array('text', 'textarea', 'search', 'email', 'password');

    protected $dataComposerMemory;
    protected $HDIVConfig;
    protected $logger;

    public function __construct(DataComposerMemory $dataComposerMemory, HDIVConfig $HDIVConfig, $logger)
    {
        $this->dataComposerMemory = $dataComposerMemory;
        $this->HDIVConfig = $HDIVConfig;
        $this->logger = $logger;
    }

     public function buildForm(FormBuilderInterface $builder, array $options)
     {

        $HDIVConfig = $this->HDIVConfig;

        if (!$this->HDIVConfig->isHdivEnabled()) {
            return;
        }

        if ($options['compound']) {

            //Creates _HDIV_STATE_ hidden field to the form
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($builder, $HDIVConfig) {

                $form = $event->getForm();
                $this->HDIVConfig;
                if ($form->isRoot()) {
                    $factory = $builder->getFormFactory();
                    $field = $factory->createNamed($HDIVConfig->getHdivStateName(), 'hidden', '', array('auto_initialize' => false, 'mapped'=>false));
                    $form->add($field);
                }
            });


            // Rules validation
            if ($this->HDIVConfig->isEditableValidationEnabled() == "true") {

                    $logger = $this->logger;

                    //Checks text, textarea, search, email and password fields inputs
                    $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($logger, $HDIVConfig) {
        
                    // Gets Symfony's form errors
                    $errors =  $this->getErrorMessages($event->getForm());
        
                    // Log Errors
                    foreach($errors as $key => $value) {
                        $logger->log('INVALID_PARAMETER_VALUE', null, $_SERVER['REMOTE_ADDR'], $key, $value, null, null);        
                    }

                    //Get actionForm
                    $formAction = $this->removeParam($event->getForm()->getConfig()->getAction(), $HDIVConfig->getHdivStateName());

                    $editableValidationUrls = array_keys($this->HDIVConfig->getEditableValidations());

                    //Get matched validation key
                    $editableValidationMatchedKey = $this->getMatchedUrl($editableValidationUrls, $formAction);

                    //There is no rule validation for this action
                    if (strlen($editableValidationMatchedKey) <= 0) {
                        return;
                    }

                    // For each parameter
                    foreach($event->getForm() as $key => $field) {

                        // If is an text, textarea...
                        if (in_array($field->getConfig()->getType()->getName(), HDIVTypeExtension::$typeNames)) {

                            // The field has no value
                            if (!$field->getData()) {
                                return;
                            }

                            $rules = $this->HDIVConfig->getEditableValidations()[$editableValidationMatchedKey];
                            $this->checkRulesField($rules, $field, $logger);
                        }
                    }
                });
            }
        }
    }


    /**
     * Get matched editableValidation key
     *
     * @param $editableValidationUrls
     * @param $formAction
     */
    private function getMatchedUrl($editableValidationUrls, $formAction) {

        $editableValidationMatchedKey = "";
        foreach($editableValidationUrls as $editableValidation) {
            if (preg_match($editableValidation, $formAction)) {
                $editableValidationMatchedKey = $editableValidation;
                return $editableValidationMatchedKey;
            }
        }
    }

    /**
     * Get errorMessages
     *
     * @param $form
     * @param $errors array
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                /**
                 * @var \Symfony\Component\Form\Form $child
                 */
                if (!$child->isValid()) {

                    $errors[$child->getName()] = $child->getConfig()->getType()->getName();
                }
            }
        } else {
            /**
             * @var \Symfony\Component\Form\FormError $error
             */
            foreach ($form->getErrors() as $key => $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Checks if a rule match with the field value
     *
     * @param $rules
     * @param $field
     */
    private function checkRulesField($rules, $field, $logger) {
        foreach ($rules as $nameRule => $rule){

            $acceptedPattern = $rule->getAcceptedPattern();

            if ($acceptedPattern != NULL && strlen($acceptedPattern) > 0) {
                if (!preg_match($acceptedPattern, $field->getData())) {

                    $field->addError(new FormError('HDIV Validation.'.$rule->getName().'. Invalid Characters.'));
                    $logger->log('INVALID_EDITABLE_VALUE', null, $_SERVER['REMOTE_ADDR'], $field->getName(), null, $field->getData(), $rule->getName());
                    return;
                }
            }
            $rejectedPattern = $rule->getRejectedPattern();

            if ($rejectedPattern != NULL && strlen($rejectedPattern) > 0) {
                if (preg_match($rejectedPattern, $field->getData())) {

                    $field->addError(new FormError('HDIV Validation.'.$rule->getName().'. Invalid Characters.'));
                    $logger->log('INVALID_EDITABLE_VALUE', null, $_SERVER['REMOTE_ADDR'], $field->getName(), null, $field->getData(), $rule->getName());
                    return;
                }
            }
        }
    }

    /**
     * Checks if <code>$in</code> is a non malicious string with ModSecurity rules
     * @param $in
     * @return matched rule, otherwise false
     */
    private function editableRuleValidation($fieldValue, HDIVConfig $HDIVConfig){

        $defaultBlackListRules = $HDIVConfig->getDefaultRules();

        foreach ($defaultBlackListRules as $key=>$rule) {
            if (preg_match($rule, $fieldValue)) {
                return $key;
            }
        }

        return false;
    }


    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        
        if (!$this->HDIVConfig->isHdivEnabled()) {
            return;
        }

        if ($options['compound']) {

            //Store all form fields and set the _HDIV_STATE_ value to the hidden field.
            $action = $this->removeParam($form->getConfig()->getAction(), $this->HDIVConfig->getHdivStateName());
            $view->vars['action'] = $action;
            $token = $this->dataComposerMemory->addFormToCurrentPage($form, $action);

            if ($form->isRoot()) {
                $view->children[$this->HDIVConfig->getHdivStateName()]->vars['value'] = $token;
            }

           // $this->toString($form);
         }
    }


    public function getExtendedType()
    {
        return 'form';
    }

    public function toString(FormInterface $form)
    {
        $children = $form->all();

        print("<br/>FORM CHILDREN<br/>");
        print("<br/> FORM TYPE: ".$form->getConfig()->getType()->getName()."<br/>");
        print("<br/> FORM NAME: ".$form->getName()."<br/>");
        print("<br/>");

        foreach ($children as $ch) {
            print("Name: ".$ch->getName() . "<br/>");
            print("Type: ".$ch->getConfig()->getType()->getName() . "<br/>");
            print("<br/>");
            if ($ch->all()) {
                $this->toString($ch);
            }
        }
        print(" FORM END: ".$form->getName()."<br/>");
        print("<br/>");
        print("<br/>");
    }


    /**
     * Removes a param from a given url
     *
     * @param $url
     * @param $param
     * @return mixed
     */
    function removeParam($url, $param) {
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*$/', '', $url);
        $url = preg_replace('/(&|\?)'.preg_quote($param).'=[^&]*&/', '$1', $url);
        return $url;
    }

}