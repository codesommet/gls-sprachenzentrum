<?php

namespace Tests\Unit\Services\Encaissement;

use App\Services\Encaissement\EncaissementNormalizer;
use PHPUnit\Framework\TestCase;

class EncaissementNormalizerTest extends TestCase
{
    private EncaissementNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new EncaissementNormalizer();
    }

    // ── Amount parsing ────────────────────────────────────────

    public function test_parse_amount_with_space_thousands(): void
    {
        $this->assertEquals(1300.0, $this->normalizer->parseAmount('1 300'));
    }

    public function test_parse_amount_simple(): void
    {
        $this->assertEquals(300.0, $this->normalizer->parseAmount('300'));
    }

    public function test_parse_amount_with_dh_suffix(): void
    {
        $this->assertEquals(1300.0, $this->normalizer->parseAmount('1300 Dh'));
    }

    public function test_parse_amount_with_decimals(): void
    {
        $this->assertEquals(1300.50, $this->normalizer->parseAmount('1300.50'));
    }

    public function test_parse_amount_european_comma(): void
    {
        $this->assertEquals(1300.50, $this->normalizer->parseAmount('1300,50'));
    }

    public function test_parse_amount_large_with_spaces(): void
    {
        $this->assertEquals(8100.0, $this->normalizer->parseAmount('8 100'));
    }

    // ── Payment method ────────────────────────────────────────

    public function test_old_payment_esp(): void
    {
        $this->assertEquals('especes', $this->normalizer->normalizeOldPaymentMethod('ESP'));
    }

    public function test_old_payment_cr(): void
    {
        $this->assertEquals('tpe', $this->normalizer->normalizeOldPaymentMethod('CR'));
    }

    public function test_old_payment_vr_with_ref(): void
    {
        $this->assertEquals('virement', $this->normalizer->normalizeOldPaymentMethod('VR N° 030803998'));
    }

    public function test_new_payment_especes(): void
    {
        $this->assertEquals('especes', $this->normalizer->normalizeNewPaymentMethod('Espèces'));
    }

    public function test_new_payment_tpe(): void
    {
        $this->assertEquals('tpe', $this->normalizer->normalizeNewPaymentMethod('TPE'));
    }

    public function test_new_payment_virement(): void
    {
        $this->assertEquals('virement', $this->normalizer->normalizeNewPaymentMethod('Virement bancaire'));
    }

    public function test_new_payment_cheque(): void
    {
        $this->assertEquals('cheque', $this->normalizer->normalizeNewPaymentMethod('Chèque'));
    }

    // ── Date parsing ──────────────────────────────────────────

    public function test_parse_french_date(): void
    {
        $this->assertEquals('2025-10-01', $this->normalizer->parseDate('01/10/2025'));
    }

    public function test_parse_iso_date(): void
    {
        $this->assertEquals('2025-10-01', $this->normalizer->parseDate('2025-10-01'));
    }

    // ── Old CRM observations parsing ──────────────────────────

    public function test_parse_frais_annuel_only(): void
    {
        $result = $this->normalizer->parseOldObservations('Frais annuel');
        $this->assertTrue($result['has_inscription']);
        $this->assertEmpty($result['months']);
    }

    public function test_parse_month_only(): void
    {
        $result = $this->normalizer->parseOldObservations('10');
        $this->assertFalse($result['has_inscription']);
        $this->assertEquals([10], $result['months']);
        $this->assertEquals('mensualite', $result['fee_type']);
    }

    public function test_parse_frais_annuel_with_month(): void
    {
        $result = $this->normalizer->parseOldObservations('Frais annuel, 10');
        $this->assertTrue($result['has_inscription']);
        $this->assertEquals([10], $result['months']);
    }

    public function test_parse_multiple_months(): void
    {
        $result = $this->normalizer->parseOldObservations('09, 10');
        $this->assertFalse($result['has_inscription']);
        $this->assertEquals([9, 10], $result['months']);
    }

    public function test_parse_frais_annuel_with_many_months(): void
    {
        $result = $this->normalizer->parseOldObservations('Frais annuel, 10, 11, 12, 01, 02, 03');
        $this->assertTrue($result['has_inscription']);
        $this->assertCount(6, $result['months']);
        $this->assertContains(10, $result['months']);
        $this->assertContains(1, $result['months']);
    }

    // ── New CRM frais parsing ─────────────────────────────────

    public function test_parse_new_frais_decembre(): void
    {
        $result = $this->normalizer->parseNewFrais('Frais de Décembre');
        $this->assertEquals('mensualite', $result['fee_type']);
        $this->assertEquals(12, $result['month_number']);
    }

    public function test_parse_new_frais_octobre(): void
    {
        $result = $this->normalizer->parseNewFrais("Frais d'Octobre");
        $this->assertEquals('mensualite', $result['fee_type']);
        $this->assertEquals(10, $result['month_number']);
    }

    public function test_parse_new_frais_inscription_a1(): void
    {
        $result = $this->normalizer->parseNewFrais("Frais d'inscription A1/A2/B1");
        $this->assertEquals('inscription_a1', $result['fee_type']);
        $this->assertNull($result['month_number']);
    }

    public function test_parse_new_frais_inscription_b2(): void
    {
        $result = $this->normalizer->parseNewFrais("Frais d'inscription B2");
        $this->assertEquals('inscription_b2', $result['fee_type']);
        $this->assertNull($result['month_number']);
    }

    // ── Month to date ─────────────────────────────────────────

    public function test_month_to_date_with_school_year_october(): void
    {
        $this->assertEquals('2025-10-01', $this->normalizer->monthToDate(10, '2025/2026'));
    }

    public function test_month_to_date_with_school_year_january(): void
    {
        $this->assertEquals('2026-01-01', $this->normalizer->monthToDate(1, '2025/2026'));
    }

    public function test_month_to_date_with_school_year_september(): void
    {
        $this->assertEquals('2025-09-01', $this->normalizer->monthToDate(9, '2025/2026'));
    }

    // ── Name cleaning ─────────────────────────────────────────

    public function test_clean_name(): void
    {
        $this->assertEquals('Douha El Hafdaoui', $this->normalizer->cleanName('DOUHA EL HAFDAOUI'));
    }

    public function test_clean_name_extra_spaces(): void
    {
        $this->assertEquals('Salma Ossaid', $this->normalizer->cleanName('  SALMA   OSSAID  '));
    }
}
