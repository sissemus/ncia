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
                <v-alert v-if="!carregando && !acessoGlobal && !possuiVinculoProfissional" type="warning" outlined>
                    Seu usuário não está vinculado a uma equipe assistencial ativa. Confirme se o CPF do usuário é o mesmo do cadastro do profissional.
                </v-alert>

                <v-alert v-else-if="!carregando && !chamados.length" type="info" outlined>
                    <span v-if="acessoGlobal">Não há chamados em fila ou em atendimento.</span>
                    <span v-else>Não há chamados em fila ou em atendimento vinculados à sua equipe.</span>
                </v-alert>

                <template v-if="chamados.length">
                    <div class="subtitle-1 font-weight-bold mb-2">Atendimento atual</div>
                    <v-alert v-if="!atendimentos.length" type="info" dense outlined>
                        Nenhum chamado está em atendimento no momento.
                    </v-alert>
                    <chamado-operacional v-for="chamado in atendimentos" :key="`atendimento-${chamado.CHAMADO_ID}`"
                        :chamado="chamado" :prioridades="prioridades" :tipos-chamado="listaTiposChamado" :processando-id="processandoId"
                        :pode-avancar="true" @avancar="confirmarAvanco" @cancelar="confirmarCancelamento" @consultar="consultarChamado" />

                    <v-divider class="my-5"></v-divider>
                    <div class="subtitle-1 font-weight-bold mb-2">Chamados em fila</div>
                    <v-alert v-if="!fila.length" type="info" dense outlined>
                        Nenhum chamado aguardando recebimento.
                    </v-alert>
                    <chamado-operacional v-for="chamado in fila" :key="`fila-${chamado.CHAMADO_ID}`"
                        :chamado="chamado" :prioridades="prioridades" :tipos-chamado="listaTiposChamado" :processando-id="processandoId"
                        :pode-avancar="podeReceber(chamado)" @avancar="confirmarAvanco" @cancelar="confirmarCancelamento" @consultar="consultarChamado" />
                </template>
            </v-card-text>
        </v-card>

        <v-dialog v-model="dialogDetalhes" max-width="1050" scrollable>
            <v-card v-if="chamadoDetalhado">
                <v-toolbar color="primary" dark flat dense>
                    <v-toolbar-title>Chamado Nº {{ chamadoDetalhado.CHAMADO_ID }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn text small :href="`${baseUrl}/chamado_acompanhamento?chamado=${chamadoDetalhado.CHAMADO_ID}`"
                        target="_blank" rel="noopener noreferrer" title="Abrir consulta completa do chamado">
                        <v-icon small left>mdi-open-in-new</v-icon>
                        Consulta completa
                    </v-btn>
                    <v-btn icon @click="fecharDetalhes"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <v-card-text class="pt-4">
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 blue-grey lighten-5">Paciente e transporte</v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="6"><strong>Paciente:</strong> {{ nomePacienteDetalhado }}</v-col>
                                <v-col cols="12" md="3"><strong>CPF:</strong> {{ dado(chamadoDetalhado.paciente, 'PACIENTE_CPF') }}</v-col>
                                <v-col cols="12" md="3"><strong>Nascimento:</strong> {{ dado(chamadoDetalhado.paciente, 'PACIENTE_DT_NASCIMENTO') }}</v-col>
                                <v-col cols="12" md="6"><strong>Origem:</strong> {{ dado(chamadoDetalhado.unidadeSolicitante, 'UNIDADE_NOME') }}</v-col>
                                <v-col cols="12" md="6"><strong>Destino:</strong> {{ dado(chamadoDetalhado.unidadeDestino, 'UNIDADE_NOME') }}</v-col>
                                <v-col cols="12" md="6"><strong>Setor/leito de origem:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_SETOR_SOLICITANTE') }} / {{ dado(chamadoDetalhado, 'CHAMADO_LEITO_SOLICITANTE') }}</v-col>
                                <v-col cols="12" md="6"><strong>Setor/leito de destino:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_SETOR_DESTINO') }} / {{ dado(chamadoDetalhado, 'CHAMADO_LEITO_DESTINO') }}</v-col>
                                <v-col cols="12" md="4"><strong>Tipo:</strong> {{ descricao(listaTiposChamado, chamadoDetalhado.TG_CHAMADO_ID) }}</v-col>
                                <v-col cols="12" md="4"><strong>Prioridade:</strong> {{ descricao(prioridades, chamadoDetalhado.TG_PRIORIDADE_ID) }}</v-col>
                                <v-col cols="12" md="4"><strong>Abertura:</strong> {{ formatarDataHora(chamadoDetalhado.CHAMADO_DATA) }}</v-col>
                                <v-col cols="12" md="6"><strong>Procedimentos:</strong> {{ descricoes(chamadoDetalhado.procedimentos, 'PROCEDIMENTO_DESCRICAO') }}</v-col>
                                <v-col cols="12" md="6"><strong>Diagnósticos:</strong> {{ descricoes(chamadoDetalhado.diagnosticos, 'DIAGNOSTICO_DESCRICAO') }}</v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 blue-grey lighten-5">Dados clínicos do chamado</v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="4"><strong>Precaução:</strong> {{ descricao(tiposPrecaucao, chamadoDetalhado.TG_TIPO_PRECAUCAO_ID) }}</v-col>
                                <v-col cols="12" md="4"><strong>Suporte O2:</strong> {{ descricao(suportesO2, chamadoDetalhado.TG_SUPORTE_O2_ID) }}</v-col>
                                <v-col cols="12" md="4"><strong>Suporte hemodinâmico:</strong> {{ descricao(suportesHemodinamicos, chamadoDetalhado.TG_SUPORTE_HEMODINAMICO_ID) }}</v-col>
                                <v-col cols="12" md="4"><strong>Temperatura:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_TEMPERATURA') }}</v-col>
                                <v-col cols="12" md="4"><strong>Pressão arterial:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_PRESSAO_ARTERIAL') }}</v-col>
                                <v-col cols="12" md="4"><strong>Frequência cardíaca:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_FREQUENCIA_CARDIACA') }}</v-col>
                                <v-col cols="12" md="4"><strong>Saturação O2:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_SATURACAO_O2') }}</v-col>
                                <v-col cols="12" md="4"><strong>Escala Glasgow:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_ESCALA_GLASGOW') }}</v-col>
                                <v-col cols="12" md="4"><strong>Dispositivos:</strong> {{ dado(chamadoDetalhado, 'CHAMADO_DISPOSITIVOS') }}</v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                    <atualizacao-clinica @salvo="aposAtualizacaoClinica" />
                    <v-card outlined>
                        <v-card-title class="subtitle-2 blue-grey lighten-5">Histórico do atendimento</v-card-title>
                        <v-card-text class="pt-3">
                            <div v-for="etapa in chamadoDetalhado.etapasAtendimento || []" :key="etapa.CHAMADO_ATENDIMENTO_ETAPA_ID">
                                {{ etapa.ATENDIMENTO_ETAPA_DESCRICAO }} — {{ formatarDataHora(etapa.ATENDIMENTO_ETAPA_DATA) }}
                            </div>
                            <div v-if="!chamadoDetalhado.etapasAtendimento || !chamadoDetalhado.etapasAtendimento.length">Nenhuma etapa registrada.</div>
                        </v-card-text>
                    </v-card>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { formatarDataHoraFortaleza } from '../../helpers/DataHora';
import Swal from "sweetalert2";
import TratarErroAjax from "../assets/TratarErroAjax";
import ChamadoOperacional from "./ChamadoOperacional";
import SituacaoChamadoEnum from "../../enums/SituacaoChamadoEnum";
import { mapGetters } from "vuex";

export default {
    name: "AtendimentoEquipeView",
    components: { TratarErroAjax, ChamadoOperacional },
    props: {
        prioridades: { type: Array, default: () => [] },
        tiposChamado: { type: Array, default: () => [] },
        tipos_chamado: { type: Array, default: () => [] },
        sexos: { type: Array, default: () => [] },
        tiposPrecaucao: { type: Array, default: () => [] },
        suportesO2: { type: Array, default: () => [] },
        suportesHemodinamicos: { type: Array, default: () => [] },
    },
    data() {
        return {
            msgId: "msgAtendimentoEquipe",
            chamados: [],
            possuiVinculoProfissional: true,
            acessoGlobal: false,
            carregando: false,
            processandoId: null,
            dialogDetalhes: false,
            chamadoDetalhado: null,
        };
    },
    computed: {
        ...mapGetters({ baseUrl: "getBaseUrl" }),
        listaTiposChamado() {
            return (this.tiposChamado && this.tiposChamado.length)
                ? this.tiposChamado
                : (this.tipos_chamado || []);
        },
        atendimentos() {
            return this.chamados.filter(item => this.situacaoId(item) === SituacaoChamadoEnum.EM_ATENDIMENTO);
        },
        fila() {
            return this.chamados.filter(item => this.situacaoId(item) === SituacaoChamadoEnum.EM_FILA);
        },
        nomePacienteDetalhado() {
            const paciente = this.chamadoDetalhado && this.chamadoDetalhado.paciente;
            return paciente && (paciente.PACIENTE_NOME || (Number(paciente.PACIENTE_VULNERABILIDADE_SOCIAL) === 1
                ? "PACIENTE EM VULNERABILIDADE SOCIAL" : "-")) || "-";
        },
    },
    created() {
        this.$store.dispatch("DominioModule/setAtualizacaoClinicaDominios", {
            prioridades: this.prioridades,
            tiposChamado: this.listaTiposChamado,
            tiposPrecaucao: this.tiposPrecaucao,
            suportesO2: this.suportesO2,
            suportesHemodinamicos: this.suportesHemodinamicos,
            sexos: this.sexos,
        });
        this.$store.dispatch("AtualizacaoClinicaModule/clear");
    },
    mounted() { this.carregar(); },
    methods: {
        formatarDataHora(valor) { return formatarDataHoraFortaleza(valor, true); },
        dado(item, campo) { return item && item[campo] ? item[campo] : "-"; },
        descricao(lista, id) {
            const item = (lista || []).find(valor => Number(valor.COLUNA_ID) === Number(id));
            return item ? item.DESCRICAO : "-";
        },
        descricoes(lista, campo) {
            return (lista || []).map(item => item[campo]).filter(Boolean).join(", ") || "-";
        },
        consultarChamado(chamado) {
            axios.get(`${this.baseUrl}/atendimento_equipe/chamado/${chamado.CHAMADO_ID}`)
                .then(response => {
                    this.chamadoDetalhado = response.data;
                    return this.$store.dispatch("AtualizacaoClinicaModule/setChamado", {
                        chamado: response.data,
                        contexto: "equipe",
                    });
                })
                .then(() => { this.dialogDetalhes = true; })
                .catch(this.erro);
        },
        fecharDetalhes() {
            this.dialogDetalhes = false;
            this.chamadoDetalhado = null;
            this.$store.dispatch("AtualizacaoClinicaModule/clear");
        },
        aposAtualizacaoClinica(dados) {
            if (this.chamadoDetalhado) {
                this.chamadoDetalhado.TG_PRIORIDADE_ID = dados.prioridadeAtual;
            }
            this.carregar();
        },
        carregar() {
            this.carregando = true;
            return axios.get(`${this.baseUrl}/atendimento_equipe/fila`)
                .then(response => {
                    this.chamados = response.data.chamados || [];
                    this.possuiVinculoProfissional = !!response.data.possuiVinculoProfissional;
                    this.acessoGlobal = !!response.data.acessoGlobal;
                })
                .catch(this.erro)
                .finally(() => { this.carregando = false; });
        },
        situacaoId(chamado) {
            return Number(chamado && chamado.situacaoAtual && chamado.situacaoAtual.TG_SITUACAO_ID);
        },
        podeReceber(chamado) {
            return chamado && chamado.PODE_RECEBER === true;
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
        confirmarCancelamento(chamado) {
            if (this.processandoId !== null) return;
            Swal.fire({
                title: "Cancelar atendimento",
                text: `Informe o motivo do cancelamento do chamado Nº ${chamado.CHAMADO_ID}.`,
                input: "textarea",
                inputLabel: "Motivo do cancelamento",
                inputPlaceholder: "Descreva o motivo...",
                inputAttributes: { maxlength: 2000 },
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Cancelar atendimento",
                cancelButtonText: "Voltar",
                confirmButtonColor: "#d32f2f",
                inputValidator: valor => {
                    if (!String(valor || "").trim()) return "Informe o motivo do cancelamento.";
                    return null;
                },
            }).then(resultado => {
                if (!resultado.isConfirmed) return;
                this.processandoId = chamado.CHAMADO_ID;
                axios.post(`${this.baseUrl}/atendimento_equipe/cancelar`, {
                    CHAMADO_ID: chamado.CHAMADO_ID,
                    MOTIVO: String(resultado.value || "").trim(),
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
