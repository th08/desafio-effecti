<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Validators\DocumentValidator;

/**
 * Testes unitários para validação de CPF e CNPJ
 */
class DocumentValidatorTest extends TestCase
{
    // ========================
    // CPF Válidos
    // ========================

    public function testValidCpf(): void
    {
        // CPF válido gerado por cálculo
        $this->assertTrue(DocumentValidator::validate('52998224725'));
    }

    public function testValidCpfWithFormatting(): void
    {
        $this->assertTrue(DocumentValidator::validate('529.982.247-25'));
    }

    public function testValidCpfSanitize(): void
    {
        $this->assertEquals('52998224725', DocumentValidator::sanitize('529.982.247-25'));
    }

    public function testCpfGetType(): void
    {
        $this->assertEquals('CPF', DocumentValidator::getType('52998224725'));
    }

    public function testCpfFormat(): void
    {
        $formatted = DocumentValidator::format('52998224725');
        $this->assertEquals('529.982.247-25', $formatted);
    }

    // ========================
    // CPF Inválidos
    // ========================

    public function testInvalidCpfAllSameDigits(): void
    {
        // Todos os dígitos iguais devem ser inválidos
        $this->assertFalse(DocumentValidator::validate('11111111111'));
        $this->assertFalse(DocumentValidator::validate('00000000000'));
        $this->assertFalse(DocumentValidator::validate('99999999999'));
    }

    public function testInvalidCpfWrongCheckDigit(): void
    {
        $this->assertFalse(DocumentValidator::validate('52998224726'));
    }

    public function testInvalidCpfWrongLength(): void
    {
        $this->assertFalse(DocumentValidator::validate('1234'));
        $this->assertFalse(DocumentValidator::validate('123456789012'));
    }

    public function testInvalidCpfEmpty(): void
    {
        $this->assertFalse(DocumentValidator::validate(''));
    }

    // ========================
    // CNPJ Válidos
    // ========================

    public function testValidCnpj(): void
    {
        $this->assertTrue(DocumentValidator::validate('11222333000181'));
    }

    public function testValidCnpjWithFormatting(): void
    {
        $this->assertTrue(DocumentValidator::validate('11.222.333/0001-81'));
    }

    public function testCnpjGetType(): void
    {
        $this->assertEquals('CNPJ', DocumentValidator::getType('11222333000181'));
    }

    public function testCnpjFormat(): void
    {
        $formatted = DocumentValidator::format('11222333000181');
        $this->assertEquals('11.222.333/0001-81', $formatted);
    }

    // ========================
    // CNPJ Inválidos
    // ========================

    public function testInvalidCnpjAllSameDigits(): void
    {
        $this->assertFalse(DocumentValidator::validate('11111111111111'));
        $this->assertFalse(DocumentValidator::validate('00000000000000'));
    }

    public function testInvalidCnpjWrongCheckDigit(): void
    {
        $this->assertFalse(DocumentValidator::validate('11222333000182'));
    }

    public function testInvalidCnpjWrongLength(): void
    {
        $this->assertFalse(DocumentValidator::validate('1122233300018'));
    }
}
