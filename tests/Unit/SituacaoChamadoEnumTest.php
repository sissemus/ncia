<?php

namespace Tests\Unit;

use App\MyLibs\SituacaoChamadoEnum;
use PHPUnit\Framework\TestCase;

class SituacaoChamadoEnumTest extends TestCase
{
    public function testValuesReturnsEveryStatusIdInDomainOrder()
    {
        $this->assertSame([
            SituacaoChamadoEnum::ABERTO,
            SituacaoChamadoEnum::EM_ANALISE,
            SituacaoChamadoEnum::EM_ATENDIMENTO,
            SituacaoChamadoEnum::CONCLUIDO,
            SituacaoChamadoEnum::CANCELADO,
        ], SituacaoChamadoEnum::values());
    }

    public function testStatusIdsAreStableAndUnique()
    {
        $this->assertSame(1, SituacaoChamadoEnum::ABERTO);
        $this->assertSame(2, SituacaoChamadoEnum::EM_ANALISE);
        $this->assertSame(3, SituacaoChamadoEnum::EM_ATENDIMENTO);
        $this->assertSame(4, SituacaoChamadoEnum::CONCLUIDO);
        $this->assertSame(5, SituacaoChamadoEnum::CANCELADO);
        $this->assertCount(
            count(SituacaoChamadoEnum::values()),
            array_unique(SituacaoChamadoEnum::values())
        );
    }
}
