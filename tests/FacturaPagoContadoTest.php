<?php

namespace Tests;

final class FacturaPagoContadoTest extends FacturaTestCase
{

    protected function getDocumentData(): array
    {
        return require __DIR__ . '/cases/facturas/factura-pago-contado.php';
    }

    public function testGenerarFactura()
    {
        $this->expectNotToPerformAssertions();
        $this->generarFactura();
    }

    public function testSendToSunat()
    {
        $this->sendToSunat();
    }
}
