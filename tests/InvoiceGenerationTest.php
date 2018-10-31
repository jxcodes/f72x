<?php

namespace Tests;

use F72X\Repository;
use F72X\Sunat\Operations;
use F72X\Sunat\Catalogo;
use F72X\Sunat\DocumentGenerator;
use F72X\Sunat\DataMap;
use F72X\Sunat\InvoiceItems;

use PHPUnit\Framework\TestCase;

final class InvoiceGenerationTest extends TestCase {

    public function __construct() {
        Util::initF72X();
        $this->removeBillDocs();
        $this->generateDetailMatrix('boleta_caso1');
        $this->generateDetailMatrix('factura_caso1');
    }
    public function removeBillDocs() {
        Repository::removeBillDocs('20100454523-01-F001-00004355', false);
        Repository::removeBillDocs('20100454523-03-B001-00003652', false);
        Repository::removeBillDocs('20100454523-07-FC01-00000211', false);
    }
    public function testGenerateFactura() {
        $data = self::getCaseData('factura_caso1');
        $xmlInvice = DocumentGenerator::createInvoice('FACTURA',$data);
        DocumentGenerator::generateFiles($xmlInvice);
    }
    public function testGeneratBoleta() {
        $data = self::getCaseData('boleta_caso1');
        $xmlInvice = DocumentGenerator::createInvoice('BOLETA',$data);
        DocumentGenerator::generateFiles($xmlInvice);
    }
    public function testGenerateCreditNote() {
        $data = self::getCaseData('notacredito_caso1');
        $xmlDoc = DocumentGenerator::createCreditNote($data);
        DocumentGenerator::generateFiles($xmlDoc);
    }

    public function testDataMapRightCalcsForFactura() {
        $in = self::getCaseData('factura_caso1');
        $Invoice = new DataMap($in, 'B');
        $out = [
                'operationType'     => $Invoice->getOperationType(),
                'documentSeries'     => $Invoice->getDocumentSeries(),
                'documentNumber'     => (int)$Invoice->getDocumentNumber(),
                'customerDocType'   => $Invoice->getCustomerDocType(),
                'customerDocNumber' => $Invoice->getCustomerDocNumber(),
                'customerRegName'   => $Invoice->getCustomerRegName(),
                'customerAddress'     => $Invoice->getCustomerAddress(),
                'issueDate'         => $Invoice->getIssueDate()->format('Y-m-d\TH:i:s'),
                'purchaseOrder'     => $Invoice->getPurchaseOrder(),
                'allowancesCharges' => $Invoice->getAllowancesAndCharges(),
                'items'             => $Invoice->getRawItems(),
                'totalTaxes'        => (float)Operations::formatAmount($Invoice->getTotalTaxes()),
                'taxableAmount'     => (float)Operations::formatAmount($Invoice->getTaxableAmount()),
                'totalAllowances'   => (float)Operations::formatAmount($Invoice->getTotalAllowances()),
                'payableAmount'     => (float)Operations::formatAmount($Invoice->getPayableAmount())
        ];
        self::assertEquals($in, $out);
    }

    public function testDataMapRightCalcsForBoleta() {
        $in = self::getCaseData('boleta_caso1');
        $Invoice = new DataMap($in, 'B');
        $out = [
                'operationType'       => $Invoice->getOperationType(),
                'documentSeries'       => $Invoice->getDocumentSeries(),
                'documentNumber'       => (int)$Invoice->getDocumentNumber(),
                'customerDocType'     => $Invoice->getCustomerDocType(),
                'customerDocNumber'   => $Invoice->getCustomerDocNumber(),
                'customerRegName'     => $Invoice->getCustomerRegName(),
                'customerAddress'     => $Invoice->getCustomerAddress(),
                'issueDate'           => $Invoice->getIssueDate()->format('Y-m-d\TH:i:s'),
                'allowancesCharges'   => $Invoice->getAllowancesAndCharges(),
                'items'               => $Invoice->getRawItems(),
                'totalTaxes'          => (float)Operations::formatAmount($Invoice->getTotalTaxes()),
                'totalFreeOperations' => (float)Operations::formatAmount($Invoice->getTotalFreeOperations()),
                'taxableAmount'       => (float)Operations::formatAmount($Invoice->getTaxableAmount()),
                'totalAllowances'     => (float)Operations::formatAmount($Invoice->getTotalAllowances()),
                'payableAmount'       => (float)Operations::formatAmount($Invoice->getPayableAmount())
        ];
        self::assertEquals($in, $out);
    }

    public function generateDetailMatrix($caseName) {
        $data = self::getCaseData($caseName);
        $Items = new InvoiceItems();
        $Items->populate($data['items'], 'PEN');
        // Calculate totals
        $rows = $Items->countRows();
        $Items->set(InvoiceItems::COL_IGV,   $rows, $Items->sum(InvoiceItems::COL_IGV));
        $Items->set(InvoiceItems::COL_ITEM_BILLABLE_VALUE, $rows, $Items->sum(InvoiceItems::COL_ITEM_BILLABLE_VALUE));
        $Items->set(InvoiceItems::COL_ITEM_PAYABLE_AMOUNT, $rows, $Items->sum(InvoiceItems::COL_ITEM_PAYABLE_AMOUNT));
        $Items->set(InvoiceItems::COL_ITEM_TAXABLE_AMOUNT, $rows, $Items->sum(InvoiceItems::COL_ITEM_TAXABLE_AMOUNT));
        
        $html = $Items->getHtml();
        file_put_contents(__DIR__ . "/cases/$caseName.html", $html);
    }

    public function testGetCatItem() {
        $output = Catalogo::getCatItem(16, '01');
        $expected = [
            'id'    => '01',
            'value' => 'Precio unitario (incluye el IGV)'
        ];
        self::assertEquals($expected, $output);
    }

    public static function getCaseData($caseName) {
        return require "cases/$caseName.php";
    }
}
