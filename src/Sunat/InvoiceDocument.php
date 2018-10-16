<?php

/**
 * MÓDULO DE EMISIÓN ELECTRÓNICA F72X
 * UBL 2.1
 * Version 1.1
 * 
 * Copyright 2018, Jaime Cruz
 */

namespace F72X\Sunat;

use DateTime;
use F72X\Company;
/**
 * InvoiceDocument
 * 
 * Esta clase es una representación interna de una factura o boleta, la cual se
 * encarga de aplicar toda la logica de negocio en cuanto a cálculos de tributos
 * y totales
 * 
 */
class InvoiceDocument {

    const BOLETA_PREFIX = 'B';
    const BOLETA_NAME = 'BOLETA';
    const FACTURA_PREFIX = 'F';
    const FACTURA_NAME = 'FACTURA';

    private $_rawData;
    private $invoiceType;
    private $currencyType;
    private $voucherId;
    private $voucherIdPrefix;
    private $voucherName;
    private $voucherSeries;
    private $voucherNumber;

    /** @var DateTime */
    private $issueDate;

    /** @var DateTime */
    protected $DueDate;
    private $operationType;
    private $customerDocType;
    private $customerDocNumber;
    private $customerRegName;
    private $customerAddress;
    private $purchaseOrder;

    /** @var InvoiceItems */
    private $_items;
    private $_rawItems;
    private $allowancesAndCharges = [];

    /**
     * 
     * @param array $data
     * @param string $type Catalogo::CAT1_BOLETA|Catalogo::CAT1_FACTURA
     * @param string $currencyType
     */
    public function __construct(array $data, $type, $currencyType = 'PEN') {
        // Items
        $items = new InvoiceItems();
        $items->populate($data['items'], $currencyType);

        $this->_rawData = $data;
        $this->_rawItems = $data['items'];
        $this->addDefaults($data);
        $this->currencyType = $currencyType;
        $this->voucherSeries = $data['voucherSeries'];
        $this->voucherNumber = str_pad($data['voucherNumber'], 8, '0', STR_PAD_LEFT);
        // requires voucherSeries and voucherNumber
        $this->setTypeProperties($type);
        $this->issueDate = $data['issueDate'];
        $this->purchaseOrder = $data['purchaseOrder'];
        $this->operationType = $data['operationType'];
        $this->customerDocType = $data['customerDocType'];
        $this->customerDocNumber = $data['customerDocNumber'];
        $this->customerRegName = mb_strtoupper($data['customerRegName']);
        $this->customerAddress = mb_strtoupper($data['customerAddress']);
        $this->_items = $items;
        $this->allowancesAndCharges = $data['allowancesCharges'];
    }

    private function addDefaults(array &$data) {
        $data['allowancesCharges'] = isset($data['allowancesCharges']) ? $data['allowancesCharges'] : [];
        $data['issueDate'] = isset($data['issueDate']) ? $data['issueDate'] : new DateTime();
        $data['purchaseOrder'] = isset($data['purchaseOrder']) ? $data['purchaseOrder'] : null;
    }

    private function setTypeProperties($type) {
        $this->invoiceType = $type;
        if ($type === Catalogo::CAT1_BOLETA) {
            $this->voucherIdPrefix = self::BOLETA_PREFIX;
            $this->voucherName = self::BOLETA_NAME;
        } else {
            $this->voucherIdPrefix = self::FACTURA_PREFIX;
            $this->voucherName = self::FACTURA_NAME;
        }
        $this->voucherId = $this->voucherSeries . '-' . $this->voucherNumber;
    }

    public function getVoucherId() {
        return $this->voucherId;
    }

    public function getRawData() {
        return $this->_rawData;
    }

    /**
     * Boleta o Factura @CAT1
     * @return string
     */
    public function getInvoiceType() {
        return $this->invoiceType;
    }

    public function getCurrencyType() {
        return $this->currencyType;
    }

    public function getVoucherIdPrefix() {
        return $this->voucherIdPrefix;
    }

    public function getVoucherName() {
        return $this->voucherName;
    }

    public function getVoucherSeries() {
        return $this->voucherSeries;
    }

    public function setVoucherSeries($voucherSeries) {
        $this->voucherSeries = $voucherSeries;
        return $this;
    }

    public function getVoucherNumber() {
        return $this->voucherNumber;
    }

    public function setVoucherNumber($voucherNumber) {
        $this->voucherNumber = $voucherNumber;
        return $this;
    }

    public function getIssueDate() {
        return $this->issueDate;
    }

    public function setIssueDate(DateTime $IssueDate) {
        $this->issueDate = $IssueDate;
        return $this;
    }

    public function getDueDate() {
        return $this->DueDate;
    }

    public function setDueDate(DateTime $DueDate) {
        $this->DueDate = $DueDate;
        return $this;
    }

    public function getOperationType() {
        return $this->operationType;
    }

    public function setOperationType($operationType) {
        $this->operationType = $operationType;
        return $this;
    }

    public function getCustomerDocType() {
        return $this->customerDocType;
    }

    public function setCustomerDocType($customerDocType) {
        $this->customerDocType = $customerDocType;
        return $this;
    }

    public function getCustomerDocNumber() {
        return $this->customerDocNumber;
    }

    public function setCustomerDocNumber($customerDocNumber) {
        $this->customerDocNumber = $customerDocNumber;
        return $this;
    }

    public function getCustomerRegName() {
        return $this->customerRegName;
    }

    public function setCustomerRegName($customerRegName) {
        $this->customerRegName = $customerRegName;
        return $this;
    }
    public function getCustomerAddress() {
        return $this->customerAddress;
    }

    public function setCustomerAddress($customerAddress) {
        $this->customerAddress = $customerAddress;
        return $this;
    }

    public function getPurchaseOrder() {
        return $this->purchaseOrder;
    }

    public function setPurchaseOrder($purchaseOrder) {
        $this->purchaseOrder = $purchaseOrder;
        return $this;
    }

    /**
     * 
     * @return InvoiceItems
     */
    public function getItems() {
        return $this->_items;
    }

    /**
     * Numero de items del documento
     * @return int
     */
    public function getTotalItems() {
        return $this->_items->getCount();
    }

    public function getRawItems() {
        return $this->_rawItems;
    }

    public function getAllowancesAndCharges() {
        return $this->allowancesAndCharges;
    }

    public function setAllowancesAndCharges($allowancesAndCharges) {
        $this->allowancesAndCharges = $allowancesAndCharges;
        return $this;
    }

    /**
     * Base imponible
     * 
     * Formula = SUM(BASE_IMPONIBLE_X_ITEM) - DESCUENTOS_GLOBALES + CARGOS_GLOBALES
     * 
     * @return float
     */
    public function getTaxableAmount() {
        $totalItems = $this->_items->getTotalTaxableOperations();
        return $this->applyAllowancesAndCharges($totalItems);
    }

    /**
     * Monto con impuestos
     * 
     * Formula = BASE_IMPONIBLE + IGV
     * 
     * @return float
     */
    public function getTaxedAmount() {
        $totalTaxableAmount = $this->getTaxableAmount();
        $totalIgv = $this->getIGV();
        return $totalTaxableAmount + $totalIgv;
    }

    /**
     * Total operaciones gravadas
     * @return float
     */
    public function getTotalTaxableOperations() {
        return $this->getTaxableAmount();
    }

    /**
     * Total operaciones gratuitas
     * @return float
     */
    public function getTotalFreeOperations() {
        return $this->_items->getTotalFreeOperations();
    }

    /**
     * Total operationes exoneradas
     * 
     * Formula = SUM(EXEMPTED_OPERATIONS_X_ITEM) - DESCUENTOS_GLOBALES + CARGOS_GLOBALES
     * 
     * @return float
     */
    public function getTotalExemptedOperations() {
        $totalItems = $this->_items->getTotalExemptedOperations();
        return $this->applyAllowancesAndCharges($totalItems);
    }

    /**
     * Total operaciones inafectas
     * @return float
     */
    public function getTotalUnaffectedOperations() {
        $totalItems = $this->_items->getTotalUnaffectedOperations();
        return $this->applyAllowancesAndCharges($totalItems);
    }

    /**
     * Valor de venta
     * 
     * Valor total de la factura sin considerar descuentos impuestos u otros tributos
     * @return float
     */
    public function getBillableValue() {
        return $this->_items->getTotalBillableValue();
    }

    /**
     * Total descuentos
     * 
     * Formula: SUM(DESCUENTOS_X_ITEM) + DESCUENTOS_GLOBALES
     * @return float
     */
    public function getTotalAllowances() {
        $totalItems = $this->_items->getTotalAllowances();
        $totalTaxableAmountItems = $this->_items->getTotalTaxableAmount();
        $globalAllowancesAmount = Operations::getTotalAllowances($totalTaxableAmountItems, $this->allowancesAndCharges);
        return $totalItems + $globalAllowancesAmount;
    }

    /**
     * Total a pagar
     * 
     * El importe que el usuario está obligado a pagar
     * 
     * Formula = OPERACIONES_GRAVADAS + IGV + OPERACIONES_EXONERADAS + OPERACIONES_INAFECTAS
     * 
     * @return float
     */
    public function getPayableAmount() {
        // Totals
        $totalTaxableOperations = $this->getTotalTaxableOperations();
        $totalIGV = $this->getIGV();
        $totalExemptedOperations = $this->getTotalExemptedOperations();
        return $totalTaxableOperations + $totalIGV + $totalExemptedOperations;
    }

    /**
     * 
     * Total impuestos
     * 
     * Fórmula: IGV + ISC + IVAP
     * 
     * @return float
     */
    public function getTotalTaxes() {
        $IGV = $this->getIGV();
        $ISC = $this->getISC();
        $IVAP = $this->getIVAP();
        return $IGV + $ISC + $IVAP;
    }

    /**
     * IGV
     * @return float
     */
    public function getIGV() {
        $baseAmount = $this->getTaxableAmount();
        return Operations::calcIGV($baseAmount);
    }

    /**
     * ISC
     * @IMP
     * @return float
     */
    public function getISC() {
        return Operations::calcISC();
    }

    /**
     * IVAP
     * @IMP
     * @return float
     */
    public function getIVAP() {
        return Operations::calcIVAP();
    }

    /**
     * 
     * @param float $amount
     * @return float
     */
    private function applyAllowancesAndCharges($amount) {
        return Operations::applyAllowancesAndCharges($amount, $this->allowancesAndCharges);
    }
    public function getBillName() {
        return Company::getRUC() . '-' . $this->invoiceType . '-' . $this->voucherId;
    }
}
