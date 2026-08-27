<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense><v-icon color="primary"
                    class="mr-2">mdi-clipboard-check-outline</v-icon><v-toolbar-title>Analisar
                    Chamado</v-toolbar-title><v-spacer></v-spacer><v-btn icon
                    @click="buscar"><v-icon>mdi-refresh</v-icon></v-btn></v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <v-card-text v-if="chamado" class="analysis-content">
                <v-alert prominent outlined :type="tipoSituacao" class="status-alert">
                    <div class="status-content">
                        <span>Chamado Nº {{ chamado.CHAMADO_ID }}</span>
                        <v-chip small dark :color="corSituacao">{{ situacaoAtual }}</v-chip>
                    </div>
                </v-alert>

                <v-row dense>
                    <v-col cols="12" md="6">
                        <v-text-field label="Paciente" readonly filled
                            :value="valor(chamado.paciente, 'PACIENTE_NOME')"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field label="CPF" readonly filled
                            :value="valor(chamado.paciente, 'PACIENTE_CPF')"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field label="Prioridade" readonly filled
                            :color="corPrioridade" :value="descricao(prioridades, chamado.TG_PRIORIDADE_ID)"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Origem" readonly filled color="green"
                            :value="valor(chamado.unidadeSolicitante, 'UNIDADE_NOME')"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Destino" readonly filled color="deep-orange"
                            :value="valor(chamado.unidadeDestino, 'UNIDADE_NOME')"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Profissional solicitante" readonly filled
                            :value="chamado.profissionalSolicitanteNome || chamado.CHAMADO_PROFISSIONAL_SOLICITANTE || '-'" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field label="Setor origem" readonly filled
                            :value="chamado.CHAMADO_SETOR_SOLICITANTE || '-'" />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field label="Leito origem" readonly filled
                            :value="chamado.CHAMADO_LEITO_SOLICITANTE || '-'" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Procedimento" readonly filled
                            :value="procedimentoNome" />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field label="Diagnóstico" readonly filled
                            :value="diagnosticoNome" />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea label="Dispositivos" readonly filled
                            rows="2" :value="chamado.CHAMADO_DISPOSITIVOS || '-'" />
                    </v-col>
                    <v-col cols="12">
                        <v-textarea label="Observações" readonly filled
                            rows="2" :value="chamado.CHAMADO_OBSERVACAO || '-'" />
                    </v-col>
                </v-row>
                <v-divider class="my-3"></v-divider>
                <v-btn v-if="statusId === 1 || statusId === 2" color="primary" tile :disabled="!veiculos.length"
                    @click="abrirEncaminhar">Encaminhar</v-btn>
                <v-btn v-if="statusId === 2 || statusId === 3" color="error" dark tile class="ml-2"
                    @click="abrirCancelar">Cancelar</v-btn>
                <v-btn v-if="statusId === 3" color="success" tile class="ml-2" @click="concluir">Concluir</v-btn>
                <v-alert v-if="(statusId === 1 || statusId === 2) && !veiculos.length" class="mt-3" type="warning" outlined>Não há
                    veículo/equipe disponível para encaminhamento.</v-alert>
            </v-card-text>
            <v-card-text v-else><v-alert type="info" outlined>Informe um chamado na URL ou selecione um chamado na
                    Home.</v-alert></v-card-text>
        </v-card>
        <v-dialog v-model="dialog" persistent width="800" scrollable :fullscreen="fullScreen">
            <v-card v-if="dialog">
                <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                    <v-toolbar-title>{{ acao === 'cancelar' ? 'Cancelar chamado' : 'Encaminhar chamado'
                        }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="fullScreen = true"
                        v-show="!fullScreen"><v-icon>mdi-window-maximize</v-icon></v-btn>
                    <v-btn icon @click="fullScreen = false"
                        v-show="fullScreen"><v-icon>mdi-window-restore</v-icon></v-btn>
                    <v-btn icon @click="fecharDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <v-card-text class="mt-5">
                    <fieldset v-if="acao === 'encaminhar'" class="custom-fieldset">
                        <legend class="custom-legend">VEÍCULO E EQUIPE</legend>
                        <v-select label="Veículo / equipe*" :items="veiculos" item-text="VEICULO_IDENTIFICACAO"
                            return-object v-model="equipeSelecionada" outlined dense></v-select>
                    </fieldset>
                    <fieldset v-else class="custom-fieldset">
                        <legend class="custom-legend">MOTIVAÇÃO DO CANCELAMENTO</legend>
                        <v-select label="Motivo*" :items="motivosCancelamento" item-text="DESCRICAO"
                            item-value="COLUNA_ID" v-model="motivo" outlined dense></v-select>
                        <v-textarea label="Motivação*" v-model="observacao" outlined rows="4"></v-textarea>
                    </fieldset>
                </v-card-text>
                <v-divider class="ma-0"></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn class="btn-confirmar" color="primary" dark tile :disabled="!podeConfirmar"
                        @click="confirmar">Confirmar</v-btn>
                    <v-btn color="red" dark outlined tile @click="fecharDialog">Fechar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import TratarErroAjax from '../assets/TratarErroAjax';
export default {
    name: 'ChamadoAnalisarView',

    components: {
        TratarErroAjax
    },

    props: {
        prioridades: Array,
        situacoesChamado: Array,
        motivosCancelamento: Array
    },

    data: () => ({
        msgId: 'msgChamadoAnalisar',
        chamado: null,
        veiculos: [],
        equipeSelecionada: null,
        dialog: false,
        fullScreen: false,
        acao: null,
        motivo: null,
        observacao: ''
    }),

    computed: {
        baseUrl() {
            return this.$store.getters.getBaseUrl;
        },

        statusId() {
            return this.chamado && this.chamado.situacaoAtual
                ? Number(this.chamado.situacaoAtual.TG_SITUACAO_ID)
                : null;
        },

        situacaoAtual() {
            return this.descricao(this.situacoesChamado, this.statusId);
        },

        tipoSituacao() {
            return this.statusId === 5
                ? 'error'
                : this.statusId === 4
                    ? 'success'
                    : 'info';
        },

        corSituacao() {
            return this.statusId === 5
                ? 'red'
                : this.statusId === 4
                    ? 'green'
                    : 'primary';
        },

        corPrioridade() {
            const prioridade = this.descricao(
                this.prioridades,
                this.chamado && this.chamado.TG_PRIORIDADE_ID
            ).toUpperCase();

            if (prioridade.indexOf('VERMELHO') >= 0) return 'red';
            if (prioridade.indexOf('LARANJA') >= 0) return 'orange';
            if (prioridade.indexOf('AMARELO') >= 0) return 'amber darken-2';

            return 'green';
        },

        procedimentoNome() {
            return this.chamado && this.chamado.procedimentos && this.chamado.procedimentos.length
                ? this.chamado.procedimentos[0].PROCEDIMENTO_DESCRICAO
                : '-';
        },

        diagnosticoNome() {
            return this.chamado && this.chamado.diagnosticos && this.chamado.diagnosticos.length
                ? this.chamado.diagnosticos[0].DIAGNOSTICO_DESCRICAO
                : '-';
        },

        podeConfirmar() {
            return this.acao === 'encaminhar'
                ? !!this.equipeSelecionada
                : !!this.motivo && !!String(this.observacao || '').trim();
        }
    },

    mounted() {
        this.buscar();
    },

    methods: {
        buscar() {
            const id = new URLSearchParams(window.location.search).get('chamado');

            if (!id) {
                return;
            }

            axios.get(`${this.baseUrl}/chamado_analisar/buscar/${id}`)
                .then(response => {
                    this.chamado = response.data;

                    if (this.statusId === 1 || this.statusId === 2) {
                        this.carregarVeiculos();
                    }
                })
                .catch(error => this.erro(error));
        },

        carregarVeiculos() {
            axios.get(`${this.baseUrl}/chamado_analisar/veiculos-disponiveis`)
                .then(response => {
                    this.veiculos = response.data;
                })
                .catch(error => this.erro(error));
        },

        abrirEncaminhar() {
            this.carregarVeiculos();
            this.acao = 'encaminhar';
            this.equipeSelecionada = null;
            this.dialog = true;
            this.fullScreen = false;
        },

        abrirCancelar() {
            this.acao = 'cancelar';
            this.dialog = true;
            this.fullScreen = false;
        },

        concluir() {
            this.executar('concluir');
        },

        confirmar() {
            if (!this.podeConfirmar) {
                return;
            }

            if (this.acao === 'encaminhar') {
                this.executar('encaminhar', {
                    EQUIPE_ID: this.equipeSelecionada && this.equipeSelecionada.equipe
                        ? this.equipeSelecionada.equipe.EQUIPE_ID
                        : null
                });

                return;
            }

            this.executar('cancelar', {
                MOTIVO_CANCELAMENTO_ID: this.motivo,
                CHAMADO_SITUACAO_OBSERVACAO: this.observacao
            });
        },

        fecharDialog() {
            this.dialog = false;
            this.fullScreen = false;
        },

        executar(acao, dados = {}) {
            axios.post(`${this.baseUrl}/chamado_analisar/${acao}`, {
                CHAMADO_ID: this.chamado.CHAMADO_ID,
                ...dados
            })
                .then(response => {
                    this.fecharDialog();
                    this.chamado = response.data.retorno;
                })
                .catch(error => this.erro(error));
        },

        valor(obj, campo) {
            return obj && obj[campo] ? obj[campo] : '-';
        },

        descricao(lista, id) {
            const item = (lista || []).find(
                itemAtual => Number(itemAtual.COLUNA_ID) === Number(id)
            );

            return item ? item.DESCRICAO : '-';
        },

        erro(error) {
            this.$store.dispatch(
                'TratarErroAjaxModule/tratarErro',
                {
                    id: this.msgId,
                    response: error && error.response
                },
                { root: true }
            );
        }
    }
}
</script>

<style scoped>
.custom-fieldset {
    padding: 18px 16px 8px;
    border: 1px solid #bdbdbd;
    border-radius: 3px;
}

.custom-legend {
    padding: 0 8px;
    color: #616161;
    font-size: 14px;
    font-style: italic;
    font-weight: 700;
}

.analysis-content {
    padding: 18px 16px 22px;
}

.status-alert {
    margin-bottom: 18px;
}

.status-content {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.status-content span {
    font-weight: 600;
}

.btn-confirmar.v-btn--disabled {
    background-color: #1976d2 !important;
    color: #fff !important;
    opacity: .45;
}
</style>
