<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense>
                <v-icon color="primary" class="mr-2">mdi-clipboard-check-outline</v-icon>
                <v-toolbar-title>Analisar Chamado</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click="buscar"><v-icon>mdi-refresh</v-icon></v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>

            <v-card-text v-if="chamado" class="analysis-content">
                <v-alert prominent outlined :type="tipoSituacao" class="mb-4">
                    <div class="status-content">
                        <span>Chamado Nº {{ chamado.CHAMADO_ID }}</span>
                        <v-chip small dark :color="corSituacao">{{ situacaoAtual }}</v-chip>
                    </div>
                </v-alert>

                <v-alert v-if="statusId === 3" :type="encaminhadoAgora ? 'success' : 'info'" outlined class="mb-4">
                    <strong>{{ encaminhadoAgora ? 'Encaminhamento realizado.' : 'Chamado em atendimento.' }}</strong>
                    O chamado está vinculado ao veículo e à equipe informados abaixo. O encerramento deve ser feito no Acompanhamento de Chamados.
                </v-alert>

                <v-card outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Paciente</v-card-title>
                    <v-card-text class="pt-3">
                        <v-row dense>
                            <v-col cols="12" md="5"><v-text-field label="Paciente" readonly filled dense hide-details :value="valor(paciente, 'PACIENTE_NOME')" /></v-col>
                            <v-col cols="12" md="3"><v-text-field label="CPF" readonly filled dense hide-details :value="valor(paciente, 'PACIENTE_CPF')" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Idade" readonly filled dense hide-details :value="idadePaciente" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Sexo" readonly filled dense hide-details :value="descricao(sexos, paciente && paciente.TG_SEXO_ID)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Vulnerabilidade social" readonly filled dense hide-details :value="simNao(paciente && paciente.PACIENTE_VULNERABILIDADE_SOCIAL)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Paciente temporário" readonly filled dense hide-details :value="simNao(paciente && paciente.PACIENTE_TEMPORARIO)" /></v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-card outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Dados do chamado</v-card-title>
                    <v-card-text class="pt-3">
                        <v-row dense>
                            <v-col cols="12" md="3"><v-text-field label="Tipo de chamado" readonly filled dense hide-details :value="descricao(tiposChamado, chamado.TG_CHAMADO_ID)" /></v-col>
                            <v-col cols="12" md="3"><v-text-field label="Prioridade" readonly filled dense hide-details :color="corPrioridade" :value="descricao(prioridades, chamado.TG_PRIORIDADE_ID)" /></v-col>
                            <v-col cols="12" md="3"><v-text-field label="Data/Hora de abertura" readonly filled dense hide-details :value="formatarDataHora(chamado.CHAMADO_DATA)" /></v-col>
                            <v-col cols="12" md="3"><v-text-field label="Horário de atendimento" readonly filled dense hide-details :value="chamado.CHAMADO_HORARIO_ATENDIMENTO || '-'" /></v-col>

                            <v-col cols="12" md="6"><v-text-field label="Unidade de origem" readonly filled dense hide-details color="green" :value="valor(unidadeSolicitante, 'UNIDADE_NOME')" /></v-col>
                            <v-col cols="12" md="6"><v-text-field label="Unidade de destino" readonly filled dense hide-details color="deep-orange" :value="valor(unidadeDestino, 'UNIDADE_NOME')" /></v-col>

                            <v-col cols="12" md="4"><v-text-field label="Profissional solicitante" readonly filled dense hide-details :value="chamado.profissionalSolicitanteNome || chamado.CHAMADO_PROFISSIONAL_SOLICITANTE || '-'" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Setor de origem" readonly filled dense hide-details :value="chamado.CHAMADO_SETOR_SOLICITANTE || '-'" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Leito de origem" readonly filled dense hide-details :value="chamado.CHAMADO_LEITO_SOLICITANTE || '-'" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Setor de destino" readonly filled dense hide-details :value="chamado.CHAMADO_SETOR_DESTINO || '-'" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Leito de destino" readonly filled dense hide-details :value="chamado.CHAMADO_LEITO_DESTINO || '-'" /></v-col>

                            <v-col cols="12" md="6"><v-textarea label="Procedimentos" readonly filled dense hide-details rows="2" :value="procedimentosNome" /></v-col>
                            <v-col cols="12" md="6"><v-textarea label="Diagnósticos" readonly filled dense hide-details rows="2" :value="diagnosticosNome" /></v-col>
                            <v-col cols="12" md="9"><v-textarea label="Dispositivos" readonly filled dense hide-details rows="2" :value="chamado.CHAMADO_DISPOSITIVOS || '-'" /></v-col>
                            <v-col cols="6" md="1"><v-text-field label="Peso" readonly filled dense hide-details :value="peso" /></v-col>
                            <v-col cols="6" md="2"><v-text-field label="Ambulância extra" readonly filled dense hide-details :value="simNao(chamado.CHAMADO_AMBULANCIA_EXTRA)" /></v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-card outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Suporte e sinais vitais</v-card-title>
                    <v-card-text class="pt-3">
                        <v-row dense>
                            <v-col cols="12" md="4"><v-text-field label="Precaução" readonly filled dense hide-details :value="descricao(tiposPrecaucao, chamado.TG_TIPO_PRECAUCAO_ID)" /></v-col>
                            <v-col cols="12" md="4"><v-text-field label="Suporte de O2" readonly filled dense hide-details :value="descricao(suportesO2, chamado.TG_SUPORTE_O2_ID)" /></v-col>
                            <v-col cols="12" md="4"><v-text-field label="Suporte hemodinâmico" readonly filled dense hide-details :value="descricao(suportesHemodinamicos, chamado.TG_SUPORTE_HEMODINAMICO_ID)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Temperatura" readonly filled dense hide-details :value="descricao(temperaturas, chamado.TG_TEMPERATURA_ID)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Frequência cardíaca" readonly filled dense hide-details :value="descricao(frequenciasCardiacas, chamado.TG_FREQUENCIA_CARDIACA_ID)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Pressão arterial" readonly filled dense hide-details :value="descricao(pressoesArteriais, chamado.TG_PRESSAO_ARTERIAL_ID)" /></v-col>
                            <v-col cols="6" md="3"><v-text-field label="Saturação de O2" readonly filled dense hide-details :value="descricao(saturacoes, chamado.TG_SATURACAO_ID)" /></v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-card v-if="equipeVinculada" outlined class="mb-4">
                    <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Veículo e equipe vinculados</v-card-title>
                    <v-card-text class="pt-3">
                        <v-row dense>
                            <v-col cols="12" md="4"><v-text-field label="Veículo" readonly filled dense hide-details :value="veiculoVinculado ? veiculoVinculado.VEICULO_IDENTIFICACAO : '-'" /></v-col>
                            <v-col cols="12" md="3"><v-text-field label="Equipe" readonly filled dense hide-details :value="descricaoEquipe(equipeVinculada)" /></v-col>
                            <v-col cols="12" md="5"><v-text-field label="Profissionais" readonly filled dense hide-details :value="profissionaisVinculados" /></v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-textarea label="Observações" readonly filled rows="3" :value="chamado.CHAMADO_OBSERVACAO || '-'" />

                <v-divider class="my-3"></v-divider>
                <v-btn v-if="statusId === 2" color="primary" tile :disabled="!veiculos.length" @click="abrirEncaminhar">Encaminhar para atendimento</v-btn>
                <v-btn v-if="statusId === 2" color="error" dark tile class="ml-2" @click="abrirCancelar">Cancelar análise</v-btn>
                <v-btn v-if="statusId === 3" color="primary" tile @click="irAcompanhamento">Ir para Acompanhamento</v-btn>
                <v-btn v-if="statusId === 3" outlined tile class="ml-2" @click="voltarHome">Voltar para Home</v-btn>
                <v-alert v-if="statusId === 2 && !veiculos.length" class="mt-3" type="warning" outlined>Não há veículo/equipe disponível para encaminhamento.</v-alert>
            </v-card-text>

            <v-card-text v-else>
                <v-alert type="info" outlined>Informe um chamado na URL ou selecione um chamado na Home.</v-alert>
            </v-card-text>
        </v-card>

        <v-dialog v-model="dialog" persistent width="800" scrollable :fullscreen="fullScreen">
            <v-card v-if="dialog">
                <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                    <v-toolbar-title>{{ acao === 'cancelar' ? 'Cancelar análise' : 'Encaminhar para atendimento' }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="fullScreen = true" v-show="!fullScreen"><v-icon>mdi-window-maximize</v-icon></v-btn>
                    <v-btn icon @click="fullScreen = false" v-show="fullScreen"><v-icon>mdi-window-restore</v-icon></v-btn>
                    <v-btn icon @click="fecharDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <v-card-text class="mt-5">
                    <fieldset v-if="acao === 'encaminhar'" class="custom-fieldset">
                        <legend class="custom-legend">VEÍCULO E EQUIPE</legend>
                        <v-select label="Veículo / equipe*" :items="veiculos" :item-text="descricaoVeiculoEquipe" return-object v-model="equipeSelecionada" outlined dense />
                    </fieldset>
                    <fieldset v-else class="custom-fieldset">
                        <legend class="custom-legend">MOTIVAÇÃO DO CANCELAMENTO</legend>
                        <v-select label="Motivo*" :items="motivosCancelamento" item-text="DESCRICAO" item-value="COLUNA_ID" v-model="motivo" outlined dense />
                        <v-textarea label="Motivação*" v-model="observacao" outlined rows="4" />
                    </fieldset>
                </v-card-text>
                <v-divider class="ma-0"></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" dark tile :disabled="!podeConfirmar" @click="confirmar">Confirmar</v-btn>
                    <v-btn color="red" dark outlined tile @click="fecharDialog">Fechar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import moment from 'moment';
import TratarErroAjax from '../assets/TratarErroAjax';

export default {
    name: 'ChamadoAnalisarView',
    components: { TratarErroAjax },
    props: {
        prioridades: { type: Array, default: () => [] },
        situacoesChamado: { type: Array, default: () => [] },
        motivosCancelamento: { type: Array, default: () => [] },
        sexos: { type: Array, default: () => [] },
        tiposChamado: { type: Array, default: () => [] },
        tiposPrecaucao: { type: Array, default: () => [] },
        suportesO2: { type: Array, default: () => [] },
        suportesHemodinamicos: { type: Array, default: () => [] },
        temperaturas: { type: Array, default: () => [] },
        frequenciasCardiacas: { type: Array, default: () => [] },
        pressoesArteriais: { type: Array, default: () => [] },
        saturacoes: { type: Array, default: () => [] }
    },
    data: () => ({
        msgId: 'msgChamadoAnalisar', chamado: null, veiculos: [], equipeSelecionada: null,
        dialog: false, fullScreen: false, acao: null, motivo: null, observacao: '', encaminhadoAgora: false
    }),
    computed: {
        baseUrl() { return this.$store.getters.getBaseUrl; },
        paciente() { return this.chamado ? this.chamado.paciente : null; },
        unidadeSolicitante() { return this.chamado && (this.chamado.unidadeSolicitante || this.chamado.unidade_solicitante); },
        unidadeDestino() { return this.chamado && (this.chamado.unidadeDestino || this.chamado.unidade_destino); },
        statusId() {
            const situacao = this.chamado && (this.chamado.situacaoAtual || this.chamado.situacao_atual);
            return situacao ? Number(situacao.TG_SITUACAO_ID) : null;
        },
        situacaoAtual() { return this.descricao(this.situacoesChamado, this.statusId); },
        tipoSituacao() { return this.statusId === 5 ? 'error' : this.statusId === 4 ? 'success' : 'info'; },
        corSituacao() { return this.statusId === 5 ? 'red' : this.statusId === 4 ? 'green' : this.statusId === 3 ? 'orange darken-2' : 'primary'; },
        corPrioridade() {
            const valor = this.descricao(this.prioridades, this.chamado && this.chamado.TG_PRIORIDADE_ID).toUpperCase();
            if (valor.includes('VERMELHO')) return 'red';
            if (valor.includes('LARANJA')) return 'orange';
            if (valor.includes('AMARELO')) return 'amber darken-2';
            return 'green';
        },
        idadePaciente() {
            const nascimento = this.paciente && this.paciente.PACIENTE_DT_NASCIMENTO;
            return nascimento ? `${moment().diff(moment(nascimento), 'years')} anos` : '-';
        },
        procedimentosNome() {
            const itens = this.chamado && this.chamado.procedimentos || [];
            return itens.map(item => item.PROCEDIMENTO_DESCRICAO).filter(Boolean).join(', ') || '-';
        },
        diagnosticosNome() {
            const itens = this.chamado && this.chamado.diagnosticos || [];
            return itens.map(item => item.DIAGNOSTICO_DESCRICAO).filter(Boolean).join(', ') || '-';
        },
        peso() { return this.chamado && this.chamado.CHAMADO_PESO ? `${this.chamado.CHAMADO_PESO} kg` : '-'; },
        vinculoEquipe() {
            const vinculos = this.chamado && (this.chamado.vinculosEquipe || this.chamado.vinculos_equipe) || [];
            return vinculos.length ? vinculos[0] : null;
        },
        equipeVinculada() { return this.vinculoEquipe && this.vinculoEquipe.equipe; },
        veiculoVinculado() { return this.equipeVinculada && this.equipeVinculada.veiculo; },
        profissionaisVinculados() {
            const itens = this.equipeVinculada && (this.equipeVinculada.equipeProfissional || this.equipeVinculada.equipe_profissional) || [];
            return itens.filter(item => Number(item.EQUIPE_PROFISSIONAL_ATIVO) === 1)
                .map(item => item.profissional && item.profissional.PROFISSIONAL_NOME).filter(Boolean).join(', ') || '-';
        },
        podeConfirmar() {
            return this.acao === 'encaminhar' ? !!this.equipeSelecionada : !!this.motivo && !!String(this.observacao || '').trim();
        }
    },
    mounted() { this.buscar(); },
    methods: {
        buscar() {
            const id = new URLSearchParams(window.location.search).get('chamado');
            if (!id) return;
            axios.get(`${this.baseUrl}/chamado_analisar/buscar/${id}`).then(response => {
                this.chamado = response.data;
                if (this.statusId === 2) this.carregarVeiculos();
            }).catch(this.erro);
        },
        carregarVeiculos() {
            axios.get(`${this.baseUrl}/chamado_analisar/veiculos-disponiveis`)
                .then(response => { this.veiculos = response.data; }).catch(this.erro);
        },
        abrirEncaminhar() { this.carregarVeiculos(); this.acao = 'encaminhar'; this.equipeSelecionada = null; this.dialog = true; this.fullScreen = false; },
        abrirCancelar() { this.acao = 'cancelar'; this.motivo = null; this.observacao = ''; this.dialog = true; this.fullScreen = false; },
        confirmar() {
            if (!this.podeConfirmar) return;
            if (this.acao === 'encaminhar') {
                const equipe = this.equipeSelecionada && this.equipeSelecionada.equipe;
                this.executar('encaminhar', { EQUIPE_ID: equipe ? equipe.EQUIPE_ID : null });
                return;
            }
            this.executar('cancelar', { MOTIVO_CANCELAMENTO_ID: this.motivo, CHAMADO_SITUACAO_OBSERVACAO: this.observacao });
        },
        fecharDialog() { this.dialog = false; this.fullScreen = false; },
        executar(acao, dados) {
            axios.post(`${this.baseUrl}/chamado_analisar/${acao}`, { CHAMADO_ID: this.chamado.CHAMADO_ID, ...(dados || {}) })
                .then(response => {
                    this.fecharDialog();
                    this.chamado = response.data.retorno;
                    this.encaminhadoAgora = acao === 'encaminhar';
                    if (this.encaminhadoAgora) this.veiculos = [];
                }).catch(this.erro);
        },
        voltarHome() { window.location.href = `${this.baseUrl}/home`; },
        irAcompanhamento() { window.location.href = `${this.baseUrl}/chamado_acompanhamento?chamado=${this.chamado.CHAMADO_ID}`; },
        descricaoVeiculoEquipe(veiculo) {
            if (!veiculo) return '-';
            return `${veiculo.VEICULO_IDENTIFICACAO || 'Veículo'} — ${this.descricaoEquipe(veiculo.equipe)}`;
        },
        descricaoEquipe(equipe) { return equipe ? `Equipe Nº ${equipe.EQUIPE_ID}${equipe.EQUIPE_TURNO ? ` - ${equipe.EQUIPE_TURNO}` : ''}` : '-'; },
        valor(obj, campo) { return obj && obj[campo] ? obj[campo] : '-'; },
        descricao(lista, id) { const item = (lista || []).find(atual => Number(atual.COLUNA_ID) === Number(id)); return item ? item.DESCRICAO : '-'; },
        formatarDataHora(data) { return data ? moment(data).format('DD/MM/YYYY HH:mm:ss') : '-'; },
        simNao(valor) { return valor === null || valor === undefined ? '-' : Number(valor) === 1 ? 'Sim' : 'Não'; },
        erro(error) {
            this.$store.dispatch('TratarErroAjaxModule/tratarErro', { id: this.msgId, response: error && error.response }, { root: true });
        }
    }
};
</script>

<style scoped>
.custom-fieldset { padding: 18px 16px 8px; border: 1px solid #bdbdbd; border-radius: 3px; }
.custom-legend { padding: 0 8px; color: #616161; font-size: 14px; font-style: italic; font-weight: 700; }
.analysis-content { padding: 18px 16px 22px; }
.status-content { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.status-content span { font-weight: 600; }
</style>
