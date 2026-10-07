<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense class="elevation-1">
                <v-icon color="primary" class="mr-2">mdi-ambulance</v-icon>
                <v-toolbar-title class="font-weight-bold">Atendimento da Equipe</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon color="primary" title="Atualizar fila" :disabled="processandoId !== null" @click="carregar">
                    <v-icon>mdi-refresh</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <v-progress-linear v-if="carregando" indeterminate color="primary"></v-progress-linear>

            <v-card-text>
                <v-alert v-if="!carregando && !possuiVinculoProfissional" type="warning" outlined>
                    Seu usuário não está vinculado a uma equipe assistencial ativa. Confirme se o CPF do usuário é o mesmo do cadastro do profissional.
                </v-alert>

                <v-alert v-else-if="!carregando && !chamados.length" type="info" outlined>
                    Não há chamados em fila ou em atendimento vinculados à sua equipe.
                </v-alert>

                <template v-if="chamados.length">
                    <div class="subtitle-1 font-weight-bold mb-2">Atendimento atual</div>
                    <v-alert v-if="!atendimentos.length" type="info" dense outlined>
                        Nenhum chamado está em atendimento no momento.
                    </v-alert>
                    <chamado-operacional v-for="chamado in atendimentos" :key="`atendimento-${chamado.CHAMADO_ID}`"
                        :chamado="chamado" :prioridades="prioridades" :processando-id="processandoId"
                        :pode-avancar="true" @avancar="confirmarAvanco" />

                    <v-divider class="my-5"></v-divider>
                    <div class="subtitle-1 font-weight-bold mb-2">Chamados em fila</div>
                    <v-alert v-if="!fila.length" type="info" dense outlined>
                        Nenhum chamado aguardando recebimento.
                    </v-alert>
                    <chamado-operacional v-for="chamado in fila" :key="`fila-${chamado.CHAMADO_ID}`"
                        :chamado="chamado" :prioridades="prioridades" :processando-id="processandoId"
                        :pode-avancar="podeReceber(chamado)" @avancar="confirmarAvanco" />
                </template>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import Swal from "sweetalert2";
import TratarErroAjax from "../assets/TratarErroAjax";
import ChamadoOperacional from "./ChamadoOperacional";
import SituacaoChamadoEnum from "../../enums/SituacaoChamadoEnum";
import { mapGetters } from "vuex";

export default {
    name: "AtendimentoEquipeView",
    components: { TratarErroAjax, ChamadoOperacional },
    props: { prioridades: { type: Array, default: () => [] } },
    data() {
        return {
            msgId: "msgAtendimentoEquipe",
            chamados: [],
            possuiVinculoProfissional: true,
            carregando: false,
            processandoId: null,
        };
    },
    computed: {
        ...mapGetters({ baseUrl: "getBaseUrl" }),
        atendimentos() {
            return this.chamados.filter(item => this.situacaoId(item) === SituacaoChamadoEnum.EM_ATENDIMENTO);
        },
        fila() {
            return this.chamados.filter(item => this.situacaoId(item) === SituacaoChamadoEnum.EM_FILA);
        },
    },
    mounted() { this.carregar(); },
    methods: {
        carregar() {
            this.carregando = true;
            return axios.get(`${this.baseUrl}/atendimento_equipe/fila`)
                .then(response => {
                    this.chamados = response.data.chamados || [];
                    this.possuiVinculoProfissional = !!response.data.possuiVinculoProfissional;
                })
                .catch(this.erro)
                .finally(() => { this.carregando = false; });
        },
        situacaoId(chamado) {
            return Number(chamado && chamado.situacaoAtual && chamado.situacaoAtual.TG_SITUACAO_ID);
        },
        podeReceber(chamado) {
            const equipeId = Number(chamado.EQUIPE_FILA_ID);
            if (this.atendimentos.some(item => Number(item.EQUIPE_FILA_ID) === equipeId)) return false;
            const primeiro = this.fila.find(item => Number(item.EQUIPE_FILA_ID) === equipeId);
            return !!primeiro && Number(primeiro.CHAMADO_ID) === Number(chamado.CHAMADO_ID);
        },
        confirmarAvanco(chamado) {
            if (this.processandoId !== null) return;
            Swal.fire({
                title: chamado.PROXIMA_ETAPA_DESCRICAO,
                text: `Confirma o registro desta etapa no chamado Nº ${chamado.CHAMADO_ID}?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sim, registrar",
                cancelButtonText: "Não",
            }).then(resultado => {
                if (!resultado.isConfirmed) return;
                this.processandoId = chamado.CHAMADO_ID;
                axios.post(`${this.baseUrl}/atendimento_equipe/avancar`, {
                    CHAMADO_ID: chamado.CHAMADO_ID,
                    ETAPA_ESPERADA_ID: chamado.PROXIMA_ETAPA_ID,
                })
                    .then(response => this.carregar().then(() => Swal.fire("Sucesso", response.data.msg, "success")))
                    .catch(this.erro)
                    .finally(() => { this.processandoId = null; });
            });
        },
        erro(error) {
            this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                id: this.msgId,
                response: error && error.response,
            }, { root: true });
        },
    },
};
</script>
