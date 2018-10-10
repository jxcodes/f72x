<?php

/**
 * MÓDULO DE EMISIÓN ELECTRÓNICA F72X
 * UBL 2.1
 * Version 1.1
 * 
 * Copyright 2018, Jaime Cruz
 */

namespace F72X\Sunat;

use F72X\Sunat\Catalogo;
use F72X\Tools\Validations;

class InputValidator {

    private $data;
    private $type;
    private static $validations = [
        'operationType' => [
            'required' => true,
            'inCat' => Catalogo::CAT_FACTURA_TYPE
        ],
        'voucherSeries' => [
            'required' => true
        ],
        'voucherNumber' => [
            'required' => true
        ],
        'customerDocType' => [
            'required' => true,
            'inCat' => Catalogo::CAT_IDENT_DOCUMENT_TYPE
        ],
        'customerDocNumber' => [
            'required' => true
        ],
        'customerRegName' => [
            'required' => true
        ],
        'items' => [
            'required' => true,
            'type' => 'Array'
        ]
    ];
    private $errors = [];

    public function __construct(array $data, $type) {
        $this->data = $data;
        $this->type = $type;
        $this->validate();
    }

    public function isValid() {
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    private function validate() {

        foreach (self::$validations as $field => $item) {
            $defauls = [
                'required' => false,
                'type' => null,
                'incat' => null
            ];
            $validation = array_merge($defauls, $item);
            if ($field == 'customerDocNumber') {
                $validation['type'] = $this->getDocTypeValidator();
            }
            $this->validateItem($field, $validation, $this->data);
        }
    }

    private function validateItem($field, $validation, $data) {
        $fieldExist = isset($data[$field]);
        $fieldValue = $fieldExist ? $data[$field] : null;

        $required = $validation['required'];
        $catNumber = $validation['incat'];
        $type = $validation['type'];
        // Required
        if ($required && !$fieldExist) {
            $this->errors = "$field es requerido.";
        }
        if (!$fieldExist) {
            return;
        }
        // Data type
        if ($type && !Validations::{'is' . $type}($fieldValue)) {
            $this->errors = $this->getTypeErrorValidationMessage($field, $fieldValue, $type);
        }
        // In catalog
        if ($catNumber && !Catalogo::itemExist($catNumber, $fieldValue)) {
            $this->errors = "El valor $fieldValue en el campo $field no existe en el Cátalogo N° $catNumber.";
        }
    }

    private function getTypeErrorValidationMessage($field, $value, $type) {
        switch ($type) {
            case 'Array':
                return $field == 'items' ?
                        'El campo items debe ser de tipo array.' :
                        "Se espera que el campo $field sea un array.";
            case 'Dni':
                return "$value no es un DNI valido.";
            case 'Ruc':
                return "$value no es un DUC valido.";
            default:
                break;
        }
    }

    private function getDocTypeValidator() {
        $doctypeExist = isset($this->data['customerDocType']);
        $doctypeValue = $doctypeExist ? $this->data['customerDocType'] : null;
        if ($doctypeExist && Catalogo::itemExist(Catalogo::CAT_IDENT_DOCUMENT_TYPE, $doctypeValue)) {
            //@CAT6
            switch ($doctypeValue) {
                case '1': return 'Dni';
                case '6': return 'Ruc';
                case '0': 
                case '7':
                case 'A':
                case 'B':
                case 'C':
                case 'D':
                case 'E': return null;
            }
        }
        return null;
    }

}
