<?php

/**
 * MÓDULO DE EMISIÓN ELECTRÓNICA F72X
 * UBL 2.1
 * Version 1.1
 * 
 * Copyright 2018, Jaime Cruz
 */

namespace F72X\UblComponent;

use Sabre\Xml\Writer;

class BillingReference extends BaseComponent {

    /** @var InvoiceDocumentReference */
    protected $InvoiceDocumentReference;

    function xmlSerialize(Writer $writer) {
        $writer->write([
            SchemaNS::CAC . 'InvoiceDocumentReference' => $this->InvoiceDocumentReference
        ]);
    }

    public function getInvoiceDocumentReference() {
        return $this->InvoiceDocumentReference;
    }

    public function setInvoiceDocumentReference(InvoiceDocumentReference $InvoiceDocumentReference) {
        $this->InvoiceDocumentReference = $InvoiceDocumentReference;
        return $this;
    }

}