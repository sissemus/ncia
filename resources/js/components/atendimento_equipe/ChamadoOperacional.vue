<template>
    <v-card outlined class="mb-3">
        <v-card-title class="py-2 subtitle-2 blue-grey lighten-5">
            <span class="font-weight-bold">Chamado Nº {{ chamado.CHAMADO_ID }}</span>
            <v-chip x-small dark class="ml-3" :color="corPrioridade">{{ prioridade }}</v-chip>
            <v-chip x-small dark class="ml-2" :color="corSituacao">{{ situacao }}</v-chip>
            <v-spacer></v-spacer>
            <v-btn color="error" outlined small tile class="mr-2"
                :disabled="processandoId !== null" @click="$emit('cancelar', chamado)">
                <v-icon small left>mdi-cancel</v-icon>
                Cancelar atendimento
            </v-btn>
            <v-btn color="primary" small tile :loading="processandoId === chamado.CHAMADO_ID"
                :disabled="processandoId !== null || !podeAvancar" @click="$emit('avancar', chamado)">
                <v-icon small left>mdi-arrow-right-bold-circle-outline</v-icon>
                {{ chamado.PROXIMA_ETAPA_DESCRICAO }}
            </v-btn>
        </v-card-title>
        <v-card-text class="pt-3">
            <v-row dense>
                <v-col cols="12" md="4"><strong>Paciente:</strong> {{ paciente }}</v-col>
                <v-col cols="12" md="4"><strong>Origem:</strong> {{ unidade(chamado.unidadeSolicitante) }}</v-col>
                <v-col cols="12" md="4"><strong>Destino:</strong> {{ unidade(chamado.unidadeDestino) }}</v-col>
                <v-col cols="12" md="4"><strong>Equipe:</strong> Nº {{ chamado.EQUIPE_FILA_ID }}</v-col>
                <v-col cols="12" md="4"><strong>Etapa atual:</strong> {{ chamado.ETAPA_ATUAL_DESCRICAO }}</v-col>
                <v-col cols="12" md="4"><strong>Abertura:</strong> {{ formatarData(chamado.CHAMADO_DATA) }}</v-col>
            </v-row>
            <div v-if="chamado.etapasAtendimento && chamado.etapasAtendimento.length" class="mt-3">
                <v-chip v-for="etapa in chamado.etapasAtendimento" :key="etapa.CHAMADO_ATENDIMENTO_ETAPA_ID"
                    small outlined color="primary" class="mr-2 mb-1">
                    {{ etapa.ATENDIMENTO_ETAPA_DESCRICAO }} — {{ formatarData(etapa.ATENDIMENTO_ETAPA_DATA) }}
                </v-chip>
            </div>
            <v-alert v-if="!podeAvancar" dense text type="info" class="mt-3 mb-0">
                Aguardando a conclusão do atendimento atual ou dos chamados anteriores da fila.
            </v-alert>
        </v-card-text>
    </v-card>
</template>

<script>
import { getPrioridadeColor } from "../../enums/PrioridadePacienteEnum";
import SituacaoChamadoEnum, { getSituacaoChamadoColor } from "../../enums/SituacaoChamadoEnum";

export default {
    name: "ChamadoOperacional",
    props: {
        chamado: { type: Object, required: true },
        prioridades: { type: Array, default: () => [] },
        processandoId: { default: null },
        podeAvancar: { type: Boolean, default: false },
    },
    computed: {
        paciente() {
            const paciente = this.chamado.paciente || {};
            return paciente.PACIENTE_NOME || (Number(paciente.PACIENTE_VULNERABILIDADE_SOCIAL) === 1
                ? "PACIENTE EM VULNERABILIDADE SOCIAL" : "-");
        },
        prioridade() {
            const item = this.prioridades.find(p => Number(p.COLUNA_ID) === Number(this.chamado.TG_PRIORIDADE_ID));
            return item ? item.DESCRICAO : "-";
        },
        corPrioridade() { return getPrioridadeColor(this.chamado.TG_PRIORIDADE_ID); },
        situacao() {
            return this.chamado.situacaoAtual
                && Number(this.chamado.situacaoAtual.TG_SITUACAO_ID) === SituacaoChamadoEnum.EM_FILA
                ? "EM FILA" : "EM ATENDIMENTO";
        },
        corSituacao() {
            const id = this.chamado.situacaoAtual && this.chamado.situacaoAtual.TG_SITUACAO_ID;
            return getSituacaoChamadoColor(id);
        },
    },
    methods: {
        unidade(item) { return item && item.UNIDADE_NOME ? item.UNIDADE_NOME : "-"; },
        formatarData(valor) {
            if (!valor) return "-";
            const data = new Date(String(valor).replace(" ", "T"));
            return Number.isNaN(data.getTime()) ? valor : data.toLocaleString("pt-BR");
        },
    },
};
</script>
